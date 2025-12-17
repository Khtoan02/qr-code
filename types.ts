export enum PaymentStatus {
  PENDING = 'PENDING',
  PAID = 'PAID',
  FAILED = 'FAILED'
}

export interface PaymentTransaction {
  id: string;
  paymentCode: string; // Mã thanh toán dùng để đối soát (Nội dung chuyển khoản)
  amount: number;
  description: string;
  customerName: string;
  status: PaymentStatus;
  createdAt: number;
  themeImage?: string; 
}

export type ViewState = 'DASHBOARD' | 'CREATE_PAYMENT' | 'IMAGE_EDITOR' | 'CUSTOMER_VIEW';

export interface GeminiError {
  message: string;
}
