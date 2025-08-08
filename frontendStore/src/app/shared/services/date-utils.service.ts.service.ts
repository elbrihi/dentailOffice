import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DateUtilsServiceTsService {

  constructor() { }

    getNextDayFromStringToDate(dateInput: any): Date {
    const date = new Date(dateInput);
    date.setDate(date.getDate() + 1);
    return date;
  }

  getNextDayFromDateToString(dateInput:any)
  {
    let date =  dateInput.setDate(dateInput.getDate() + 1);

    date  = new Date(date).toISOString().slice(0, 10);

    return date;
   
  }
}
