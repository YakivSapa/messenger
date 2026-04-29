<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UsersListController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(UserRepository $repository): Response
    {
        return $this->render('users_list/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }
}
