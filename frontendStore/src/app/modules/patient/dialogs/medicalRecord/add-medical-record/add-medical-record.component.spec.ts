import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddMedicalRecordComponent } from './add-medical-record.component';

describe('AddMedicalRecordComponent', () => {
  let component: AddMedicalRecordComponent;
  let fixture: ComponentFixture<AddMedicalRecordComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [AddMedicalRecordComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddMedicalRecordComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
