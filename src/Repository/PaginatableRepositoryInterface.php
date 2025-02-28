<?php
namespace App\Repository;

use Knp\Component\Pager\Paginator;


// src/Repository/PaginatableRepositoryInterface.php
interface PaginatableRepositoryInterface
{
    public function paginate(int $page, int $limit): Paginator;
}