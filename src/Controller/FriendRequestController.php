<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use App\Entity\FriendRequest;
use App\Repository\FriendRequestRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/friend-request')]
class FriendRequestController extends AbstractController
{
    #[Route('/send/{id}', name: 'app_friend_request_send', methods: ['POST'])]
    public function send(Request $request, User $recipient, #[CurrentUser] ?User $currentUser, FriendRequestRepository $friendRequestRepository, EntityManagerInterface $entityManager): Response
    {
        $query = $request->request->get('q');
        if (!$currentUser){
            return $this->json(['error' => 'Not authenticated'], 401);
        }
        if ($currentUser === $recipient){
            return $this->json(['error' => 'Cannot send friend request to yourself'], 400);
        }
        if ($friendRequestRepository->areFriends($currentUser, $recipient)){
            return $this->json(['error' => 'You are already friends'], 400);
        }
        if ($friendRequestRepository->hasPendingRequest($currentUser, $recipient)){
            return $this->json(['error' => 'Friend request already sent'], 400);
        }
        // if (!$friendRequestRepository->canSendRequest($currentUser, $recipient)) {
        //     $this->addFlash('warning', 'A friend request already exists between you and this user.');
        //     return $this->redirectToRoute('app_community_search_user', ['q' => $query]);
        // }
        try {
            $friendRequest = new FriendRequest();
            $friendRequest->setSender($currentUser);
            $friendRequest->setReceiver($recipient);
            $friendRequest->setStatus('pending');

            $entityManager->persist($friendRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Friend request sent!');
        } catch(UniqueConstraintViolationException $e) {
            $this->addFlash('warning', 'A friend request already exists between you and this user.');
        }
        
        return $this->redirectToRoute('app_community_search_user', ['q' => $query]);
        // return $this->json(['message' => 'Friend request sent'], 201);
    }
    #[Route('/accept/{id}', name: 'app_friend_request_accept', methods: ['POST'])]
    public function accept(Request $request, #[CurrentUser] ?User $currentUser, FriendRequest $friendRequest, EntityManagerInterface $entityManager): Response
    {
        if (!$currentUser){
            return $this->json(['error' => 'Not authenticated'], 401);
        }
        if ($friendRequest->getReceiver()->getId() !== $currentUser->getId()){
            return $this->json(['error' => 'Not authorized'], 403);
        }
        if (!$friendRequest->isPending()){
            return $this->json(['error' => 'The request is not pending'], 400);
        }

        $friendRequest->setStatus('accepted');
        $entityManager->flush();
        
        $query = $request->request->get('q');
        return $this->redirectToRoute('app_community_search_user', ['q' => $query]);

        // return $this->json(['message' => 'Friend request accepted']);
    }
    #[Route('/decline/{id}', name: 'app_friend_request_decline', methods: ['POST'])]
    public function decline(Request $request, #[CurrentUser] ?User $currentUser, FriendRequest $friendRequest, EntityManagerInterface $entityManager): Response
    {
        if (!$currentUser){
            return $this->json(['error' => 'Not authenticated'], 401);
        }
        if ($friendRequest->getReceiver()->getId() !== $currentUser->getId()){
            return $this->json(['error' => 'Not authorized'], 403);
        }
        if (!$friendRequest->isPending()){
            return $this->json(['error' => 'The request is not pending'], 400);
        }

        $friendRequest->setStatus('declined');
        $entityManager->flush();

        $query = $request->request->get('q');
        return $this->redirectToRoute('app_community_search_user', ['q' => $query]);
        // return $this->json(['message' => 'Friend request declinded']);
    }
    #[Route('/pending', name: 'friend_request_pending', methods: ['GET'])]
    public function pending(#[CurrentUser] ?User $currentUser, FriendRequestRepository $friendRequestRepository)
    {
        if (!$currentUser){
            return $this->redirectToRoute('app_login');
        }
        $pendingRequests = $friendRequestRepository->getPendingRequestsForUser($currentUser);

        return $this->render('friend_request/pending.html.twig', [
            'pendingRequests' => $pendingRequests,
        ]);
    }
    #[Route('/cancel/{id}', name: 'app_friend_request_cancel', methods: ['POST'])]
    public function cancel(#[CurrentUser] ?User $currentUser, FriendRequest $friendRequest, EntityManagerInterface $entityManager, Request $request): Response
    {
        $query = $request->request->get('query');
        if (!$currentUser){
            return $this->json(['error' => 'Not authenticated'], 401);
        }
        if ($friendRequest->getSender()->getId() !== $currentUser->getId()){
            return $this->json(['error' => 'Not authorized'], 401);
        }
        if (!$friendRequest->isPending()){
            return $this->json(['error' => 'The request is not pending'], 400);
        }

        $entityManager->remove($friendRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Friend request cancelled');
        return $this->redirectToRoute('app_community_search_user', ['query' => $query]);
    }
    #[Route('/resend/{id}', name: 'app_friend_request_resend', methods: ['POST'])]
    public function resend(User $user, FriendRequestRepository $friendRequestRepository, EntityManagerInterface $em): Response 
    {
        $currentUser = $this->getUser();
        
        // Find the declined request
        $request = $friendRequestRepository->findOneBy([
            'sender' => $currentUser,
            'receiver' => $user,
            'status' => 'declined'
        ]);
        
        if (!$request) {
            throw $this->createNotFoundException('Friend request not found');
        }
        
        // Update status back to pending
        $request->setStatus('pending');
        $em->flush();
        
        $this->addFlash('success', sprintf('Invite resent to %s', $user->getUsername()));
        
        return $this->redirectToRoute('app_community_search_user');
    }
}
