<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\GeneralUserSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Controller\ResetPasswordController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


final class UserSettingsController extends AbstractController
{
    #[Route('/user/settings/{section}', name: 'app_user_settings')]
    public function index(Security $security, Request $request, EntityManagerInterface $entityManager, ResetPasswordController $resetPasswordController, MailerInterface $mailer, TranslatorInterface $translator, string $section = 'initial'): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $currentUser = $security->getUser();
        if (!$currentUser) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }
        $allowedSections = ['initial', 'general', 'password-reset'];
        if (!in_array($section, $allowedSections)) {
            throw $this->createNotFoundException('The requested settings section does not exist.');
        }
        if ($section === 'general') {
            $form = $this->createForm(GeneralUserSettingsType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // dd($form->getData());
                $displayName = $form->get('displayName')->getData();
                $user->setDisplayName($displayName);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Your settings have been updated successfully.');
                return $this->redirectToRoute('app_user_settings', ['section' => 'general']);
            } else if ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash('error', 'There was an error updating your settings. Please check the form and try again.');
            }
        }
        if ($section === 'password-reset') {
            // $form = $this->;
            // $form->handleRequest($request);
            if ($request->isMethod('POST')) {
                // dd($form->getData());

                $this->addFlash('success', 'A password reset email has been sent to your email address. Please check your inbox and follow the instructions to reset your password.');
                return $resetPasswordController->processSendingPasswordResetEmailFromSettings($user, $mailer);
            }
        }

        return $this->render('user_settings/index.html.twig', [
            'user' => $user,
            'section' => $section,
            'form' => $form ?? null,
        ]);
    }
}
