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
   *  */                          
  listPatient = new MatTableDataSource<Patient>;

  // dependiencnies injectons 
  patientDataSource = inject(PatientDataSource)
  dialog = inject(MatDialog)


  constructor(){}

  ngAfterViewInit(): void {

  }

  ngOnInit(): void {
    this.lengthOfDisplayedPatientColumns = this.displayedMedicalRecordColumns.length
    console.log(this.loadPatiens())
  }
  onPageChange(event: any)
  {
    this.itemsPatientPerPage = event.pageSize; // Update items per page
    this.currentPatientPage = event.pageIndex + 1; // MatPaginator's pageIndex is zero-based
    this.loadPatiens(); // Reload suppliers
  }
  public loadPatiens(): void {
    this.patientDataSource.getPatientsByPagination(this.itemsPatientPerPage,this.currentPatientPage)
    .subscribe({
      next: (response: any) =>{

        this.patients =  response['hydra:member']; 
        console.log("hello",this.patients)
        this.listPatient = new MatTableDataSource(this.patients)
        this.listPatient.sort = this.sort
        this.listPatient.paginator = this.paginator
        this.totalPatientItem = response['hydra:view']?.['hydra:next']
          ? this.currentPatientPage * this.itemsPatientPerPage
          : this.patients.length; // Handle total count dynamically or add API metadata parsing
      }
    }
      
    )
      
  }
  tab(element:any)
  {                                                                                     
      console.log(element)
  }

  openAddPatientDialog()
  {
      let dialog = this.dialog.open(AddPatientComponent,{
        width: '60vw',   // 98% of the viewport width
        height: '95h',  // 95% of the viewport height
        maxWidth: '98vw',
        maxHeight: '98vh',
      
  
      })
  
      this.dialog.afterAllClosed.subscribe(() => this.loadPatiens())
  }

  openUpdatePatientDialog(event:Event,patientId:number|string)
  {
    let dialog = this.dialog.open(UpdatePatientComponent,{
      width: '60vw',   // 98% of the viewport width
      height: '95h',  // 95% of the viewport height
      maxWidth: '98vw',
      maxHeight: '98vh',
      data:{
        id:patientId } as Patient
    }
  
  )

    this.dialog.afterAllClosed.subscribe(() => this.loadPatiens())
  }

  openMedicalRecordDialog(patientId:number)
  {
    
    this.dialog.open(AddMedicalRecordComponent,{
      width: '60vw',   // 98% of the viewport width
      height: '95h',  // 95% of the viewport height
      maxWidth: '98vw',
      maxHeight: '98vh',
      data:{
        id:patientId } as PatientDTO
    })
  }




  get patientColSpan(): number {
    return this.displayedPatientColumns.length;
  }

  openUpdateMedicalRecord(id:any)
  {
      
      this.dialog.open(UpdateMedicalRecordComponent,{      
        width: '60vw',   // 98% of the viewport width
        height: '95h',  // 95% of the viewport height
        maxWidth: '98vw',
        maxHeight: '98vh',
        data:{
          id:id as MedicalRecordDto} 
         })
  }

}
