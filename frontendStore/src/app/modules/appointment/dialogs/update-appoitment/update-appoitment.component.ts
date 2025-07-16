import { Component, Inject, inject, Injectable, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { AppointmentDataSource } from '../../services/appointment.data.source';
import { AppointmentDto } from '../../models/appointment-dto';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-update-appoitment',
  standalone: false,
  templateUrl: './update-appoitment.component.html',
  styleUrl: './update-appoitment.component.scss'
})
export class UpdateAppoitmentComponent implements OnInit{

  FormAppointmentBuilder: FormGroup;
  fb = inject(FormBuilder);
  constructor(
        public dialogRef: MatDialogRef<UpdateAppoitmentComponent>,
         @Inject(MAT_DIALOG_DATA) public appointment: AppointmentDto 

  )
  {
    this.FormAppointmentBuilder = this.fb.group({
      id: [''],
      appointmentDate: ['', Validators.required],
      reason:['',Validators.required],
      status: [true]
    });
  }

    public appointmentDataSource = inject(AppointmentDataSource);
    
    ngOnInit(): void
    {
      
      
      this.appointmentDataSource.getAppointmentById(this.appointment.id).subscribe({
        next: (appointment: AppointmentDto)=>{

          this.FormAppointmentBuilder.patchValue({
            id: appointment.id,
            appointmentDate: new Date(appointment.appointmentDate),
            reason: appointment.reason,
            status: appointment.status
          })

        }
      });


    }

    submitAppointment(event:Event)
    {
      event.preventDefault();

      const formValue = this.FormAppointmentBuilder.value;


        const appointmentDto = {
          appointment_date: new Date(formValue.appointmentDate).toISOString().slice(0, 10),
          reason: formValue.reason,
          status: formValue.status
      };

      console.log(appointmentDto);
      this.appointmentDataSource.upateAppointment(appointmentDto,this.appointment.id).subscribe({
          next: () =>{
            console.log('Appointment saved updated!');
            this.dialogRef.close(true); // ✅ Correct type
          },

      }
    
    )

    }

    close(){
        this.dialogRef.close();
    }
}
