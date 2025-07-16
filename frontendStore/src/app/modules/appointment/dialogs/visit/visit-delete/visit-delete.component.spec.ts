import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VisitDeleteComponent } from './visit-delete.component';

describe('VisitDeleteComponent', () => {
  let component: VisitDeleteComponent;
  let fixture: ComponentFixture<VisitDeleteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [VisitDeleteComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(VisitDeleteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
