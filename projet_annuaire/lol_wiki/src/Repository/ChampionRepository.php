<?php

namespace App\Repository;

use App\Entity\Champion;
use App\Entity\Tag;
use App\search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Champion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Champion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Champion[]    findAll()
 * @method Champion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChampionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)

    {
        parent::__construct($registry, Champion::class);
    }

    // /**
    //  * @return Champion[] Returns an array of Champion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Champion
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllChampions(){
        $query = $this->createQueryBuilder('c');
        $query
            ->orderBy('c.nom', 'ASC');

        return $query->getQuery()->getResult();
    }



    public function findWithPhotos($limit) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->groupBy('a.id')
            ->orderBy('a.date', 'DESC')
            ->setMaxResults($limit)
        ;
        return $qb->getQuery()->getResult();

    }

    public function findByLetter($letter){
        $query = $this->createQueryBuilder('c')
            ->where('c.nom LIKE :A')
            ->orderBy('c.nom', 'ASC')
            ->setParameter('A' , ''.$letter.'%')->getQuery();

        return $query->getResult();
    }


    public function search(Search $search){

        return $this->createQueryBuilder('champion')
            ->andWhere('champion.nom LIKE :nom')
            ->setParameter('nom', '%'.$search->getKeyword().'%')
            ->getQuery()
            ->getResult();
    }


}
