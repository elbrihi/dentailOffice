
export class PaginatorService<T> {
  items: T[] = [];
  paginatedItems: T[] = [];
  currentPage: number = 1;
  itemsPerPage: number = 5;
  
}
