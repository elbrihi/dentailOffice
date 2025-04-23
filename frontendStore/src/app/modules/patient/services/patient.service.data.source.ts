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
  getFilterPatientByParms(queryParams: { [param: string]: string | number | boolean })
  {
     //http://localhost:8181/api/get/patients

     let params = new HttpParams();
          Object.keys(queryParams).forEach(key => {
            const value = queryParams[key];

            // Handle nested or object-like values (optional, for safety)
            if (typeof value === 'object' && value !== null) {
              params = params.set(key, JSON.stringify(value));
            } else {
              params = params.set(key, String(value));
            }

      });
      console.log("params__",params)
      const paramString = params.toString(); // ✅ serializes all params

      const fullUrl = `${this.baseUrl}/get/patients?${paramString}`;
      console.log('Full request URL:', fullUrl);

     return this.http.get(`${this.baseUrl}/get/patients`, {
       headers: new HttpHeaders({
         'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
       }),
       params: params
     }).pipe(
       catchError(err => {
         console.error('API error:', err);
         return throwError(() => new Error('Unable to filter patients.'));
       })
     );
  }
  
}
