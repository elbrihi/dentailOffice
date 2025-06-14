import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './modules/store/components/dashboard/dashboard.component';
import { LoginComponent } from './modules/auth/components/login/login.component';
import { LogoutComponent } from './modules/auth/components/logout/logout.component';
import { CommentsComponent } from './pages/comments/comments.component';
import { VediosComponent } from './pages/vedios.component';
import { AnalyticsComponent } from './pages/analytics/analytics.component';
import { StoreComponent } from './modules/store/store.component';
import { AuthGuardService } from './core/guards/auth.guard.service';
import { SupplierListComponent } from './modules/supplier/components/supplier-list/supplier-list.component';
import { CategoryListComponent } from './modules/product/components/category-list/category-list.component';
import { CategoryProductListComponent } from './modules/product/components/product-list/category-product-list.component';
import { DatatableSubitemComponent } from './modules/product/components/datatable-subitem/datatable-subitem.component';
import { DatatableTutoComponent } from './modules/product/components/datatable-tuto/datatable-tuto.component';
import { PatientListComponent } from './modules/patient/components/patient-list/patient-list.component';
import { MedicalRecordComponent } from './modules/patient/components/medical-record/medical.record.component';

const routes: Routes = [
  {
    path: '',
    pathMatch: 'full',
    redirectTo: 'store', // Default redirect to 'store'
    
  },
  { 
    path: 'login', 
    component: LoginComponent 
  },
  { 
    path: 'store', 
    component: StoreComponent,
    canActivate: [AuthGuardService],
    children: [
      {
        path: 'dashboard',
        component: DashboardComponent
      },
      {
        path: 'content',
        children: [
          {
            path: 'vedios', // Correct spelling
            component: VediosComponent // Ensure the component name matches
          },
          {
            path: 'analytics',
            component: AnalyticsComponent
          }
        ]
      },
      {
        path: 'patients', 

       
        children: [
          
          {
            path: 'patiens',
            component: PatientListComponent
          },

          {
            path: 'medical-records',
            component: MedicalRecordComponent
          },

        
        ]
      },


      {
        path: 'comments',
        component: CommentsComponent
      }
    ]
  },
  { 
    path: 'logout', 
    component: LogoutComponent 
  },
  { 
    path: '**', 
    redirectTo: 'store' // Catch-all route at the end
  }
];
@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
