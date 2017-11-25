<?php
/**
 * Created by PhpStorm.
 * User: home
 * Date: 8/07/17
 * Time: 17:55
 */

namespace Dev\ApiBundle\Security;

use Dev\ApiBundle\Entity\AuthToken;
use Dev\ApiBundle\Entity\User;
use Dev\ApiBundle\Repository\AuthTokenRepository;
use Dev\ApiBundle\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthTokenUserProvider implements UserProviderInterface
{
    /**
     * @var AuthTokenRepository
     */
    protected $authTokenRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(EntityRepository $authTokenRepository, EntityRepository $userRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param $authTokenHeader
     * @return null|AuthToken|object
     */
    public function getAuthToken($authTokenHeader)
    {
        return $this->authTokenRepository->findOneBy(['authTokenValue' => $authTokenHeader]);
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        return $this->userRepository->findOneByUsername($username);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}