# Pagination
Paginatinon builder

# USAGE: 

```$pagination = new Pagination('nameOfPagination');
$limit = $pagination->getSqlPaginationLimit();
$totalCount = $this->MySQL->getTotalCount(); // Total count of items (MySQL: SQL_CALC_FOUND_ROWS \*)
$html = $pagination->buildPagination($totalCount);
$header = $pagination->getHeader();
$startCounter = $pagination->getStartCounter();```
