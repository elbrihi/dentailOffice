export interface MedicalRecord
{
    visitDate: Date| string;
    chiefComplaint: string;
    clinicalDiagnosis: string;
    treatmentPlan: string;
    followUpDate: Date | string
    prescriptions: []
    notes: string
}