import { VisitDto } from "../../appointment/models/visit-dto";

export interface  MedicalRecordDto {

  id:number;
  visit_date: any ;
  chief_complaint: string;
  clinical_diagnosis: string;
  treatment_plan: string;
  follow_up_date: any;
  notes: string;
  
  agreedAmout: number;
  totalPaid:number;
  remainingDue:number;


  prescriptions: [];
  visits: VisitDto[] ;

  expandedPrescriptions?: boolean;
}
