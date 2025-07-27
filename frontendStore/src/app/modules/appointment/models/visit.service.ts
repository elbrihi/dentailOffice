import { Injectable } from '@angular/core';
import { MedicalRecordDto } from '../../patient/models/medical-record-dto';
import { PaymentDto } from '../../payment/models/payment-dto';
import { VisitDto } from './visit-dto';

@Injectable({
  providedIn: 'root'
})
export class VisitService implements VisitDto {

  id: number = 0;
  visitDate: any = '' ;
  notes: string = '';
  amountPaid: number = 0;
  remainingDueAfterVisit:number = 0;
  durationMinutes:number = 0;
  medicalRecord: MedicalRecordDto[] =[];
  createdAt: any;
  createdBy: any;  
  type:string = '';
  payments: PaymentDto[] = [];

}
