<?php

declare(strict_types = 1);

namespace App\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('default')]
final class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            // we're not scheduling command here, but from class designed to run command itself.
//            ->add(
//                RecurringMessage::cron (
//                    // To run our command every mid-night
//                    '0 0 * * *',
//                    new RunCommandMessage('app:send-booking-reminders')
//                )
//            )
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
            // ->lock()
        ;
    }
}
