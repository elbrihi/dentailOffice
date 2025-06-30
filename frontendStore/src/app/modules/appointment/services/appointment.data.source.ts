import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { AppointmentDto } from '../models/appointment-dto';
import { catchError, tap, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AppointmentDataSource extends RestDataSource{

   constructor(http:HttpClient) { 
    super(http)
  }


  saveAppointment(appointment:any, appointmentId:any)
  {

      const url = `${this.baseUrl}/create/patient/${appointmentId}/appointment`;

      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
        'Content-Type': 'application/ld+json', // Ensure this is set correctly
      });

      return this.http.post<AppointmentDto>(url, appointment, {headers})
  }

  upateAppointment(appointnemt:AppointmentDto,appointmentId:any)
  {
    const url = `${this.baseUrl}/update/appointment/${appointmentId}`;

    const headers= new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
      'Content-Type': 'application/ld+json',
    })

    return this.http.put<AppointmentDto>(url,appointnemt,{headers})
  }

  getAppointmentById(appointmentId:any){
   

    const url = `${this.baseUrl}/get/appointment/by/${appointmentId}`;
        const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
      'Content-Type': 'application/ld+json', // Ensure this is set correctly
    });

      return this.http.get<AppointmentDto>(url,{headers}).pipe(
        tap(data => console.log('Fetched data:', data)), // Just for logging
        catchError(error => {
          console.error('Error fetching patient data:', error);
          return throwError(() => error);
        })
      );
  
  
  }
}
