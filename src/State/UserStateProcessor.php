<?php

declare(strict_types = 1);

namespace App\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User\UserApi;
use App\Email\EmailFactory;
use App\Entity\User;
use App\Enum\ActivityLog;
use App\Service\ActivityLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class UserStateProcessor implements ProcessorInterface
{
    private const array ROLES_TO_REMOVE = ["ROLE_FULL_USER", "ROLE_USER"];

    public function __construct (
        private readonly Security $security,
        private readonly DtoToEntityStateProcessor $processor,
        private readonly ActivityLogService $activityLogService,
        private readonly MicroMapperInterface $microMapper,
        private readonly EntityManagerInterface $entityManager,
        /* Symfony Mailer to send verification email */
        private readonly MailerInterface $mailer,
        private readonly EmailFactory $emailFactory,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        assert($data instanceof UserApi);

        if ($operation instanceof Delete) {

            $user = $this->microMapper->map($data, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);

            // make user inactive on deletion instead of removing user from database.
            $user->setIsActive(false);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return null;
        }

        // Do not allow "non-admin" user to set the roles
        if (!$this->security->isGranted('ROLE_ADMIN') && ($operation instanceof Post || $operation instanceof Patch)) {
            if ($operation instanceof Post) {
                // we can set default roles here
                $data->roles = [];

                // set verification token
                $data->verificationToken = bin2hex(random_bytes(32));

                // Initially, set isActive to true for new users
                $data->isActive = true;


            } else if (isset($data->roles)) {
                $data->roles = [];
            }
        }

        // If admin tries to update roles, then it's possible to update the roles
        if ($operation instanceof Patch && $this->security->isGranted('ROLE_ADMIN')) {
            if (count($data->roles) > 0) {
                // merge previous roles with new roles, and only return unique values
                $roles = array_unique(array_merge($data->roles, $context['previous_data']->roles));

                // remove default roles, and set it to roles property
                $data->roles = array_values(array_diff($roles, self::ROLES_TO_REMOVE));
            }
        }

        $entity = $this->processor->process($data, $operation, $uriVariables, $context);

        // if user is created then send email to verify the account
        if (isset($entity->email) && !$this->security->isGranted('ROLE_ADMIN') && $operation instanceof Post ) {
            // Create email
            $email = $this->emailFactory->createAccountConfirmationEmail($entity);

            $this->mailer->send($email);
        }

        // log
        $this->log($operation, $entity);

        return $entity;
    }

    public function log(Operation $operation, mixed $entity): void
    {
        if ($operation instanceof Post) {
            // log the activity
            $this->activityLogService->logActivity(ActivityLog::CREATE_USER, 'User created', $entity);
        } else if ($operation instanceof Patch) {
            $this->activityLogService->storeLog(ActivityLog::UPDATE_USER, $entity);
        } else if ($operation instanceof Delete) {
            $this->activityLogService->storeLog(ActivityLog::DELETE_USER);
        }
    }
}
