<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
        public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
    public function findByEmailorUsername(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :identifier OR u.username = :identifier')
            ->setParameter('identifier', strtolower($identifier))
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function loadUserByIdentifier(string $identifier): ?User
    {
        $identifier = strtolower(trim($identifier));
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = :identifier')
            ->orWhere('LOWER(u.username) = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function searchUsers(string $query, int $limit = 50): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.displayName LIKE :query')
            ->orWhere('u.username LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.displayName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    public function countVerifiedUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isVerified = :verified')
            ->setParameter('verified', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
