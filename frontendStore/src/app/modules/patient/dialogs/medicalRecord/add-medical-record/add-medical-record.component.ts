import { Component, Inject, inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MedicalRecord } from '../../../models/medical.record.model.service';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MedicalRecordDataSourceService } from '../../../services/medical-record-data-source.service';
import { PatientDTO } from '../../../models/patient-dto.service';
import { from } from 'rxjs';
import { MedicalRecordDto } from '../../../models/medical-record-dto';
import { AppointmentDto } from '../../../../appointment/models/appointment-dto';

@Component({
  selector: 'app-add-medical-record',
  standalone: false,
  
  templateUrl: './add-medical-record.component.html',
  styleUrl: './add-medical-record.component.scss'
})
export class AddMedicalRecordComponent {


  addMedicalDiscoredForm: FormGroup

  dialog= inject(MatDialogRef);
  fb = inject(FormBuilder)
  medicalRecordDataSource = inject(MedicalRecordDataSourceService);


  constructor(
            public dialogRef: MatDialogRef<AddMedicalRecordComponent>,
            @Inject(MAT_DIALOG_DATA) public data:  any,
  )
  {
    console.log("data",data);
    this.addMedicalDiscoredForm  = this.fb.group({
        visit_date:[this.formatDate('2024-03-19'), Validators.required],
        chief_complaint:[''],
        clinical_diagnosis:[''],
        treatment_plan:[''],
        follow_up_date:['2024-03-19'],
        prescriptions: this.fb.array([]),  // ✅ this is critical
        agreedAmout:[''],
        notes:[''],
        appointments:['',Validators.required]

      })
  }
  submitMedicalRecord(event:Event)
  {


  
      event.preventDefault();

      const formValue = this.addMedicalDiscoredForm.value;

      let appointmentId =formValue.appointments;
      const medicalRecordDto = {
        visit_date: new Date(formValue.visit_date).toISOString().slice(0, 10),
        chief_complaint: formValue.chief_complaint,
        status: true,
        clinical_diagnosis:  formValue.clinical_diagnosis,
        treatment_plan: formValue.treatment_plan,
        follow_up_date: new Date(formValue.follow_up_date).toISOString().slice(0, 10),
        prescriptions: formValue.prescriptions,
        notes: formValue.notes,
        agreedAmout: parseFloat (formValue.agreedAmout),

      } ;


      console.log("formValue",formValue.appointments )
     this.medicalRecordDataSource.postMedicalRecord(medicalRecordDto,this.data.patientId,appointmentId).subscribe({

      next: () => {
        console.log('Patient updated successfully!');
        this.dialogRef.close(true); // Close dialog and return success flag
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
  get appointments(): AppointmentDto[]
  {
    console.log("hello world",this.data.appintment)
    return this.data.appointment;
   
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
