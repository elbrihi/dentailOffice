import { DialogRef } from '@angular/cdk/dialog';
import { Component,Inject,inject } from '@angular/core';
import { FormBuilder,FormGroup, Validators } from '@angular/forms';
import { AppointmentDto } from '../../../models/appointment-dto';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { AppointmentDataSource } from '../../../services/appointment.data.source';
import { PatientDTO } from '../../../../patient/models/patient-dto.service';

@Component({
  selector: 'app-add-appoitment',
  standalone: false,
  templateUrl: './add-appoitment.component.html',
  styleUrl: './add-appoitment.component.scss'
})


export class AddAppoitmentComponent{

    FormAppointmentBuilder: FormGroup;

    fb= inject(FormBuilder);
    
    appintementDataSource = inject(AppointmentDataSource);
    constructor(
            public dialogRef: MatDialogRef<AddAppoitmentComponent>,
            @Inject(MAT_DIALOG_DATA) public patient:  PatientDTO,
    )
    {

      console.log(this.patient.id)
      
        this.FormAppointmentBuilder = this.fb.group({
          appointment_date:['',Validators.required],
          reason:[''],
          status: true
        })
    }

    submitAppointment(event: Event): void {
     // event.preventDefault();

      const formValue = this.FormAppointmentBuilder.value;

      const appointmentDto = {
        appointment_date: new Date(formValue.appointment_date).toISOString().slice(0, 10),
        reason: formValue.reason,
        status: formValue.status
      };

      const patientId = this.patient?.id; // this might be undefined if you're creating

      this.appintementDataSource.saveAppointment(appointmentDto, patientId).subscribe({
        next: () => {
          console.log('Appointment saved successfully!');
          this.dialogRef.close(true); // ✅ Correct type
        },
        error: (err) => {
          console.error('Error saving appointment:', err);
          alert('Error saving appointment. Please try again.');
        }
      });
    }


  close(){
    this.dialogRef.close();
  }


}
