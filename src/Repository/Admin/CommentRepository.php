<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Comment;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
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
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    //*******LEFT JOİN WİTH SQL*****************(kullanıcıyla ilişkilendirdik)
    public function getAllComments():array
    {
        $conn=$this->getEntityManager()->getConnection(); //sqlde bağlantı kurdum

        //phpmyadmindeki sql sorgu cümlem
        $sql='
         SELECT C.*,u.name,u.surname,u.id as userid,h.id as emlakid,h.title FROM comment c       
         JOIN  user u ON u.id = c.userid
         JOIN  emlak h ON h.id = c.emlakid
         ORDER BY c.id DESC
      ';
        $stmt=$conn->prepare($sql); // sorguyu hazırlıyor
        $stmt->execute();// sorguyu calıstıyor
        //return on array of arrays (i.e,raw data set)
        return $stmt->fetchAll(); // array halınde donduruyor.
    }

    //*******LEFT JOİN WİTH DOCTRINE*****************(emlakla ilişkilendirdik)
    public function getAllCommentsUser($userid):array  //kullanıcının verileri için userid dedik
    {


        $qb = $this->createQueryBuilder('c')
            ->select('c.id,c.subject,c.comment,c.rate,c.created_at,c.status,c.emlakid,h.title')
            ->Join('App\Entity\Emlak', 'h', 'WITH', 'c.emlakid = h.id')
            ->where('c.userid = :userid')
            ->setParameter('userid', $userid)
            ->orderBy('c.id', 'DESC');

        $query = $qb->getQuery();
        return $query->execute();
    }
}
