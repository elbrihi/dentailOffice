import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { VisitDto } from '../models/visit-dto';
import { catchError, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class VisitDataSourceService extends RestDataSource{

   constructor(http:HttpClient) {

    super(http)
  }

  postVisit(visit:any,MedicalRecordId:number)
  {
            console.log(visit);
      // /api/create/patient/4/medicalRecords
      //http://localhost:8181/api/create/patient/4/appointment/20/medicalRecords
      const url = `${this.baseUrl}/create/medicalRecord/${MedicalRecordId}/visit`

      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      })

      return this.http.post<VisitDto>(url,visit, {headers}).pipe(
            catchError(err => {
              console.error('Error during visit uopdate:', err);
              return throwError(() => new Error('Failed to create visit.'));
            })
          );
  }

  putVisit(visitData: any, visitId: any) {
    const url = `${this.baseUrl}/update/visit/${visitId}`;
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
      'Content-Type': 'application/ld+json',
    });

    return this.http.put<any>(url, visitData, { headers }).pipe(
      catchError(err => {
        console.error('Error during visit update:', err);
        return throwError(() => new Error('Failed to update visit.'));
      })
    );
  }

  deleteVisit(visitedID:any)
  {
    const url = `${this.baseUrl}/delete/visit/${visitedID}`;
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
      'Content-Type': 'application/ld+json',
    });

    return this.http.delete(url,{headers}).pipe(
      catchError(err => {
        console.error('Error during visit update:', err);
        return throwError(() => new Error('Failed to update visit.'));
      })
    );

  }

  getVisitsByPagination(page: number,itemsPerPage:number)
  {
      console.log("hello world!");
      const url = `${this.baseUrl}/get/visits/by/paginations`;
      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      })
      const params = new HttpParams()
            .set('itemsPerPage',itemsPerPage.toString())
            .set('page',page.toString());
      return this.http.get<VisitDto[]>(url, 
        {params:params,
        headers: headers
        }).pipe(
          catchError(err =>{
             console.error('API error:', err);
        return throwError(() => new Error('Unable to get vists.'));
          })
        )
  }

  getVisitsByParams(page: number,itemsPerPage:number,queryParams: { [param: string]: any })
  {
      const url = `${this.baseUrl}/get/visits/by/paginations`;
            const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      })
     
        // Start with page and itemsPerPage
      let params = new HttpParams()
        .set('page', page.toString())
        .set('itemsPerPage', itemsPerPage.toString());

      // Add custom filter query parameters
      Object.entries(queryParams || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          params = params.set(key, value);
        }
      });

      
      return this.http.get<any>(url, {
          params: params, // ✅ Final combined HttpParams
          headers: headers
        }).pipe(
          catchError(err => {
            console.error('API error:', err);
            return throwError(() => new Error('Unable to get visits.'));
          })
      );

  }

}
