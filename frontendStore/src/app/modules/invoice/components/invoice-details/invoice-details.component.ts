import { Component, ElementRef, inject, OnInit, ViewChild } from '@angular/core';
import { InvoiceDataSourceService } from '../../services/invoice-data-source.service';
import { ActivatedRoute, Router } from '@angular/router';
import { MatTableDataSource } from '@angular/material/table';
import { InvoiceDto } from '../../models/invoiceDto';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';


@Component({
  selector: 'app-invoice-details',
  standalone: false,
  templateUrl: './invoice-details.component.html',
  styleUrl: './invoice-details.component.scss'
})
export class InvoiceDetailsComponent implements OnInit{

  invoiceDataSourceService = inject(InvoiceDataSourceService)

  

  invoiceId!: number;

  paymentsList =  new MatTableDataSource<any>();

  invoice: any  = {}

  paymentColumns: string[] = ['amount', 'method', 'paymentDate'];
  @ViewChild('invoiceContent', { static: false }) invoiceContent!: ElementRef;

  constructor(private route: ActivatedRoute)
  {

    this.invoiceId = Number(this.route.snapshot.paramMap.get('invoiceId'));
  
  }
 
  ngOnInit()
  {
     this.invoiceDataSourceService.getInvoiceById(this.invoiceId).subscribe({

      next: (response: any)=>
      {
          this.invoice =  response;
         
          this.paymentsList.data = this.invoice.payments;
      },
      error: (err) =>{

      } 
     })
  }

    downloadPDF() {
    const content = this.invoiceContent.nativeElement;

    html2canvas(content).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p', 'mm', 'a4');

      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      pdf.save(`facture-${this.invoice.invoiceNumber}.pdf`);
    });
  }


}
