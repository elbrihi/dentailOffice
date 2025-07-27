import { MedicalRecordDto } from "../../patient/models/medical-record-dto";
import { PaymentDto } from "../../payment/models/payment-dto";

export interface VisitDto
{
    id: number;
    visitDate: any;
    notes: string;
    amountPaid: number;
    remainingDueAfterVisit: number;
    durationMinutes: number;
    type: string;
    createdAt:any;
    createdBy:any;
    payments: PaymentDto[];
    medicalRecord: MedicalRecordDto[];

}