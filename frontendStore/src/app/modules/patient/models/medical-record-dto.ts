
export interface  MedicalRecordDto {

  id:number;
  visit_date: string ;
  chief_complaint: string;
  clinical_diagnosis: string;
  treatment_plan: string;
  follow_up_date: string;
  prescriptions: []
  notes: string;

}
