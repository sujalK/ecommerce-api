<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Contracts\TokenCreationServiceInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{

    public function __construct (
        private readonly TokenCreationServiceInterface $tokenCreationService,
    )
    {
    }

    #[Route(path: '/api/login', name: 'app_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user = null): Response
    {

        if ( ! $user ) {
            return $this->json([
                'error' => 'Please make sure "Content-Type" header is passed.'
            ], 401);
        }

        // if user is not active then also do not allow to log in into the system
        if (!$user->getIsActive()) {
            return $this->json([
                'error' => 'Account is inactive. Please contact support for further instructions.'
            ], 401);
        }

        // If there is verification token still, then user is not authenticated
        if ( $user->getVerificationToken() !== null || !$user->getVerifiedAt() ) {
            return $this->json([
                'account_verification' => 'Please make sure that your account is verified before logging in',
            ], 401);
        }

        // create token
        $token = $this->tokenCreationService->createToken($user);

        // send token in response
        return $this->json([
            'token' => $token->getToken(),
        ]);
    }

    #[Route(path: '/login', name: 'app_login_form')]
    public function loginForm(): Response
    {

        return $this->render('login/loginForm.html.twig');
    }

}