import { Component, inject, OnInit } from '@angular/core';
import { InvoiceDataSourceService } from '../../services/invoice-data-source.service';
import { MatTableDataSource } from '@angular/material/table';
import { PageEvent } from '@angular/material/paginator';
import { Router } from '@angular/router';

@Component({
  selector: 'app-invoices-list',
  standalone: false,
  templateUrl: './invoices-list.component.html',
  styleUrl: './invoices-list.component.scss'
})
export class InvoicesListComponent implements OnInit{

  totalInvoicesItem = 0;   // Correct: used for [length]
  currentInvoicePage = 1; // Correct: page number for backend
  itemsInvoicesPerPage = 5; // Default pageSize
  pageIndex = 0;                // Default pageIndex
  
  queryParams:any;

  filters: any[] = []
  availableFields = [
    { value: 'invoiceNumber', label: 'Numéro de facture' },

    { value: 'invoiceDate', label: 'Date de la facture' },
    

  ];


  listInvoices = new MatTableDataSource<any>();

  columnLabels: { [key: string]: string } = {
  id: 'ID',
  lastName: 'Nom',
  firstName: 'Prénom',
  medicalRecord: 'Dossier Médical',
  appointment: 'Rendez-vous',
  cni: 'Numéro de CNI',
  createdBy: 'Créé par',
  sex: 'Sexe',
  phone: 'Téléphone',
  bloodGroup: 'Groupe sanguin',
  notes: 'Notes',
  createdAt: 'Date de création',
  actions: 'Actions',
  chief_complaint: 'Plainte principale',
  clinical_diagnosis: 'Diagnostic clinique',
  follow_up_date: 'Date de suivi',
  treatment_plan: 'Plan de traitement',
  visit_date: 'Date de visite',
  appointmentDate: 'Date de rendez-vous',
  reason: 'Cause',
  visits: 'Les visites',
  visitDate: 'Date de visite',
  amount: 'Montant payé',
  remainingDueAfterVisit: 'Reste dû après visite',
  agreedAmount: 'Montant convenu',
  totalPaid: 'Total payé',
  remainingDue: 'Reste dû',
  invoiceDate: 'Date de la facture',
  invoiceNumber: 'Numéro de facture',
  totalAmount: 'Montant total',
  payments: 'Paiements',
  PatientName: 'Nom du patient',
  PatientCni: 'CNI du patient',
  paymentDate: 'La date de payements'

  };


  displayedInvoicesColumns  = ['id','payments','invoiceNumber','PatientName','PatientCni','invoiceDate','totalAmount','totalPaid','remainingDue','actions']

  expandedPaymentsDetails = ['id','payments','amount','method','paymentDate']
  
  
  data = [
    
        {field: "invoice_date", value:
                             {
                              befor_invoice_date: "2024-12-01",
                              after_invoice_date: "2023-01-01"
                            }

        },{
          field: "invoiceNumber", value:"FAC-20250604-983"
        }
  
  
  ]
  dataDate = [
    
        {field: "invoice_date", value:
                             {
                              befor_invoice_date: "2025-05-29",
                              after_invoice_date: "2025-05-29"
                            }

        }
  
  
  ]
  availableFieldsTest =[
      { value: "invoiceNumber", label:"Numero de la facture"},
      { value: "invoiceDate", label:"Date de la facture"}
  ]
  
  constructor(){
    
  }

  invoiceDataSource=inject(InvoiceDataSourceService);
router = inject(Router);
  ngOnInit()
  {
    this.loadInvoices();
  }


  get paymentColSpan()
  {
    return this.displayedInvoicesColumns.length;
  }
  public loadInvoices()
  {
     this.invoiceDataSource.getInvoicesByPagination(this.currentInvoicePage,this.itemsInvoicesPerPage)
          .subscribe({
            next: (response: any) =>{

              console.log(response[''])
              const data = response['hydra:member'] || [];
              const total = response['hydra:totalItems'] || data.length;

              this.listInvoices.data = data;
              this.totalInvoicesItem = total;

              console.log(this.listInvoices.data, this.totalInvoicesItem)
           
            },
            error: (err) => console.error('Error lors du changments des donnees',err)
          })
    }
    onInvoicePageChange(event:PageEvent): void
    {
        this.itemsInvoicesPerPage = event.pageSize;
        this.currentInvoicePage = event.pageIndex + 1;
        this.pageIndex = event.pageIndex;
  
  
      
        console.log("itemsInvoicesPerPage", this.itemsInvoicesPerPage)
        console.log("currentInvoicePage",this.currentInvoicePage)
        console.log("after load visits")
        this.getInvoicesByParams(this.currentInvoicePage, this.itemsInvoicesPerPage, this.queryParams);
  
    }

    getInvoicesByParams(currentVisitPage:number,itemsInvoicesPerPage:number,queryParams:{})
    {
        this.invoiceDataSource.getInvoicesByParams(currentVisitPage,itemsInvoicesPerPage,queryParams)
              .subscribe({
                next: (response:any) =>
                {
                   console.log(response);
                    const data = response['hydra:member'] || [];
                    const total = response['hydra:totalItems'] || data.length; 

                    this.listInvoices.data = data;
                    this.totalInvoicesItem = total;

                          
                    console.log("Fetched Invoice", data.length);
                    console.log("Total Invoice Records:", total);
                    console.log("Invoices:", data);
                },
                error: () =>{

                }
        })
    }

  addFilter() {

    this.filters.push({value:"",field:"",operator:""})

    console.log("filters",this.filters)
  
  }
  onSelect(str:any)
  {

  }

  onOperatorChange(filter: any) {
    if (filter.operator === 'between') {
      filter.value = {   };
    } else {
      filter.value = '';
    }
  }


  removeFilter(index: number)
  {
     
      this.filters.splice(index,1);
       console.log(this.filters);
  }
  applyFilters()
  {

      console.log(this.filters)
      console.log("data",this.data)
      console.log("dataDate",this.dataDate)
    
      const queryParams: any = {};
      const queryParams1: any = {};
      this.filters.forEach(({ field, value }) => { 
        if (typeof value === "object" && value !== null && !Array.isArray(value))
        {
            Object.entries(value).forEach(([key, val]) => {
            queryParams[`${key}`] = String(val) ;
          });
        }else {
          queryParams[field] = String(value);
        }
      });

      console.log("queryParams",queryParams)
  
      if (queryParams.befor_invoice_date || queryParams.invoice_date_end_date) {
        if (queryParams.befor_invoice_date && queryParams.after_invoice_date) {
          // Both exist
          const befor_invoice_date = new Date(queryParams.befor_invoice_date);
          befor_invoice_date.setDate(befor_invoice_date.getDate() + 1);
          queryParams1[`befor_invoice_date`] = befor_invoice_date.toISOString().slice(0, 10) 

          const after_invoice_date = new Date(queryParams.after_invoice_date);
          after_invoice_date.setDate(after_invoice_date.getDate() + 1);
          queryParams1[`after_invoice_date`] = after_invoice_date .toISOString().slice(0, 10)  

        } else if (queryParams.befor_invoice_date) {
          // Only start date exists

          const befor_invoice_date = new Date(queryParams.befor_invoice_date);
          befor_invoice_date.setDate(befor_invoice_date.getDate() + 1);
          queryParams1[`befor_invoice_date`] = befor_invoice_date.toISOString().slice(0, 10) 
        } else if (queryParams.after_invoice_date) {
          
          const after_invoice_date = new Date(queryParams.after_invoice_date);
          after_invoice_date.setDate(after_invoice_date.getDate() + 1);
          queryParams1[`after_invoice_date`] = after_invoice_date .toISOString().slice(0, 10)  

        }
      }
      Object.keys(queryParams).forEach((key) => {
        if (!key.includes("invoice_date") && queryParams[key]) {
          queryParams1[key] = queryParams[key];
        }
      });
      this.queryParams = queryParams1;
      console.log("queryParams1",this.queryParams)
      this.getInvoicesByParams(this.currentInvoicePage, this.itemsInvoicesPerPage, this.queryParams);

  }
  resetFilters() {
    this.filters = [];
    this.totalInvoicesItem = 0;   // Correct: used for [length]
    this.currentInvoicePage = 1; // Correct: page number for backend
    this.itemsInvoicesPerPage = 5; // Default pageSize
    this.pageIndex = 0;                // Default pageIndex
    this.loadInvoices()
  }

  goToInvoiceDetails(invoiceId:number )
  {
    this.router.navigate(['store', 'invoices', invoiceId, 'details'])
  }
}
