<?php

namespace App\Repository;

use App\Entity\Emlak;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Emlak|null find($id, $lockMode = null, $lockVersion = null)
 * @method Emlak|null findOneBy(array $criteria, array $orderBy = null)
 * @method Emlak[]    findAll()
 * @method Emlak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmlakRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emlak::class);
    }

    // /**
    //  * @return Emlak[] Returns an array of Emlak objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Emlak
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    //*******LEFT JOİN WİTH SQL*****************(kullanıcıyla ilişkilendirdik)
    public function getAllEmlaks():array
    {
        $conn=$this->getEntityManager()->getConnection(); //sqlde bağlantı kurdum
        //phpmyadmindeki sql sorgu cümlem
        $sql='SELECT h.*,c.title as catname,u.name,u.surname FROM emlak h       
         JOIN  category c ON c.id = h.category_id
         JOIN  user u ON u.id = h.userid
         ORDER BY c.title ASC
      ';
        $stmt=$conn->prepare($sql);
        $stmt->execute();

        //return on array of arrays (i.e,raw data set)
        return $stmt->fetchAll();
    }
    public function getAllImages($emlakid):array
    {
        $conn=$this->getEntityManager()->getConnection(); //sqlde bağlantı kurdum

        $sql='SELECT image  FROM image        
         WHERE emlak_id = :emlakid
     
      ';
        $query=$conn->prepare($sql);
        $query->execute(['emlakid'=>$emlakid]);
        return $query->fetchAll();




    }
}
