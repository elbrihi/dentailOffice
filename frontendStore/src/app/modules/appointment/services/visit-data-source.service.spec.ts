import { TestBed } from '@angular/core/testing';

import { VisitDataSourceService } from './visit-data-source.service';

describe('VisitDataSourceService', () => {
  let service: VisitDataSourceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(VisitDataSourceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
