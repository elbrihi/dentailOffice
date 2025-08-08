import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { InvoiceDto } from '../models/invoiceDto';
import { catchError, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class InvoiceDataSourceService extends RestDataSource {


  constructor(http: HttpClient)
  { 
    super(http)
  }

  public getInvoiceById(invoiceId:any)
  {
      const url = `${this.baseUrl}/get/invoice/${invoiceId}`

      const headers = new HttpHeaders({
          'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
          'Content-Type': 'application/ld+json', 
      })

      return this.http.get<InvoiceDto[]>(url,
          {
            headers:headers
          }
        ).pipe(
          catchError(
            err =>{
              console.error('API error:', err);
              return throwError(() => new Error('Unable to get vists.'));
            }
          )
        )

  }

  public getInvoicesByPagination(page:number,itemsPerPage:number)
  {
        const url = `${this.baseUrl}/get/invoices/by/pagination`;

        const params = new HttpParams()
                        .set('itemsPerPage',itemsPerPage.toString())
                        .set('page',page.toString())
        
        const headers = new HttpHeaders({
          'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
          'Content-Type': 'application/ld+json', 
        })

        return this.http.get<InvoiceDto[]>(url,
          {
            params:params,
            headers:headers
          }
        ).pipe(
          catchError(
            err =>{
              console.error('API error:', err);
              return throwError(() => new Error('Unable to get vists.'));
            }
          )
        )

  }
  getInvoicesByParams(page: number,itemsPerPage:number,queryParams: { [param: string]: any })
  {
      const url = `${this.baseUrl}/get/invoices/by/pagination`;
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
