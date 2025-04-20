import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { MedicalRecord } from '../models/medical.record.model.service';
import { MedicalRecordDto } from '../models/medical-record-dto';
import { Patient } from '../models/patient.service';
import { catchError, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class MedicalRecordDataSourceService extends RestDataSource {

  constructor(http:HttpClient) {

    super(http)
  }

  postMedicalRecord(medicalRecord: MedicalRecordDto,id:number)
  {
      // /api/create/patient/4/medicalRecords
      const url = `${this.baseUrl}/create/patient/${id}/medicalRecords`

      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      })

      return this.http.post<MedicalRecordDto>(url,medicalRecord, {headers}).pipe(
            catchError(err => {
              console.error('Error during patient update:', err);
              return throwError(() => new Error('Failed to update patient.'));
            })
          );
  }
}
