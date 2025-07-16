import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { VisitDataSourceService } from '../../../services/visit-data-source.service';

@Component({
  selector: 'app-visit-delete',
  standalone: false,
  templateUrl: './visit-delete.component.html',
  styleUrl: './visit-delete.component.scss'
})
export class VisitDeleteComponent {

  constructor(
        public dialogRefer: MatDialogRef<VisitDeleteComponent>,
        public visitDataSourceService: VisitDataSourceService,
        @Inject(MAT_DIALOG_DATA) public data: any
  )
  {

  }

  confirmDelete()
  {
    const visitedID = this.data.visitId;
  
    this.visitDataSourceService.deleteVisit(visitedID).subscribe({

      next: () => {
          this.dialogRefer.close(true); // ✅ Close once with true
        },
        error: (err) => {
          console.error('Error deleting Visit:', err);
          alert('Error deleting Visit. Please try again.');
        }
    })
  }
  close()
  {
    this.dialogRefer.close();
  }

}
