import { Component, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { PatientDataSource } from '../../../services/patient.service.data.source';
import { Dialog, DialogRef } from '@angular/cdk/dialog';
import { MatDialogRef } from '@angular/material/dialog';

@Component({
  selector: 'app-add-patient',
  standalone: false,
  
  templateUrl: './add-patient.component.html',
  styleUrl: './add-patient.component.scss'
})
export class AddPatientComponent {
    
    FormPatientBuilder: FormGroup;
    fb =  inject(FormBuilder);;
    dialogRef= inject(MatDialogRef)

    PatientDataSource = inject(PatientDataSource)


    constructor()
    {
        this.FormPatientBuilder = this.fb.group({
          firstName: ['', Validators.required],
          lastName: ['', Validators.required],
          email: ['', Validators.required],
          phone: ['', Validators.required],
          cni: ['', Validators.required],
          birthDate: ['', Validators.required],
          gender: ['', Validators.required],
          bloodType: ['', Validators.required],
          address: ['', Validators.required],
          medicalHistory: ['', Validators.required],
          notes: ['', Validators.required],
        })
    }

    addPatient(event: Event)
    {
    
      const patient = this.FormPatientBuilder.value;
      
      let birthDate = this.FormPatientBuilder.value.birthDate;
      birthDate.setDate(birthDate.getDate() + 1);

      birthDate = new Date(this.FormPatientBuilder.value.birthDate).toISOString().slice(0, 10);
      

      this.FormPatientBuilder.value.birthDate =  birthDate

    
      
      this.PatientDataSource.savePatient(patient).subscribe({
        next: (response) => {
          console.log('Patient added successfully', response);
          this.dialogRef.close(true); // <-- close and return "true"
        },
        error: (error) => {
          console.error('Error adding patient', error);
        },
      });
       
    }
    cancelAdding()
    {
      this.dialogRef.close();
    }
}

