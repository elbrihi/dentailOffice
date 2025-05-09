import { Injectable } from '@angular/core';
import { PatientDTO } from './patient-dto.service';
import { MedicalRecord } from './medical.record.model.service';
import { MedicalRecordDto } from './medical-record-dto';

@Injectable({
  providedIn: 'root'
})
export class Patient implements PatientDTO {


  id: number = 0;
  firstName: string = '';
  lastName: string = '';
  email: string = '';
  phone: string = '';
  birthDate: Date = new Date();
  gender: string = '';
  bloodType: string = '';
  status: boolean = true;
  cni: string = '';
  address: string = '';
  medicalHistory: string = '';
  notes: string = '';
  createdAt: Date = new Date();
  createdBy: any;
  modifiedAt: Date = new Date();
  modifiedBy: any;
  medicalRecord: MedicalRecordDto[] = [];

  // UI state
  expanded: boolean = false;

  // Pagination-related fields
  paginatedMedicalRecords: MedicalRecord[] = [];
  currentPage: number = 1;
  medicalRecordsItemsPerPage: number = 5;

 
}
