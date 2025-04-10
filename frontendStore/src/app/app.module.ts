import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { CdkDrag, CdkDropList } from '@angular/cdk/drag-drop';
import { NgComponentOutlet } from '@angular/common';

import { StoreModule } from './modules/store/store.module';
import { DemoMaterialModule } from './shared/material-module';
import { VediosComponent } from './pages/vedios.component';
import { TestComponent } from './layout/components/header/test/test.component';
import { provideHttpClient } from '@angular/common/http';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatSortModule } from '@angular/material/sort';
import { AddDialogComponent } from './modules/supplier/dialogs/add/add.dialog/add-dialog.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AddCategoryComponent } from './modules/product/dialogs/add/add-category/add-category.component';
import { UpdateCategoryComponent } from './modules/product/dialogs/update/update-category/update-category.component';



@NgModule({
  declarations: [
    AppComponent,
    VediosComponent,
    TestComponent,
    AddDialogComponent,
    UpdateCategoryComponent,
    

  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    ReactiveFormsModule,
    FormsModule,
    StoreModule,
    AppRoutingModule,
    DemoMaterialModule,
    NgComponentOutlet,
    CdkDropList, CdkDrag, MatTableModule, MatPaginatorModule, MatSortModule,
  ],
  providers: [
    provideAnimationsAsync(),
    provideHttpClient(),

  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
