<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use App\Service\MyClient;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private MyClient $client;

    public function __construct(MyClient $client)
    {
        $this->client = $client;
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @throws UserNotFoundException if the user is not found
     * @throws ExceptionInterface
     */
    public function loadUserByIdentifier($identifier): UserInterface
    {
        // Load a User object from your data source or throw UserNotFoundException.
        // The $identifier argument may not actually be a username:
        // it is whatever value is being returned by the getUserIdentifier()
        // method in your User class.
        $content = json_decode($this->client->getUserByEmail($identifier));
        $userContent = $content->user;
        if (false === $userContent) {
            throw new UserNotFoundException('User not found by identifier');
        }
        $user = new User();
        $user->setId($userContent->id);
        $user->setEmail($userContent->email);
        $user->setTitle($userContent->title);
        $user->setFirstName($userContent->first_name);
        $user->setLastName($userContent->last_name);
        $user->setPhone($userContent->phone);
        $user->setPrefix($userContent->prefix);
        $user->setCountry($userContent->country);
        $user->setNewsletter($userContent->newsletter);
        $user->setPassword($userContent->password);

        return $user;
    }

    /**
     * @deprecated since Symfony 5.3, loadUserByIdentifier() is used instead
     */
    public function loadUserByUsername($username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     * 
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }
        
        $freshUser = $this->loadUserByIdentifier($user->getUserIdentifier());
        
        if ($freshUser != $user) {
            throw new UserNotFoundException('User data has been changed.');
        }
        
        return $user;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Upgrades the hashed password of a user, typically for using a better hash algorithm.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // TODO: when hashed passwords are in use, this method should:
        // 1. persist the new password in the user storage
        // 2. update the $user object with $user->setPassword($newHashedPassword);
    }
}
