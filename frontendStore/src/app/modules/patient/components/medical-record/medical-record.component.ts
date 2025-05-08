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

  
  displayedMedicalRecordsColumns = ['id','visitDate', 'chiefComplaint','clinicalDiagnosis', 
                                 'createdBy', 'createdAt',
                                 'treatmentPlan','followUpDate','prescriptions','notes','actions'
  ]

  columnLabels: { [key: string]: string } = {
    id: 'ID',
    visitDate: 'Date de visite',
    chiefComplaint: 'Plainte principale',
    clinicalDiagnosis: 'Diagnostic clinique',
    createdBy: 'Créé par',
    createdAt: 'Créé le',
    treatmentPlan: 'Plan de traitement',
    followUpDate: 'Date de suivi',
    prescriptions: 'Ordonnances',
    notes: 'Notes',
    actions: 'Actions'
  };
//  displayedPrescriptionColumns = ['medication']
  displayedPrescriptionColumns = ['medication','dosage','notes']
  medicalRecord: any[] = []


  availableFields = [
    { value: 'chief_complaint', label: 'Plainte principale' },
    { value: 'visit_date', label: 'Date de visite' },
    { value: 'birthDate', label: 'Birth date' },

  ];

  data = [
    {field: "chief_complaint", value: "egrgeh"},
    {field: "visit_date", value: {end_date: "2024-12-30",start_date: "2024-01-01"}

    },

  ]
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
  
    //const val = new Date("2024-12-30");
    //const strVal = val.toISOString().split('T')[0]; // "2024-12-30"
   // this.filters = this.data
   console.log("filters", this.filters);
    const queryParams: any = {};
    const queryParams1: any = {};
    this.filters.forEach(({ field, value }) => {
      if (typeof value === "object" && value !== null && !Array.isArray(value)) {
        Object.entries(value).forEach(([key, val]) => {
          console.log("val",val)
          queryParams[`${field}_${key}`] = String(val) ;
        });
      } else {
        queryParams[field] = String(value);
      }
    });
   
  
    if (queryParams.visit_date_start_date || queryParams.visit_date_end_date) {
      if (queryParams.visit_date_start_date && queryParams.visit_date_end_date) {
        // Both exist
        queryParams1[`visit_date_start_date`] = queryParams.visit_date_start_date
        queryParams1[`visit_date_end_date`] = queryParams.visit_date_end_date
      } else if (queryParams.visit_date_start_date) {
        // Only start date exists
        queryParams1[`visit_date_start_date`] = queryParams.visit_date_start_date
      } else if (queryParams.visit_date_end_date) {
        queryParams1[`visit_date_start_date`] = queryParams.visit_date_start_date
      }
    }
  
    console.log(this.medicalRecordDataSource.getFilterMedicalRecordByParms(queryParams).subscribe({
      next: (response:any) =>{
        const data = response['hydra:member'] || [];
        const total = response['hydra:totalItems'] || data.length; // Prefer 'hydra:totalItems' if available


        this.listMedicalRecords.data = data;
        this.totalMedicalRecordItem = total;

        console.log("Fetched Medical Records:", data.length);
        console.log("Total Medical Records:", total);
        console.log("Medical Records:", data);
      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    }))
   // this.patientDataSource.getFilterPatientByParms(queryParams)
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


