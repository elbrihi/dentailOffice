import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
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

}
