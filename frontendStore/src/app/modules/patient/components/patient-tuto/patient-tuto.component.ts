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

  filters: any[] = [];

  availableFields = [
    { value: 'lastName', label: 'Nom' },
    { value: 'firstName', label: 'Prénom' },
    { value: 'cni', label: 'CNI' },
    { value: 'notes', label: 'Notes' },
    { value: 'visit_date', label: 'Date de visite' },
    { value: 'birthDate', label: 'Birth date' },

  ];
    

  constructor(){}

  ngAfterViewInit(): void {
    this.lengthOfDisplayedPatientColumns = this.displayedMedicalRecordColumns.length
    this.loadPatiens(); 
  }

  ngOnInit(): void {
    this.lengthOfDisplayedPatientColumns = this.displayedMedicalRecordColumns.length
    this.loadPatiens(); 
  }
  applyFilters() {
    const queryParams: any = {};

    this.filters.forEach(filter => {
      if (
        filter.field === 'birthDate' ||
        filter.field === 'createdAt' ||
        filter.field === 'medicalRecord.visit_date' ||
        filter.field === 'medicalRecord.follow_up_date'
      ) {
        if (filter.operator === 'between') {
          queryParams[`${filter.field}[after]`] = filter.value.start;
          queryParams[`${filter.field}[before]`] = filter.value.end;
        } else if (filter.operator === 'after') {
          queryParams[`${filter.field}[after]`] = filter.value;
        } else if (filter.operator === 'before') {
          queryParams[`${filter.field}[before]`] = filter.value;
        }
      } else {
        // partial or exact search
        queryParams[filter.field] = filter.value;
      }
    });
  
    console.log("applayfilter",this.filters,queryParams)
    // Send to backend
    console.log(this.patientDataSource.getFilterPatientByParms(queryParams).subscribe({
      next: (response:any) =>{
        this.patients =  response['hydra:member']; 
     
        this.listPatient = new MatTableDataSource(this.patients)
      },
      error: (err) => {
        console.error('Error updating patient:', err);
        alert('Error updating patient. Please try again.'); // Or use a snackbar
      }
    }))
    this.patientDataSource.getFilterPatientByParms(queryParams)
  }
  onOperatorChange(filter: any) {
    if (filter.operator === 'between') {
      filter.value = { befor: '', end: '' };
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

        this.patients =  response['hydra:member']; 
        console.log()
        this.listPatient = new MatTableDataSource(this.patients)
        this.listPatient.sort = this.sort
        this.listPatient.paginator = this.paginator
        this.totalPatientItem = response['hydra:view']?.['hydra:next']
          ? this.currentPatientPage * this.itemsPatientPerPage
          : this.patients.length; // Handle total count dynamically or add API metadata parsing
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
  
  removeFilter(index: number) {
    this.filters.splice(index, 1);
  }
  

  resetFilters() {
    this.filters = [];
    this.applyFilters();
  }
    
}
