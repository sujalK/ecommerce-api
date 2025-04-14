<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{

    public function __construct (
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route(path: '/user/verify/{token}', name: 'app_user_verify', methods: ['GET'])]
    public function verify (
        #[MapEntity(mapping: ['token' => 'verificationToken'])]
        User $user,
    ): Response
    {
        if ($user->getVerificationToken() !== null) {

            // set verificationToken to NULL
            $user->setVerificationToken(null);
            $user->setVerifiedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'status'  => Response::HTTP_OK,
                'message' => 'Your account has been verified.',
            ], Response::HTTP_OK);
        } else {
            return new JsonResponse([
                'success' => false,
                'status'  => Response::HTTP_BAD_REQUEST,
                'message' => 'Invalid request.',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}