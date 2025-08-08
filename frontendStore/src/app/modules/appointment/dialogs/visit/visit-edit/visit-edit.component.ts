import { Component, inject, Inject, Input, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { VisitDataSourceService } from '../../../services/visit-data-source.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DateUtilsServiceTsService } from '../../../../../shared/services/date-utils.service.ts.service';

@Component({
  selector: 'app-visit-edit',
  standalone: false,
  templateUrl: './visit-edit.component.html',
  styleUrl: './visit-edit.component.scss'
})
export class VisitEditComponent implements OnInit {

  visitFormBuilder: FormGroup;
  fb = inject(FormBuilder);
  
  visitDataSource = inject(VisitDataSourceService)
  dateUtils = inject(DateUtilsServiceTsService);
  

  totalAgreedAmount!: number;
  totalPaidFromDb: number =0;
  initalTotalPaid: number = 0;
  updatedTotalPaid: number = 0;
  updatedRemainingDue: number = 0;



  constructor(
    public dialogRef: MatDialogRef<VisitEditComponent>,
    @Inject(MAT_DIALOG_DATA) public data:any
  )

  {
  
    this.totalAgreedAmount = this.data.medicalRecord.agreedAmout;
    this.totalPaidFromDb = this.data.medicalRecord.totalPaid;
    this.updatedTotalPaid = this.data.medicalRecord.totalPaid
    console.log(this.data.medicalRecord.agreedAmout)

    this.visitFormBuilder = this.fb.group({

      visit_date: [null, Validators.required],
      type: ['', Validators.required],
      duration_minutes: [null, Validators.required],
      amount_paid: [0, Validators.required],
      notes: ['', Validators.required],
      payment_method: ['', Validators.required],
      payment_date: [null, Validators.required]
       
    })
    
    this.visitFormBuilder.get('amount_paid')?.valueChanges.subscribe((paid: number) =>{
      
      /*const paidValue = Number(paid) || 0;
      this.updatedTotalPaid = this.totalPaidFromDb + paidValue;
      this.updatedRemainingDue = this.totalAgreedAmount - this.updatedTotalPaid;*/

      const amoutPaidControl = this.visitFormBuilder.get('amount_paid');

      console.log("amoutPaidControl",amoutPaidControl?.dirty);

      if(!amoutPaidControl?.dirty)
      {
        const paidValue = Number(paid) || 0;
       // this.updatedTotalPaid = this.totalPaidFromDb + paidValue;
       // this.updatedRemainingDue = this.totalAgreedAmount - this.updatedTotalPaid;
      }else{
        const paidValue = Number(paid) || 0;
        this.updatedTotalPaid = this.initalTotalPaid + paidValue;
        this.updatedRemainingDue = this.totalAgreedAmount - this.updatedTotalPaid;
      }
    })
  
  }
  ngOnInit(){
      console.log("hello")
      console.log(this.data.medicalRecord);
       this.visitFormBuilder.patchValue({
          visit_date: this.dateUtils.getNextDayFromStringToDate(this.data.visit.visitDate)  ,
          type: this.data.visit.type || '',
          duration_minutes: this.data.visit.durationMinutes || 0,
          amount_paid: this.data.visit.amountPaid ,
          notes: this.data.visit.notes || '',
          payment_method: this.data.visit.payments[0].method || '',
          payment_date:  this.dateUtils.getNextDayFromStringToDate(this.data.visit.payments[0].paymentDate)
      })
  }
  updateVisit() {
    let visitID = this.data.visit.id;
    const visitFromdata = this.visitFormBuilder.value;

    const visitData = {
      visit_date:this.dateUtils.getNextDayFromDateToString(visitFromdata.visit_date),
      notes: visitFromdata.notes,
      amount_paid: parseFloat(visitFromdata.amount_paid),
      duration_minutes: parseInt(visitFromdata.duration_minutes),
      type: visitFromdata.type,
      remaining_due_after_visit: this.updatedRemainingDue,
      payments: [
        {
          method: visitFromdata.payment_method,
          payment_date: this.dateUtils.getNextDayFromDateToString(visitFromdata.payment_date),
        },
      ],
    };

    console.log("visitData",visitData)
    this.visitDataSource.putVisit(visitData, visitID).subscribe({
      next: () => {
        this.dialogRef.close(true);
      },
      error: (err) => {
        console.error('Error updating visit:', err);
        alert('Error updating visit. Please try again.');
      },
    });

    console.log('VisitData sent:', visitData);
  }

}
