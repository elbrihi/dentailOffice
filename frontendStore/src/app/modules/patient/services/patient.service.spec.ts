import { TestBed } from '@angular/core/testing';

import { PatientDataSource } from './patient.service.data.source';

describe('PatientService', () => {
  let service: PatientDataSource;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PatientDataSource);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
