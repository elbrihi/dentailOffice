import { Component, inject, OnInit } from '@angular/core';
import { InvoiceDataSourceService } from '../../services/invoice-data-source.service';

@Component({
  selector: 'app-invoices-list',
  standalone: false,
  templateUrl: './invoices-list.component.html',
  styleUrl: './invoices-list.component.scss'
})
export class InvoicesListComponent implements OnInit{

  constructor(){
    
  }

  invoiceDataSource=inject(InvoiceDataSourceService);

  ngOnInit()
  {
    this.loadInvoices();
  }
  public loadInvoices()
  {
      console.log(this.invoiceDataSource.getInvoicesByPagination())
  }
}
