import { TestBed } from '@angular/core/testing';

import { PatientDtoService } from './patient-dto.service';

describe('PatientDtoService', () => {
  let service: PatientDtoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PatientDtoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
