import { Injectable } from '@angular/core';
import { RestDataSource } from '../../../core/services/rest-data-source.service';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class InvoiceDataSourceService extends RestDataSource {

  constructor(http: HttpClient)
  { 
    super(http)
  }

  public getInvoiceById(InvoiceId:any)
  {
      const url = ``
  }

  public getInvoicesByPagination()
  {
        const url = `${this.baseUrl}/get/invoices/by/pagination`;

        console.log(url)
  }
}
