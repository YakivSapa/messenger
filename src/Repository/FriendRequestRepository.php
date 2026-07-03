<?php

namespace App\Repository;

use App\Entity\FriendRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<FriendRequest>
 */
class FriendRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendRequest::class);
    }

    public function getPendingRequestsForUser(User $user): array
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.receiver = :user')
            ->andWhere('fr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->orderBy('fr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getPendingRequestsBySender(User $user): array
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.sender = :user')
            ->andWhere('fr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }
    public function canSendRequest(User $sender, User $receiver): bool
    {
        $existingRequest = $this->createQueryBuilder('fr')
            ->where('(fr.sender = :sender AND fr.receiver = :receiver) OR (fr.sender = :receiver AND fr.receiver = :sender)')
            ->andWhere('fr.status = :pending')
            ->setParameter('sender', $sender)
            ->setParameter('receiver', $receiver)
            ->setParameter('pending', 'pending')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $existingRequest === null;
    }

    public function hasPendingRequest(User $user1, User $user2): bool
    {
        $count = $this->createQueryBuilder('fr')
            ->select('COUNT(fr.id)')
            ->where(
                '(fr.sender = :user1 AND fr.receiver = :user2) OR ' .
                '(fr.sender = :user2 AND fr.receiver = :user1)'   
            )
            ->andWhere('fr.status = :status')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
    public function areFriends(User $user1, User $user2): bool
    {
        $count = $this->createQueryBuilder('fr')
        ->select('COUNT(fr.id)')
            ->where(
                '(fr.sender = :user1 AND fr.receiver = :user2) OR ' .
                '(fr.sender = :user2 AND fr.receiver = :user1)'   
            )
            ->andWhere('fr.status = :status')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('status', 'accepted')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
    public function findPendingRequest(User $sender, User $receiver): ?FriendRequest
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.sender = :sender')
            ->andWhere('fr.receiver = :receiver')
            ->andWhere('fr.status = :status')
            ->setParameter('sender', $sender)
            ->setParameter('receiver', $receiver)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function countPendingRequests(User $user): int
    {
        return (int) $this->createQueryBuilder('fr')
            ->select('COUNT(fr.id)')
            ->where('fr.receiver = :user')
            ->andWhere('fr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getPendingRequestsForUserAsArray(User $user): array
    {
        $requests = $this->createQueryBuilder('fr')
            ->where('(fr.sender = :user OR fr.receiver = :user)')
            ->andWhere('fr.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', ['pending', 'declined', 'accepted'])
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($requests as $request){
            $otherUser = ($request->getSender()->getId() === $user->getId())
                ? $request->getReceiver()
                : $request->getSender();
            $indexed[$otherUser->getId()] = $request;
        }
        return $indexed;
    }
}
