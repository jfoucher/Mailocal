<?php

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

    public function allOrderedDateDesc($criteria = [])
    {
        $criteria['deletedAt'] = null;
        return $this->findBy($criteria, [
            'created_at' => 'DESC',
            'to' => 'ASC',
        ]);
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
