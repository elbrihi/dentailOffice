import { AfterViewInit, Component, ViewChild } from '@angular/core';
import { MatTable } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { DatabaleTutoDataSource, DatabaleTutoItem } from './databale-tuto-datasource';

@Component({
  selector: 'app-databale-tuto',
  templateUrl: './databale-tuto.component.html',
  styleUrl: './databale-tuto.component.scss',
  standalone: false
})
export class DatabaleTutoComponent implements AfterViewInit {
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  @ViewChild(MatTable) table!: MatTable<DatabaleTutoItem>;
  dataSource = new DatabaleTutoDataSource();

  /** Columns displayed in the table. Columns IDs can be added, removed, or reordered. */
  displayedColumns = ['id', 'name'];

  ngAfterViewInit(): void {
    this.dataSource.sort = this.sort;
    this.dataSource.paginator = this.paginator;
    this.table.dataSource = this.dataSource;
  }
}
