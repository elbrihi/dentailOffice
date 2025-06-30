import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UpdateAppoitmentComponent } from './update-appoitment.component';

describe('UpdateAppoitmentComponent', () => {
  let component: UpdateAppoitmentComponent;
  let fixture: ComponentFixture<UpdateAppoitmentComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [UpdateAppoitmentComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UpdateAppoitmentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
