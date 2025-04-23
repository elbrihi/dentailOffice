import { DialogRef } from '@angular/cdk/dialog';
import { Component, Inject, inject } from '@angular/core';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MedicalRecord } from '../../../models/medical.record.model.service';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MedicalRecordDataSourceService } from '../../../services/medical-record-data-source.service';
import { PatientDTO } from '../../../models/patient-dto.service';

@Component({
  selector: 'app-add-medical-record',
  standalone: false,
  
  templateUrl: './add-medical-record.component.html',
  styleUrl: './add-medical-record.component.scss'
})
export class AddMedicalRecordComponent {


  addMedicalDiscoredForm: FormGroup

  dialog= inject(DialogRef);
  fb = inject(FormBuilder)
  medicalRecordDataSource = inject(MedicalRecordDataSourceService);


  constructor(
            public dialogRef: DialogRef<AddMedicalRecordComponent>,
            @Inject(MAT_DIALOG_DATA) public patient:  PatientDTO,
  )
  {
      this.addMedicalDiscoredForm  = this.fb.group({
        visit_date:[this.formatDate('2024-03-19'), Validators.required],
        chief_complaint:[''],
        clinical_diagnosis:[''],
        treatment_plan:[''],
        follow_up_date:['2024-03-19'],
        prescriptions: this.fb.array([]),  // ✅ this is critical
        notes:[''],

      })
  }
  submitMedicalRecord(event:Event)
  {
    this.medicalRecordDataSource.postMedicalRecord(this.addMedicalDiscoredForm.value,this.patient.id).subscribe({

      next: () => {
        console.log('Patient updated successfully!');
        //this.dialogRef.close(true); // Close dialog and return success flag
      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    })
  }
  
  get prescriptions(): FormArray {
    return this.addMedicalDiscoredForm.get('prescriptions') as FormArray;
  }

  addPrescription(): void {
    this.prescriptions.push(
      this.fb.group({
        medication: [''],
        dosage: [''],
        notes: ['']
      })
    );
  }
  removePrescription(index: number): void {
    this.prescriptions.removeAt(index);
  }
  reset(): void {
    this.addMedicalDiscoredForm.reset();
  }
  
  cancel()
  {
    this.dialog.close()
  }

  formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return date.toISOString().substring(0, 10); // '2024-03-19'
  }
}
