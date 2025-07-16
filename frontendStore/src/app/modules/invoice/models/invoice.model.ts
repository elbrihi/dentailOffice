import { PaymentDto } from "../../payment/models/payment-dto";
import { InvoiceDto } from "./invoiceDto";

export class Invoice implements InvoiceDto
{
    invoiceDate: any;
    invoiceNumber: string = '';
    remainingDue: number = 0;
    totalAmount: number = 0;
    totalPaid: number = 0;
    payments: PaymentDto[] = [];

   showPayments: boolean = false; // ✅ ADD THIS!

}