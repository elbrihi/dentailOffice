import { User } from '../../user/models/user';
import { MedicalRecord } from './medical.record.model.service';

export interface PatientDTO {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  birthDate: Date;
  gender: string;
  bloodType: string;
  cni:string;
  address: string;
  status:boolean;
  medicalHistory: string;
  notes: string;
  createdAt: Date;
  createdBy: User;
  modifiedAt: Date;
  modifiedBy: User;
  medicalRecords: MedicalRecord[];
}