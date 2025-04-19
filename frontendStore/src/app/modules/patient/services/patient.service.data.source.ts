import { HttpClient, HttpHandler, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { Observable } from 'rxjs';
import { PatientDTO } from '../models/patient-dto.service';
import { Patient } from '../models/patient.service';

@Injectable({
  providedIn: 'root'
})
export class PatientDataSource extends RestDataSource{

  constructor(http:HttpClient) { 
    super(http)
  }

  getPatientsByPagination(itemsPerPage: number, page: number): Observable<PatientDTO[]>
  {
    const url = `${this.baseUrl}/get/patients/by/paginations`;

    console.log('itemsPerPAge',itemsPerPage)
    const params = new HttpParams()
    .set('itemsPerPage', itemsPerPage.toString())
    .set('page',page.toString())

    console.log("pagination",url,params)
    return this.http.get<PatientDTO[]>(url, {params})
  }
  savePatient(patient:any)
  {

      const url = `${this.baseUrl}/create/new/patient`;

      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      });

      return this.http.post<Patient>(url, patient, {headers})
  }
  
}
