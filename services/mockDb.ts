import { PaymentTransaction, PaymentStatus } from '../types';

const STORAGE_KEY = 'paygen_transactions';

export const getTransactions = (): PaymentTransaction[] => {
  const stored = localStorage.getItem(STORAGE_KEY);
  return stored ? JSON.parse(stored) : [];
};

export const saveTransaction = (transaction: PaymentTransaction): void => {
  const transactions = getTransactions();
  transactions.unshift(transaction);
  localStorage.setItem(STORAGE_KEY, JSON.stringify(transactions));
};

export const updateTransactionStatus = (id: string, status: PaymentStatus): void => {
  const transactions = getTransactions();
  const index = transactions.findIndex(t => t.id === id);
  if (index !== -1) {
    transactions[index].status = status;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(transactions));
  }
};

export const getTransactionById = (id: string): PaymentTransaction | undefined => {
  const transactions = getTransactions();
  return transactions.find(t => t.id === id);
};

// Helper to calculate total revenue
export const getTotalRevenue = (): number => {
  const transactions = getTransactions();
  return transactions
    .filter(t => t.status === PaymentStatus.PAID)
    .reduce((acc, curr) => acc + curr.amount, 0);
};
