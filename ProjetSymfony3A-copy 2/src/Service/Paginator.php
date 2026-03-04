<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class Paginator
{
    public function paginate(QueryBuilder $qb, int $page = 1, int $limit = 10): array
    {
        $totalItems = count($qb->getQuery()->getResult());
        $pagesCount = ceil($totalItems / $limit);
        
        $items = $qb->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
                    
        return [
            "items" => $items,
            "total" => $totalItems,
            "pages" => (int)$pagesCount,
            "current_page" => $page,
            "limit" => $limit
        ];
    }
}
