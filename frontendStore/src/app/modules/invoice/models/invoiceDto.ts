import { MedicalRecordDto } from "../../patient/models/medical-record-dto";
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
    medicalRecord: MedicalRecordDto[];

}

// 'invoiceDate', 'invoiceNumber', 'remainingDue', 'totalAmount', 'totalPaid', 'payments'