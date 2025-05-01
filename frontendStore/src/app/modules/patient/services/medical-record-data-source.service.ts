import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
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
  getMedicalRecordByPagination(page: number,itemsPerPage:number)
  {

    const url = `${this.baseUrl}/get/medicalRecord/by/pagination`;

        const params = new HttpParams()
        .set('itemsPerPage', itemsPerPage.toString())
        .set('page',page.toString())

        return this.http.get<MedicalRecordDto[]>(url, {params})
  }
  getMedicalRecodById(id: number)
  {
      const url  = `${this,this.baseUrl}/get/medicalRecord/${id}`
      return this.http.get<MedicalRecordDto>(url)
  }

  putMedicalRecord(medicalRecord:MedicalRecordDto, id:number)
  {
       const url = `${this.baseUrl}/update/medicalRecords/${id}`

      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      })
      return this.http.put<MedicalRecordDto>(url,medicalRecord,{headers}).pipe(
        catchError(err => {
          console.error('Error during patient update:', err);
          return throwError(() => new Error('Failed to update patient.'));
        })
      )
  }

  getFilterMedicalRecordByParms(queryParams: { [param: string]: any }) {
    
    let params = new HttpParams();
    
    const paramString = params.toString(); // serialize to URL query string
  
    const fullUrl = `${this.baseUrl}/get/medicalRecord/by/pagination?${paramString}`;
    console.log('Full request URL:', fullUrl);
  
    return this.http.get<MedicalRecordDto[]>(`${this.baseUrl}/get/medicalRecord/by/pagination`, {
      headers: new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
      }),
      params: queryParams
    }).pipe(
      catchError(err => {
        console.error('API error:', err);
        return throwError(() => new Error('Unable to filter medical records.'));
      })
    );
  
  }
  
  
}
