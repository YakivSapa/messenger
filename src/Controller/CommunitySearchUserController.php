<?php

namespace App\Controller;

use App\Repository\FriendRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

final class CommunitySearchUserController extends AbstractController
{
    #[Route('/community/search/user', name: 'app_community_search_user', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, FriendRequestRepository $friendRequestRepository, #[CurrentUser] User $currentUser): Response
    {
        $query = $request->query->get('q', '');
        $users = [];
        $pendingRequests = [];

        if (strlen($query) >= 2) {
            $users = $userRepository->searchUsers($query);
            $pendingRequests = $friendRequestRepository->getPendingRequestsForUserAsArray(
                $currentUser
            );
        }

        // foreach ($users as $user){
        //     $pendingRequests[$user->getId()] = $friendRequestRepository->hasPendingRequest($this->getUser(), $user);
        // }
        // dd($pendingRequests);
        
        return $this->render('community_search_user/index.html.twig', [
            'users' => $users,
            'q' => $query,
            'pendingRequests' => $pendingRequests,
            'currentUser' => $currentUser,
        ]);
    }
}
