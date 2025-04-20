import { DialogRef } from '@angular/cdk/dialog';
import { Component, Inject, inject, OnInit } from '@angular/core';
import { Patient } from '../../../models/patient.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { PatientDataSource } from '../../../services/patient.service.data.source';
import { FormBuilder, FormGroup } from '@angular/forms';
import { PatientDTO } from '../../../models/patient-dto.service';

@Component({
  selector: 'app-update-patient',
  standalone: false,
  
  templateUrl: './update-patient.component.html',
  styleUrl: './update-patient.component.scss'
})
export class UpdatePatientComponent implements OnInit{

  patientForm: FormGroup;
  fb  = inject(FormBuilder)
  patientDataSource=inject(PatientDataSource)
  constructor(
        public dialogRef: DialogRef<UpdatePatientComponent>,
        @Inject(MAT_DIALOG_DATA) public patient: Patient,
        
  ){
    this.patientForm = this.fb.group({
      
      firstName: [''],
      lastName: [''],
      email: [''],
      phone: [''],
      cni: [''],
      birthDate: [''],
      gender: [''],
      bloodType: [''],
      address: [''],
      medicalHistory: [''],
      notes: [''],
      status: [''],
    })
  }

  ngOnInit(): void
  {
    this.patientDataSource.getPatientById(this.patient.id).subscribe({
      next: (patient: PatientDTO) => {
        this.patientForm.patchValue({
          id: patient.id,
          firstName: patient.firstName,
          lastName: patient.lastName,
          email: patient.email,
          phone: patient.phone,
          cni: patient.cni,
          birthDate: patient.birthDate,
          gender: patient.gender,
          bloodType: patient.bloodType,
          address: patient.address,
          medicalHistory: patient.medicalHistory,
          notes: patient.notes,
          status: patient.status
        });
      },
      error: err => {
      }
    });
  }

  updatePatient(event:Event)
  {
   // event.preventDefault();

    const patient = this.patientForm.value
    

     this.patientDataSource.putPatient(patient, this.patient.id).subscribe({
      next: () => {
        console.log(' updated successfully!');
        //this.dialogRef.close(true); // Close dialog and return success flag
      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }

     })
  }
  cancelAdding(){}
}
