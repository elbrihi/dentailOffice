import { Component, inject, Inject, Input } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import {VisitDataSourceService} from '../../../services/visit-data-source.service';
import { VisitDto } from '../../../models/visit-dto';


@Component({
  selector: 'app-visit-add',
  standalone: false,
  templateUrl: './visit-add.component.html',
  styleUrl: './visit-add.component.scss'
})
export class VisitAddComponent {
  formVisitBuilder: FormGroup;
  updatedTotalPaid = 0;
  updatedRemainingDue: number = 0;


  medicalRecordId: number = 0;
  fb = inject(FormBuilder);

  @Input() totalPaidFromDb = 0;
  @Input() totalAgreedAmount = 0;

  constructor(
    public dialogRefer: MatDialogRef<VisitAddComponent>,
    public visitDataSourceService: VisitDataSourceService,
    @Inject(MAT_DIALOG_DATA) public medicalRecord: any
  ) {
    // Assign values from the dialog data
    this.totalPaidFromDb = this.medicalRecord.medicalRecord.totalPaid;
    this.totalAgreedAmount = this.medicalRecord.medicalRecord.remainingDue + this.totalPaidFromDb;
    
    this.medicalRecordId = this.medicalRecord.medicalRecord.id;


    this.formVisitBuilder = this.fb.group({
      visit_date: [null, Validators.required],
      type: ['', Validators.required],
      duration_minutes: [null, Validators.required],
      amount_paid: [0, Validators.required],
      notes: [''],
      payment_method: ['', Validators.required],
      payment_date: [null, Validators.required]
    });

    // Live calculation on amount_paid
    this.formVisitBuilder.get('amount_paid')?.valueChanges.subscribe((paid: number) => {
      this.updatedTotalPaid = this.totalPaidFromDb + Number(paid || 0);
      this.updatedRemainingDue = this.totalAgreedAmount - this.updatedTotalPaid;
    });
  }

  onSubmit(event:Event): void {

    event.preventDefault();
    if (this.formVisitBuilder.valid) {
      const formValue = this.formVisitBuilder.value;
      const payload = {
        visit_date:  new Date(formValue.visit_date).toISOString().slice(0, 10),
        type: formValue.type,
        duration_minutes:parseFloat(formValue.duration_minutes) ,
        amount_paid: parseFloat(formValue.amount_paid) ,
        remaining_due_after_visit:  700,
        notes: formValue.notes,
        payments: [
          {
            method: formValue.payment_method,
            payment_date: new Date(formValue.payment_date).toISOString().slice(0, 10)
          }
        ]
      } ;

      this.visitDataSourceService.postVisit(payload,this.medicalRecordId).subscribe({

      next: () => {
        console.log('Visited successfully!');
        this.dialogRefer.close(true); // Close dialog and return success flag
      },
      error: (err) => {
        console.error('Error creating Visited:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    })
      console.log('Visit Submitted:', payload);
      this.dialogRefer.close(payload); // Optionally close modal
    } else {
      this.formVisitBuilder.markAllAsTouched();
    }
  }
}
