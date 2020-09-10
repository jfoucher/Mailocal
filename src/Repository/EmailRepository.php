<?php

/*
 * This file is part of the Maillocal package.
 *
 * Copyright 2019 Jonathan Foucher
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package Mailocal
 */

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Email|null find($id, $lockMode = null, $lockVersion = null)
 * @method Email|null findOneBy(array $criteria, array $orderBy = null)
 * @method Email[]    findAll()
 * @method Email[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Email::class);
    }

    public function allOrderedDateDesc($criteria = [], $method = 'and')
    {
        $qb = $this->createQueryBuilder('e');
        $i = 1;
        foreach ($criteria as $k => $v) {
            if (strpos($k, 'e.') !== 0) {
                //Make sure start with table name
                $k = 'e.'.$k;
            }
            if($method === 'or') {
                $qb->orWhere($k.$i);
            } else {
                $qb->andWhere($k.$i);
            }
            $qb->setParameter($i, $v);
            $i++;
        }
        $qb->orderBy('e.id', 'DESC');
        $qb->andWhere('e.deletedAt IS NULL');
        return $qb->getQuery()->getResult();
    }

    public function getRecipients()
    {
        $qb = $this->createQueryBuilder('e');

        $qb->groupBy('e.to')->where('e.deletedAt IS NULL')
        ->select('count(e.id) as num_messages')
        ->addSelect('e.to')
        ->orderBy('num_messages', 'DESC')
        ->addOrderBy('e.to', 'ASC')
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }
}
