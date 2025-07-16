import { Component, inject, Inject, Input, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { VisitDataSourceService } from '../../../services/visit-data-source.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { VisitDto } from '../../../models/visit-dto';

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

  updatedTotalPaid = 0;
  updatedRemainingDue: number = 0;

  @Input() totalPaidFromDb = 0;
  @Input() totalAgreedAmount = 0;


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
      this.updatedTotalPaid = this.totalPaidFromDb + Number(paid || 0);
      this.updatedRemainingDue = this.totalAgreedAmount - this.updatedTotalPaid;
    })
  
  }
  ngOnInit(){
      console.log("hello")
      console.log(this.data.medicalRecord);
//agreedAmout
       this.visitFormBuilder.patchValue({
          visit_date: this.data.visit.visitDate ,
          type: this.data.visit.type || '',
          duration_minutes: this.data.visit.durationMinutes || 0,
          amount_paid: this.data.visit.amountPaid ,
          notes: this.data.visit.notes || '',
          payment_method: this.data.visit.payments[0].method || '',
          payment_date: this.data.visit.payments[0].paymentDate ? new Date( this.data.visit.payments[0].paymentDate) : null
      })
  }
  updateVisit() {
    let visitID = this.data.visit.id;
    const visitFromdata = this.visitFormBuilder.value;

    const visitData = {
      visit_date: new Date(visitFromdata.visit_date).toISOString().slice(0, 10),
      notes: visitFromdata.notes,
      amount_paid: parseFloat(visitFromdata.amount_paid),
      duration_minutes: parseInt(visitFromdata.duration_minutes),
      type: visitFromdata.type,
      remaining_due_after_visit: this.updatedRemainingDue,
      payments: [
        {
          method: visitFromdata.payment_method,
          payment_date: new Date(visitFromdata.payment_date).toISOString().slice(0, 10),
        },
      ],
    };

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
