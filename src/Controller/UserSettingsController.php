<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

final class UserSettingsController extends AbstractController
{
    #[Route('/user/settings/{section}', name: 'app_user_settings')]
    public function index(Security $security, string $section = 'initial'): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }
        $allowedSections = ['initial', 'general', 'password-reset'];
        if (!in_array($section, $allowedSections)) {
            throw $this->createNotFoundException('The requested settings section does not exist.');
        }

        return $this->render('user_settings/index.html.twig', [
            'user' => $user,
            'section' => $section
        ]);
    }
}
