import { TestBed } from '@angular/core/testing';

import { AppointmentDataSource } from './appointment.data.source';

describe('AppointmentService', () => {
  let service: AppointmentDataSource;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AppointmentDataSource);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
