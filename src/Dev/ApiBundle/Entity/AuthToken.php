<?php


namespace Dev\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AuthToken
 * @package Dev\ApiBundle\Entity
 *
 * @ORM\Table(name="AuthTokens")
 * @ORM\Entity(repositoryClass="Dev\ApiBundle\Repository\AuthTokenRepository")
 */
class AuthToken
{
    /**
     * @var integer
     *
     * @ORM\Column(name="AuthTokenID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $authTokenID;

    /**
     * @var string
     *
     * @ORM\Column(name="AuthTokenValue", type="string", nullable=false, length=255, unique=true)
     */
    private $authTokenValue;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="AuthTokenCreatedAt", type="datetime", nullable=false)
     */
    private $authTokenCreatedAt;

    /**
     * @ORM\OneToOne(targetEntity="Dev\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * Get authTokenID
     *
     * @return integer
     */
    public function getAuthTokenID()
    {
        return $this->authTokenID;
    }

    /**
     * Set authTokenValue
     *
     * @param string $authTokenValue
     *
     * @return AuthToken
     */
    public function setAuthTokenValue($authTokenValue)
    {
        $this->authTokenValue = $authTokenValue;

        return $this;
    }

    /**
     * Get authTokenValue
     *
     * @return string
     */
    public function getAuthTokenValue()
    {
        return $this->authTokenValue;
    }

    /**
     * Set authTokenCreatedAt
     *
     * @param \DateTime $authTokenCreatedAt
     *
     * @return AuthToken
     */
    public function setAuthTokenCreatedAt($authTokenCreatedAt)
    {
        $this->authTokenCreatedAt = $authTokenCreatedAt;

        return $this;
    }

    /**
     * Get authTokenCreatedAt
     *
     * @return \DateTime
     */
    public function getAuthTokenCreatedAt()
    {
        return $this->authTokenCreatedAt;
    }

    /**
     * Set user
     *
     * @param  User $user
     *
     * @return AuthToken
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}