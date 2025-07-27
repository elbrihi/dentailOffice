import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { VisitDataSourceService } from '../../../services/visit-data-source.service';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';

@Component({
  selector: 'app-visit-list',
  standalone: false,
  templateUrl: './visit-list.component.html',
  styleUrl: './visit-list.component.scss'
})
export class VisitListComponent implements OnInit{

  visitDataSoource = inject(VisitDataSourceService)

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  totalVisitsItem = 0;   // Correct: used for [length]
  currentVisitPage = 1; // Correct: page number for backend
  itemsVisitsPerPage = 7; // Default pageSize
  pageIndex = 0;                // Default pageIndex
  field: string = '';
  filters: any[] = []; 
  queryParams: any ;

  availableFields = [
    { value: 'visit_date', label: 'Date de visite' },
  ]
  columnLabels: { [key: string]: string } = {
    id: 'ID',
    cni: 'CNI',
    visitors: 'Les visiteurs',
    visitDate: 'Date de visite',
    chiefComplaint: 'Plainte principale',
    clinicalDiagnosis: 'Diagnostic clinique',
    durationMinutes:"La duration",
    createdBy: 'Créé par',
    createdAt: 'Créé le',
    treatmentPlan: 'Plan de traitement',
    followUpDate: 'Date de suivi',
    prescriptions: 'Ordonnances',
    notes: 'Notes',
    actions: 'Actions'
  };
  displayedVisitsColumns = ['id','visitors','visitDate','durationMinutes','notes']
  constructor()
  {

  }

    data = [
    
        {field: "visit_date", value:
                             {
                              befor_visit_date: "2024-12-01",
                              after_visit_date: "2023-01-01"
                            }

    },]

    // {field: "visit_date", value: {befor_visit_date: "2024-01-13",after_visit_date: "2023-01-01"}

  listVisits  = new MatTableDataSource<any>();
  ngOnInit()
  {
     console.log( this.loadVisits());
  }

  onOperatorChange(filter:any)
  {
    if (filter.operator === 'between' || filter.operator === 'befor' || filter.operator === 'after' ) {
      filter.value = {   };
    } else {
      filter.value = '';
    }
  }



  onVisitPageChange(event:PageEvent): void
  {
      this.itemsVisitsPerPage = event.pageSize;
      this.currentVisitPage = event.pageIndex + 1;
      this.pageIndex = event.pageIndex;


    
      console.log("itemsVisitsPerPage", this.itemsVisitsPerPage)
      console.log("currentVisitPage",this.currentVisitPage)
      console.log("after load visits")
      this.getVisitsByParams(this.currentVisitPage, this.itemsVisitsPerPage, this.queryParams);

  }

  loadVisits()
  {  
    console.log("itemsVisitsPerPage", this.itemsVisitsPerPage)
    console.log("currentVisitPage",this.currentVisitPage)
    this.visitDataSoource
        .getVisitsByPagination(this.currentVisitPage, this.itemsVisitsPerPage)
        .subscribe({
          next: (response: any) =>{
              console.log(response);
              const data = response['hydra:member'] || [];
              const total = response['hydra:totalItems'] || data.length; 

              this.listVisits.data = data;
              this.totalVisitsItem = total;
          
              console.log("Fetched Visit Records:", data.length);
              console.log("Total Visit Records:", total);
              console.log("Medical Visits:", data);
          
            },
          error: (err) => console.error('Error lors du changments des donnees',err)
        })
          
      //this.visitDataSoource.
  }

  addFilter() {

    this.filters.push({field:'',value:''});

    console.log("field",this.filters)
  
  }
  applyFilters()
  {

      console.log(this.filters)
      console.log("data",this.data)
    
      const queryParams: any = {};
      const queryParams1: any = {};
      this.data.forEach(({ field, value }) => {
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
  
      if (queryParams.befor_visit_date || queryParams.visit_date_end_date) {
        if (queryParams.befor_visit_date && queryParams.after_visit_date) {
          // Both exist
          queryParams1[`befor_visit_date`] = new Date(queryParams.befor_visit_date).toISOString().slice(0, 10) 
          queryParams1[`after_visit_date`] = new Date(queryParams.after_visit_date).toISOString().slice(0, 10)  
        } else if (queryParams.befor_visit_date) {
          // Only start date exists
          queryParams1[`befor_visit_date`] = new Date(queryParams.befor_visit_date).toISOString().slice(0, 10) 
        } else if (queryParams.after_visit_date) {
          queryParams1[`after_visit_date`] = new Date(queryParams.after_visit_date).toISOString().slice(0, 10)  
        }
      }
    
      this.queryParams = queryParams1;
      console.log("queryParams1",this.queryParams)
      this.getVisitsByParams(this.currentVisitPage, this.itemsVisitsPerPage, this.queryParams);

  }
  getVisitsByParams(currentVisitPage:any, itemsVisitsPerPage:any ,queryParams:any)
  {
    this.visitDataSoource.getVisitsByParams(this.currentVisitPage, this.itemsVisitsPerPage, this.queryParams).subscribe({
        next:(response: any) =>{
          console.log(response);
          const data = response['hydra:member'] || [];
          const total = response['hydra:totalItems'] || data.length; 

          this.listVisits.data = data;
          this.totalVisitsItem = total;
      
          console.log("Fetched Visit Records:", data.length);
          console.log("Total Visit Records:", total);
          console.log("Medical Visits:", data);
        },
        error:() =>{      

        }
      })
  }
  onSelect(str:any)
  {

  }

  removeFilter(index: number)
  {
     
      this.filters.splice(index,1);
       console.log(this.filters);
  }

  resetFilters() {
    this.filters = [];
    this.totalVisitsItem = 0;   // Correct: used for [length]
    this.currentVisitPage = 1; // Correct: page number for backend
    this.itemsVisitsPerPage = 5; // Default pageSize
    this.pageIndex = 0;                // Default pageIndex
    this.loadVisits()
  }
}
