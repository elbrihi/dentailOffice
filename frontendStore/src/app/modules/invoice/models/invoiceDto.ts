import { PaymentDto } from "../../payment/models/payment-dto";

export interface InvoiceDto
{
    invoiceDate: any;
    invoiceNumber: string;
    remainingDue: number;
    totalAmount: number;
    totalPaid: number;
    expandedPrescriptions?: boolean;
    payments: PaymentDto[];

}

// 'invoiceDate', 'invoiceNumber', 'remainingDue', 'totalAmount', 'totalPaid', 'payments'