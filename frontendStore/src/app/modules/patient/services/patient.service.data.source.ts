import { HttpClient, HttpHandler, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { catchError, map, Observable, tap, throwError } from 'rxjs';
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

    console.log('itemsPerPage',itemsPerPage)
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


  data = [
    {field: "chief_complaint", value: "egrgeh"},
    {field: "visit_date", value: {end_date: "2024-12-30",start_date: "2024-01-01"}

    },

  ]

  getPatientById(id: number): Observable<PatientDTO> {
    const url = `${this.baseUrl}/get/patient/${id}`;
    console.log("url", url);
  
    return this.http.get<PatientDTO>(url).pipe(
      tap(data => console.log('Fetched data:', data)), // Just for logging
      catchError(error => {
        console.error('Error fetching patient data:', error);
        return throwError(() => error);
      })
    );
  }

  putPatient(patient:any,id:number|string)
  {
      //http://localhost:8181/api/update/patient/1

    const url = `${this.baseUrl}/update/patient/${id}`;
               // Set the correct headers to match the API expectations (application/ld+json)
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
      'Content-Type': 'application/ld+json', // Ensure this is set correctly
    });

    console.log("patient",patient)
    return this.http.put(url,patient, {headers}).pipe(
      catchError(err => {
        console.error('Error during patient update:', err);
        return throwError(() => new Error('Failed to update patient.'));
      })
    );
  }
  getFilterPatientByParms(queryParams: { [param: string]: any }) {
    let params = new HttpParams();
  
    // Append only non-null, non-undefined, and non-empty string values
    for (const key in queryParams) {
      const value = queryParams[key];
      if (value !== null && value !== undefined && value !== '') {
        params = params.set(key, value);
      }
    }
  
    // Optional: Log the serialized query string for debugging
    const paramString = params.toString();
    console.log("Serialized Query Params:", paramString);
    console.log("Full URL (debug only):", `${this.baseUrl}/get/patients/by/paginations?${paramString}`);
  
    // Return the GET request with correct types
    return this.http.get<PatientDTO[]>(`${this.baseUrl}/get/patients/by/paginations`, {
      headers: new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
      }),
      params: params // ✅ Correct usage
    }).pipe(
      catchError(err => {
        console.error('API error:', err);
        return throwError(() => new Error('Unable to filter patients.'));
      })
    );
  }
  
  
}
