import { AfterViewInit, Component, inject, OnInit, ViewChild } from '@angular/core';
import { MedicalRecordDataSourceService } from '../../services/medical-record-data-source.service';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';

@Component({
  selector: 'app-paginator-configurable-example',
  standalone: false,
  
  templateUrl: './paginator-configurable-example.component.html',
  styleUrl: './paginator-configurable-example.component.scss'
})
export class PaginatorConfigurableExampleComponent implements OnInit, AfterViewInit{

  
  filters: any[] = [];
  medicalMedocords: any[] = []
  totalMedicalRecordItem = 0;   // Correct: used for [length]
  currentMedicalRecordPage = 1; // Correct: page number for backend
  itemsMedicalRecordPerPage = 3; // Default pageSize
  pageIndex = 0;                // Default pageIndex

  @ViewChild(MatPaginator) paginator!: MatPaginator;

  listMedicalRecords = new MatTableDataSource<any>();

  displayedMedicalRecordsColumns = ['id','visitDate', 'chiefComplaint','clinicalDiagnosis', 'cree par', 'cree a',
    'treatmentPlan','followUpDate','prescriptions','notes','actions'
  ]
  //  displayedPrescriptionColumns = ['medication']
  displayedPrescriptionColumns = ['medication','dosage','notes']


  medicalRecordDataSource = inject(MedicalRecordDataSourceService)

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
  
  onOperatorChange(filter: any) {
    if (filter.operator === 'between') {
      filter.value = {   };
    } else {
      filter.value = '';
    }
  }

  removeFilter(index: number) {
    this.filters.splice(index, 1);
  }

  
  ngOnInit(): void
  { 
    
    console.log(this.loadMedicalRecords())
  }

  ngAfterViewInit(): void
  {
    //this.listMedicalRecords.paginator = this.paginator; // << Connect paginator to table

  }


  loadMedicalRecords(): void {
    this.medicalRecordDataSource
      .getMedicalRecordByPagination(this.currentMedicalRecordPage, this.itemsMedicalRecordPerPage)
      .subscribe({
        next: (response: any) => {
          const data = response['hydra:member'] || [];
          const total = response['hydra:totalItems'] || data.length; // Prefer 'hydra:totalItems' if available


          this.listMedicalRecords.data = data;
          this.totalMedicalRecordItem = total;

          console.log("Fetched Medical Records:", data.length);
          console.log("Total Medical Records:", total);
          console.log("Medical Records:", data);
        },
        error: (err) => console.error('Erreur lors du chargement des données :', err),
      });
  }

  onMedicalRecordPageChange(event: PageEvent): void {

    console.log("Page Event:", event);

    this.itemsMedicalRecordPerPage = event.pageSize;
    this.currentMedicalRecordPage = event.pageIndex + 1; // API expects page 1-based
    this.pageIndex = event.pageIndex;

    this.loadMedicalRecords();
  }

  get patientColSpan(): number
  {
    return this.displayedMedicalRecordsColumns.length;
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

}
