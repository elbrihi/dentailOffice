import { AppointmentDto } from '../../appointment/models/appointment-dto';
import { User } from '../../user/models/user';
import { MedicalRecordDto } from './medical-record-dto';

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
  medicalRecord: MedicalRecordDto[] ;
  appointments: AppointmentDto[];
}