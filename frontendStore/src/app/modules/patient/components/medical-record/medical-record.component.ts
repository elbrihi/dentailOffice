import { AfterViewInit, Component, inject, OnInit, ViewChild } from '@angular/core';
import { MedicalRecordDataSourceService } from '../../services/medical-record-data-source.service';
import { MedicalRecordDto } from '../../models/medical-record-dto';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatTable, MatTableDataSource } from '@angular/material/table';
import { MatSort } from '@angular/material/sort';
import { MedicalRecord } from '../../models/medical.record.model.service';

@Component({
  selector: 'app-medical-record',
  standalone: false,
  
  templateUrl: './medical-record.component.html',
  styleUrl: './medical-record.component.scss'
})
export class MedicalRecordComponent implements AfterViewInit, OnInit {



 

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
   // @ViewChild(MatTable) table!: MatTable<PatientListItem>;
  listMedicalRecords = new MatTableDataSource<any>();

  
  displayedMedicalRecordsColumns = ['id','visitDate', 'chiefComplaint','clinicalDiagnosis', 'cree par', 'cree a',
                                 'treatmentPlan','followUpDate','prescriptions','notes','actions'
  ]
//  displayedPrescriptionColumns = ['medication']
  displayedPrescriptionColumns = ['medication','dosage','notes']
  medicalRecord: any[] = []

  data = [
    {field: "chief_complaint", value: "egrgeh"},
    {
      field: "visit_date", value: {end_date: "2024-12-30",start_date: "2024-01-01"

    }

    },

  ]

  availableFields = [
    { value: 'lastName', label: 'Nom' },
    { value: 'firstName', label: 'Prénom' },
    { value: 'cni', label: 'CNI' },
    { value: 'notes', label: 'Notes' },
    { value: 'visit_date', label: 'Date de visite' },
    { value: 'birthDate', label: 'Birth date' },
    { value: 'createdAt', label: 'Birth date' },

  ];

  medicalRecordDataSource = inject(MedicalRecordDataSourceService)
  tab(medicalRecord:MedicalRecordDto)
  {
      console.log(medicalRecord)
  }
  ngOnInit(): void{
    console.log("currentMedicalRecordPage",this.currentMedicalRecordPage)

    this.loadMedicalRecords();
        
  }

  filters: any[] = [];
  medicalMedocords: any[] = []
  totalMedicalRecordItem: number = 0;
  currentMedicalRecordPage: number = 1;
  itemsMedicalRecordPerPage: number = 4;
  ngAfterViewInit() {
    this.listMedicalRecords.paginator = this.paginator;
    this.listMedicalRecords.sort = this.sort;
  }



  loadMedicalRecords(): void {
    this.medicalRecordDataSource
      .getMedicalRecordByPagination(this.currentMedicalRecordPage, this.itemsMedicalRecordPerPage)
      .subscribe({
        next: (response: any) => {
          const data = response['hydra:member'] || [];
          this.totalMedicalRecordItem = response['hydra:totalItems'] || data.length;
          this.medicalMedocords = data;
  
          this.listMedicalRecords.data = this.medicalMedocords; // update only data
        },
        error: (err) => console.error('Erreur lors du chargement des données :', err),
      });
  }
  
  onOperatorChange(filter: any) {
    if (filter.operator === 'between') {
      filter.value = { befor: '', end: '' };
    } else {
      filter.value = '';
    }
  }

    
  removeFilter(index: number) {
    this.filters.splice(index, 1);
  }

  addFilter() {
    this.filters.push({ field: '', operator: '', value: '' });
  }

   applyFilters() {
      const queryParams: any = {};
  

      // Send to backend
      /*console.log(this.patientDataSource.getFilterPatientByParms(queryParams).subscribe({
        next: (response:any) =>{
          this.patients =  response['hydra:member']; 
       
          this.listPatient = new MatTableDataSource(this.patients)
        },
        error: (err) => {
          console.error('Error updating patient:', err);
          alert('Error updating patient. Please try again.'); // Or use a snackbar
        }
      }))
      this.patientDataSource.getFilterPatientByParms(queryParams)*/
    }
    resetFilters() {
      this.filters = [];
      this.applyFilters();
    }
  
    get patientColSpan(): number
    {
      return this.displayedMedicalRecordsColumns.length;
    }

    onPageChange(event: PageEvent)
    {
      console.log("totalMedicalRecordItem",this.totalMedicalRecordItem)
      this.itemsMedicalRecordPerPage = event.pageSize; // Update items per page
      this.currentMedicalRecordPage = event.pageIndex + 1; // MatPaginator's pageIndex is zero-based
      this.loadMedicalRecords() 
    }

    onMedicalRecordPageChange(event: PageEvent): void {
      this.itemsMedicalRecordPerPage = event.pageSize;
      this.currentMedicalRecordPage = event.pageIndex + 1; // +1 if your API starts pages from 1
      this.loadMedicalRecords();
    }
}


