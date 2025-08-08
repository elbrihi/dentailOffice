import { TestBed } from '@angular/core/testing';

import { DateUtilsServiceTsService } from './date-utils.service.ts.service';

describe('DateUtilsServiceTsService', () => {
  let service: DateUtilsServiceTsService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DateUtilsServiceTsService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
