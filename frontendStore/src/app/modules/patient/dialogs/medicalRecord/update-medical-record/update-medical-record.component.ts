import { Component, Inject, inject, OnInit } from '@angular/core';
import { MedicalRecordDataSourceService } from '../../../services/medical-record-data-source.service';
import { MedicalRecordDto } from '../../../models/medical-record-dto';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { FormArray, FormBuilder, FormGroup } from '@angular/forms';

@Component({
  selector: 'app-update-medical-record',
  standalone: false,
  
  templateUrl: './update-medical-record.component.html',
  styleUrl: './update-medical-record.component.scss'
})
export class UpdateMedicalRecordComponent implements OnInit {

  medicalRecordForm: FormGroup;
  fb = inject(FormBuilder)
  dialog = inject(MatDialogRef);
  medicalRecordDatasource = inject(MedicalRecordDataSourceService)

  constructor(
    public dialogRef: MatDialogRef<UpdateMedicalRecordComponent>,
    @Inject(MAT_DIALOG_DATA) public medicalRecord:  MedicalRecordDto,
  )
  {
      this.medicalRecordForm = this.fb.group({
        visit_date: [''],
        chief_complaint: [''],
        clinical_diagnosis: [''],
        treatment_plan: [''],
        follow_up_date: [''],
        prescriptions: this.fb.array([]),
        agreedAmout:[''],
        notes: [''],
      })
  }

  ngOnInit(): void{

    this.medicalRecordDatasource.getMedicalRecodById(this.medicalRecord.id).subscribe({
      
      next: (medicalRecord: any) => {
      
        this.medicalRecordForm.patchValue({
          visit_date: new Date(medicalRecord.visit_date).toISOString().substring(0, 10),
          chief_complaint: medicalRecord.chief_complaint,
          clinical_diagnosis: medicalRecord.clinical_diagnosis,
          treatment_plan: medicalRecord.treatment_plan,
          follow_up_date: new Date(medicalRecord.follow_up_date).toISOString().substring(0, 10),
          prescriptions: medicalRecord.prescriptions,
          agreedAmout: medicalRecord.agreedAmout,
          notes: medicalRecord.notes
          
        })

        // Clear existing prescriptions
        this.prescriptions.clear();

        // Patch prescriptions properly into the FormArray
        medicalRecord.prescriptions.forEach((prescription: any) => {
          this.prescriptions.push(this.fb.group({
            medication: [prescription.medication],
            dosage: [prescription.dosage],
            notes: [prescription.notes]
          }));
        });

        
      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    })
    
  }

  get prescriptions(): FormArray {
    return this.medicalRecordForm.get('prescriptions') as FormArray;
  }


  addPrescription(): void {
    this.prescriptions.push(
      this.fb.group({
        medication: [''],
        dosage: [''],
        notes: ['']
      })
    );

    console.log(this.prescriptions)
  }
  removePrescription(index: number): void {
    this.prescriptions.removeAt(index);
  }

  updateMedicalRecord(event:Event)
  {

    const MedicalRecord = {
      visit_date:  new Date(this.medicalRecordForm.value.visit_date).toISOString().substring(0, 10),
      chief_complaint: this.medicalRecordForm.value.chief_complaint,
      clinical_diagnosis: this.medicalRecordForm.value.clinical_diagnosis,
      treatment_plan: this.medicalRecordForm.value.treatment_plan,
      follow_up_date:  new Date(this.medicalRecordForm.value.follow_up_date).toISOString().substring(0, 10),
      prescriptions: this.medicalRecordForm.value.prescriptions,
      notes: this.medicalRecordForm.value.notes,
      agreedAmout: parseFloat(this.medicalRecordForm.value.agreedAmout) ,
      
    } as MedicalRecordDto

    console.log(MedicalRecord);
    this.medicalRecordDatasource.putMedicalRecord(MedicalRecord, this.medicalRecord.id).subscribe(
      {

        next: () => {
          console.log('Medical Record updated successfully!');
          this.dialogRef.close(true); // Close dialog and return success flag
        },
        error: (err) => {
          console.error('Error updating medical record:', err);
          alert('Error updating medical record. Please try again.'); // Or use a snackbar
        }
      }
    )
    
  }

  reset(): void {
    this.medicalRecordForm.reset();
  }
  
  cancel()
  {
    this.dialogRef.close()
  }
}
