<?php

declare(strict_types = 1);

namespace App\Command;

use App\Email\EmailFactory;
use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:send-cart-reminders',
    description: 'Send cart reminder emails',
)]
class SendCartRemindersCommand extends Command
{

    private const int BATCH_SIZE = 20;

    public function __construct (
        private readonly CartRepository $cartRepository,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly EmailFactory $emailFactory,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Sending cart reminders');

        $i = 0;

        $carts = $this->cartRepository->findCartsToRemind();

        foreach ($io->progressIterate($carts) as $cart) {
            assert($cart instanceof Cart);

            $email = $this->emailFactory->createCartReminderEmail($cart);

            $this->mailer->send($email);

            // Mark reminder as sent
            $cart->setReminderSentAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

            $this->em->persist($cart);

            $i++;

            if ($i % self::BATCH_SIZE === 0) {
                $this->em->flush();
                $this->em->clear(); // Detach all entities to free memory
            }
        }

        // Flush remaining changes
        $this->em->flush();
        $this->em->clear();

        $io->success(sprintf('Sent %d cart reminders', $i));

        return Command::SUCCESS;
    }
}
