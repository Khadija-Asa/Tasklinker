<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projet>
 *
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * Retourne les projets accessibles par l'utilisateur connecté
     *
     * @param Employe $user
     * @return Projet[]
     */
    public function findProjectsForUser(Employe $user): array
    {
        // Si l'utilisateur est admin, on retourne tous les projets non archivés
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->findBy(['archive' => false]);
        }

        // Sinon, on retourne uniquement les projets assignés à cet employé
        return $this->createQueryBuilder('p')
            ->join('p.employes', 'e')
            ->where('e = :user')
            ->andWhere('p.archive = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
