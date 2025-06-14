import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MedicalRecordComponent } from './medical.record.component';

describe('PaginatorConfigurableExampleComponent', () => {
  let component: MedicalRecordComponent;
  let fixture: ComponentFixture<MedicalRecordComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [MedicalRecordComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MedicalRecordComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
