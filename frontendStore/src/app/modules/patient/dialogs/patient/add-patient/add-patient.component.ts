import { Component, inject } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { PatientDataSource } from '../../../services/patient.service.data.source';
import { Dialog, DialogRef } from '@angular/cdk/dialog';

@Component({
  selector: 'app-add-patient',
  standalone: false,
  
  templateUrl: './add-patient.component.html',
  styleUrl: './add-patient.component.scss'
})
export class AddPatientComponent {
    
    FormBuilder: FormGroup;
    fb =  inject(FormBuilder);;
    dialogRef= inject(DialogRef)

    PatientDataSource = inject(PatientDataSource)


    constructor()
    {
        this.FormBuilder = this.fb.group({
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
        })
    }

    addPatient(event: Event)
    {
    
      const patient = this.FormBuilder.value;
      this.PatientDataSource.savePatient(patient).subscribe({
        next: (response) => {
          console.log('Patient added successfully', response);
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

