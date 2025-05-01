import { AfterViewInit, Component, inject, OnInit, signal, ViewChild } from '@angular/core';
import { MatTable, MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { PatientListDataSource, PatientListItem } from './patient-list-datasource';
import { PatientDataSource } from '../../services/patient.service.data.source';

@Component({
  selector: 'app-patient-list',
  templateUrl: './patient-list.component.html',
  styleUrl: './patient-list.component.scss',
  standalone: false
})
export class PatientListComponent implements AfterViewInit, OnInit {
 
 
  patients: any[] = []
  totalPatientItem: number = 4;
  currentPatientPage: number = 1;
  itemsPatientPerPage: number = 2;
  readonly id = signal(0);
 
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  @ViewChild(MatTable) table!: MatTable<PatientListItem>;



  dataSource = new PatientListDataSource();

  /** Columns displayed in the table. Columns IDs can be added, removed, or reordered. */
  displayedColumns = ['id', 'name'];
  displayedPatientColumns = ['id','lastName', 'firstName','cni', 'cree par', 'gender','phone','email',
                              'address','bloodType','medicalHistory','notes',
                              'createdAt'
                          ]
  listPatient = new MatTableDataSource<any>;

  // dependiencnies injectons 
  patientDataSource = inject(PatientDataSource)


  constructor(){}

  ngAfterViewInit(): void {

  }

  ngOnInit(): void {
    console.log(this.loadPatiens())
  }
  onPageChange(event: any)
  {
    this.itemsPatientPerPage = event.pageSize; // Update items per page
    this.currentPatientPage = event.pageIndex + 1; // MatPaginator's pageIndex is zero-based
    this.loadPatiens(); // Reload patiens
  }
  public loadPatiens(): void {
    this.patientDataSource.getPatientsByPagination(this.itemsPatientPerPage,this.currentPatientPage)
    .subscribe({
      next: (response: any) =>{

        this.patients =  response['hydra:member']; 
        console.log(this.patients)
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
}
