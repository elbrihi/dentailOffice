import { TestBed } from '@angular/core/testing';

import { InvoiceDataSourceService } from './invoice-data-source.service';

describe('InvoiceDataSourceService', () => {
  let service: InvoiceDataSourceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(InvoiceDataSourceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
