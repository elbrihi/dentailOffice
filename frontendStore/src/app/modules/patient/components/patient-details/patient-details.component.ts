import { AfterViewInit, Component, inject, OnInit, ViewChild } from '@angular/core';
import { MatTable } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { PatientDetailsDataSource, PatientDetailsItem } from './patient-details-datasource';
import { PatientDataSource } from '../../services/patient.service.data.source';
import { ActivatedRoute } from '@angular/router';
import { PatientDTO } from '../../models/patient-dto.service';
import { Patient } from '../../models/patient.service';

@Component({
  selector: 'app-patient-details',
  templateUrl: './patient-details.component.html',
  styleUrl: './patient-details.component.scss',
  standalone: false
})
export class PatientDetailsComponent implements AfterViewInit,OnInit {
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  @ViewChild(MatTable) table!: MatTable<PatientDetailsItem>;
  patientDataSource = inject(PatientDataSource)
  dataSource = new PatientDetailsDataSource();

  /** Columns displayed in the table. Columns IDs can be added, removed, or reordered. */
  displayedColumns = ['id', 'name'];

  private patientId: number= 0;
  private route = inject(ActivatedRoute);
  public patient: PatientDTO = {} as PatientDTO; 

  prescriptionDisplayedColumns = ['medication','dosage','notes']
  constructor(){

  }
  ngAfterViewInit(): void {
    
  }

  ngOnInit(): void{
      
    this.patientId = parseInt(this.route.snapshot.paramMap.get('patientId')!, 10);

    this.patientDataSource.getPatientById(this.patientId).subscribe(
      { 
        next: (patient: PatientDTO) =>
        {
            this.patient = patient;
            console.log("patientData",this.patient)
        },
        error: err => {
        }
      }
    )

  }
}
