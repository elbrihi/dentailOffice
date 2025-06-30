import { VisitDto } from "../../appointment/models/visit-dto";

export interface PaymentDto
{
    id:number;
    amount: number;
    method:string;
    paymentDate:any;
    visit:VisitDto;

}