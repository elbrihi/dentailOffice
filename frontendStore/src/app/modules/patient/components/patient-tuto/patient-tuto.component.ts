import { AfterViewInit, Component, inject, OnInit, signal, ViewChild } from '@angular/core';
import { MatTable, MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { PatientDataSource } from '../../services/patient.service.data.source';
import { PatientListDataSource, PatientListItem } from '../patient-list/patient-list-datasource';
import { Patient } from '../../models/patient.service';
import { Dialog } from '@angular/cdk/dialog';
import { AddPatientComponent } from '../../dialogs/patient/add-patient/add-patient.component';
import { UpdatePatientComponent } from '../../dialogs/patient/update-patient/update-patient.component';
import { MatDialog } from '@angular/material/dialog';
import { AddMedicalRecordComponent } from '../../dialogs/medicalRecord/add-medical-record/add-medical-record.component';
import { PatientDTO } from '../../models/patient-dto.service';
import { UpdateMedicalRecordComponent } from '../../dialogs/medicalRecord/update-medical-record/update-medical-record.component';
import { MedicalRecordDto } from '../../models/medical-record-dto';
import { Router } from '@angular/router';

@Component({
  selector: 'app-patient-list',
  templateUrl: './patient-tuto.component.html',
  styleUrl: './patient-tuto.component.scss',
  standalone: false
})
export class PatientTutoComponent implements AfterViewInit, OnInit {
 
 
  patients: any[] = []
  totalPatientItem: number = 4;
  currentPatientPage: number = 1;
  itemsPatientPerPage: number = 2;
  readonly id = signal(0);
 
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  @ViewChild(MatTable) table!: MatTable<PatientListItem>;

  lengthOfDisplayedPatientColumns: number = 0; 

  dataSource = new PatientListDataSource();

  /** Columns displayed in the table. Columns IDs can be added, removed, or reordered. */
  displayedColumns = ['id', 'name'];
  displayedPatientColumns = ['id','lastName', 'firstName','cni', 'cree par', 'gender','phone','email',
                              'address','bloodType','medicalHistory','notes','createdAt','actions'
                          ]
  displayedMedicalRecordColumns = ['id','chief_complaint','clinical_diagnos', 'follow_up_date',
    'notes','treatment_plan','visit_date','actions'
                         ]
  /***
  displayedMedicalRecordColumns = ['id','chief_complaint','clinical_diagnos',
                                    'createdAt','createdBy','follow_up_date','modifiedAt',
                                    'modifiedBy','notes','prescriptions','treatment_plan','visit_date'
                                  ]
  **/                          
  listPatient = new MatTableDataSource<Patient>;

  // dependiencnies injectons 
  patientDataSource = inject(PatientDataSource)
  dialog = inject(MatDialog)
  router = inject(Router);

  filters: any[] = [];

  availableFields = [
    { value: 'lastName', label: 'Nom' },
    { value: 'firstName', label: 'Prénom' },
    { value: 'cni', label: 'CNI' },
    { value: 'createdAt', label: 'la date de la creation' },

  ];
    
  data = [
    {field: "cni", value: "A789012"},
    {field: "createdAt", value: {end_date: "2025-12-20",start_date: "2025-01-01"}

    },

  ]

  constructor(){}

  ngAfterViewInit(): void {
    this.lengthOfDisplayedPatientColumns = this.displayedMedicalRecordColumns.length
    this.loadPatiens(); 
  }

  ngOnInit(): void {
    this.lengthOfDisplayedPatientColumns = this.displayedMedicalRecordColumns.length
    this.loadPatiens(); 
  }

  onOperatorChange(filter: any) {
    if (filter.operator === 'between') {
      filter.value = {   };
    } else {
      filter.value = '';
    }
  }
  onPageChange(event: any)
  {
    this.itemsPatientPerPage = event.pageSize; // Update items per page
    this.currentPatientPage = event.pageIndex + 1; // MatPaginator's pageIndex is zero-based
    this.loadPatiens() 
  }
  public loadPatiens(): void {

    
    this.patientDataSource.getPatientsByPagination(this.itemsPatientPerPage,this.currentPatientPage)
    .subscribe({
      next: (response: any) =>{

        const data = response['hydra:member'] || [];
        const total = response['hydra:totalItems'] || data.length; // Prefer 'hydra:totalItems' if available

        this.listPatient.data = data;
        this.totalPatientItem = total;


        console.log("Fetched Medical Records:", data.length);
        console.log("Total Medical Records:", total);
        console.log("Medical Records:", data);
      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    }
      
    )
      
  }
  tab(element:any)
  {                                                                                     
  }

  openAddPatientDialog()
  {
    const dialogRef = this.dialog.open(AddPatientComponent,{
        width: '60vw',   // 98% of the viewport width
        height: '95h',  // 95% of the viewport height
        maxWidth: '98vw',
        maxHeight: '98vh',
      
  
      })
  
      dialogRef.afterClosed().subscribe(result => {
        if (result) {
          this.loadPatiens() 
        }
      });
  }

  openUpdatePatientDialog(event:Event,patientId:number|string)
  {
    const dialogRef = this.dialog.open(UpdatePatientComponent,{
      width: '60vw',   // 98% of the viewport width
      height: '95h',  // 95% of the viewport height
      maxWidth: '98vw',
      maxHeight: '98vh',
      data:{
        id:patientId } as Patient
    }
  
  )

    dialogRef.afterClosed().subscribe(result => {
      if (result) {

        console.log("result",result)
        this.loadPatiens() 
      }
    });
  }

  openMedicalRecordDialog(patientId:number)
  {
    
    const dialogRef = this.dialog.open(AddMedicalRecordComponent,{
      width: '60vw',   // 98% of the viewport width
      height: '95h',  // 95% of the viewport height
      maxWidth: '98vw',
      maxHeight: '98vh',
      data:{
        id:patientId } as PatientDTO
    })

    dialogRef.afterClosed().subscribe(result => {
      if (result) {
        this.loadPatiens() 
      }
    });
  }




  get patientColSpan(): number {
    return this.displayedPatientColumns.length;
  }

  openUpdateMedicalRecord(id:any)
  {     
    const dialogRef = this.dialog.open(UpdateMedicalRecordComponent,{      
        width: '60vw',   // 98% of the viewport width
        height: '95h',  // 95% of the viewport height
        maxWidth: '98vw',
        maxHeight: '98vh',
        data:{
          id:id as MedicalRecordDto} 
         })
         this.loadPatiens() 
  }

  addFilter() {
    this.filters.push({ field: '', operator: '', value: '' });
  }
  applyFilters() {
  
    //const val = new Date("2024-12-30");
    //const strVal = val.toISOString().split('T')[0]; // "2024-12-30"
    console.log("filters", this.filters);
    const queryParams: any = {};
    this.filters.forEach(({ field, value }) => {
      if (typeof value === "object" && value !== null && !Array.isArray(value)) {
        Object.entries(value).forEach(([key, val]) => {
          queryParams[`${field}_${key}`] = String(val) ;
        });
      } else {
        queryParams[field] = String(value);
      }
    });
   
  
    console.log(this.patientDataSource.getFilterPatientByParms(queryParams).subscribe({
      next: (response:any) =>{
        const data = response['hydra:member'] || [];
        const total = response['hydra:totalItems'] || data.length; // Prefer 'hydra:totalItems' if available
        this.listPatient.data = data;
        this.totalPatientItem = total;

        console.log("patient data",data)

      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    }))
   // this.patientDataSource.getFilterPatientByParms(queryParams)
  }
  removeFilter(index: number) {
    this.filters.splice(index, 1);
  }
  

  resetFilters() {
    this.filters = [];
    this.applyFilters();
  }
  goToDetails(patientId: number) {
    this.router.navigate(['store', 'patients', patientId, 'details']);
  }
}
