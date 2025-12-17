// CẢNH BÁO BẢO MẬT: Trong ứng dụng thực tế, API Key không nên lưu ở frontend.
// Vì đây là ứng dụng demo frontend-only, chúng ta sẽ sử dụng trực tiếp để demo tính năng.
const SEPAY_API_KEY = 'TZ6IDLTMBQGTVGUOGSNXWOMQZD0FKR94D02MWZXE7CCQ7WFCRVKUXSHZEEVJ92YJ';

// Dựa trên hình ảnh cấu hình Webhook bạn cung cấp:
// 1. Tài khoản ngân hàng gốc (MBBank): 0329249536
// 2. Tài khoản ảo (VA) để nhận tiền và lọc: VQRQAFYMM9200
const MAIN_ACCOUNT_NUMBER = '0329249536'; 
const VA_ACCOUNT_NUMBER = 'VQRQAFYMM9200';

export interface SePayTransaction {
  id: number;
  transaction_date: string;
  account_number: string;
  sub_account: string;
  amount_in: string;
  amount_out: string;
  accumulated: string;
  transaction_content: string;
  reference_number: string;
  body: string;
}

/**
 * Kiểm tra xem giao dịch đã thành công chưa bằng cách gọi API SePay
 * @param paymentCode Nội dung chuyển khoản cần tìm (ví dụ: DH123456)
 * @param amount Số tiền cần khớp
 */
export const checkSePayPayment = async (paymentCode: string, amount: number): Promise<boolean> {
  try {
    // Sử dụng CORS Proxy để tránh lỗi chặn CORS từ trình duyệt khi gọi API trực tiếp
    const corsProxy = 'https://corsproxy.io/?';
    
    // API Query: Chúng ta kiểm tra lịch sử giao dịch của Tài khoản gốc (Main Account)
    // SePay sẽ trả về các giao dịch, bao gồm cả giao dịch vào VA (thường nằm trong sub_account hoặc được định danh qua nội dung)
    const apiUrl = `https://my.sepay.vn/userapi/transactions/list?account_number=${MAIN_ACCOUNT_NUMBER}&limit=20`;
    
    // Encode URL để truyền qua proxy an toàn
    const fullUrl = corsProxy + encodeURIComponent(apiUrl);

    const response = await fetch(fullUrl, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${SEPAY_API_KEY}`,
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      console.warn(`SePay API Warning: ${response.status} ${response.statusText}`);
      return false;
    }

    const data = await response.json();

    if (data.status === 200 && Array.isArray(data.transactions)) {
      // Tìm giao dịch khớp với mã thanh toán
      const transaction = data.transactions.find((t: SePayTransaction) => {
        // 1. Kiểm tra nội dung chuyển khoản có chứa mã thanh toán (paymentCode)
        const contentMatch = t.transaction_content.toLowerCase().includes(paymentCode.toLowerCase());
        
        // 2. Kiểm tra số tiền (chấp nhận sai số nhỏ hoặc >= số tiền yêu cầu)
        const amountIn = parseFloat(t.amount_in || "0");
        const amountMatch = amountIn >= amount;

        // 3. (Tùy chọn) Kiểm tra xem có đúng là vào VA không nếu cần thiết
        // Với setup của bạn, chỉ cần đúng nội dung code unique là đủ an toàn.
        
        return contentMatch && amountMatch;
      });

      return !!transaction;
    }
    
    return false;
  } catch (error) {
    console.error("Lỗi kiểm tra thanh toán (SePay):", error);
    return false;
  }
};

/**
 * Tạo URL mã QR SePay
 * Sử dụng Tài khoản ảo (VA) để khách hàng quét mã và chuyển tiền vào đúng nơi
 */
export const getSePayQRUrl = (amount: number, paymentCode: string): string => {
  // Template: https://qr.sepay.vn/img?acc=[ACCOUNT]&bank=[BANK_CODE]&amount=[AMOUNT]&des=[CONTENT]
  // Sử dụng VA_ACCOUNT_NUMBER (VQRQAFYMM9200) và Ngân hàng MB (theo cấu hình)
  return `https://qr.sepay.vn/img?acc=${VA_ACCOUNT_NUMBER}&bank=MB&amount=${amount}&des=${paymentCode}`;
};