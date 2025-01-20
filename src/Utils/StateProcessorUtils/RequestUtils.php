<?php

declare(strict_types = 1);

namespace App\Utils\StateProcessorUtils;

use App\Contracts\DateAndTimeInterface;
use App\Contracts\RequestDataUtilsInterface;
use App\Utils\InputHelper;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestUtils implements RequestDataUtilsInterface
{

    public function __construct (
        private readonly RequestStack $request,
        private readonly DateAndTimeInterface $dateAndTime,
    )
    {
    }

    /**
     * Checks for the existence of a certain field/key in the request data
     *
     * @param string $key
     * @return bool
     */
    public function keyExistsInRequestData(string $key): bool
    {
        $requestData = json_decode($this->request->getCurrentRequest()->getContent(), true);

        return InputHelper::isValidKeyInArray($key, $requestData);
    }

    /**
     * Sets the updated datetime
     *
     * @param object $object
     * @return void
     * @throws \Exception
     */
    public function setUpdatedAt(object $object): void
    {
        if ( $object->id ) {
            $object->updatedAt = new \DateTimeImmutable('now', $this->dateAndTime->getTimeZone());
        }
    }

}