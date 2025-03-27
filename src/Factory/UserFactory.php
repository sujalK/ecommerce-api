<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct (
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'isActive'   => self::faker()->randomElement([true, false]),
            'email'      => self::faker()->email(),
            'firstName'  => self::faker()->firstName(),
            'lastName'   => self::faker()->lastName(),
            'password'   => '123',
            'roles'      => [],
            'username'   => self::faker()->userName(),
            'isVerified' => self::faker()->randomElement([true, false]),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user): void {
                if ( $user->getPassword() ) {
                    // set the hashed password to the User
                    $user->setPassword (
                        $this->userPasswordHasher->hashPassword($user, $user->getPassword())
                    );
                }
            })
        ;
    }
}
