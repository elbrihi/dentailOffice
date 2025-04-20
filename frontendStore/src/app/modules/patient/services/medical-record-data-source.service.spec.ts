import { TestBed } from '@angular/core/testing';

import { MedicalRecordDataSourceService } from './medical-record-data-source.service';

describe('MedicalRecordDataSourceService', () => {
  let service: MedicalRecordDataSourceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(MedicalRecordDataSourceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
