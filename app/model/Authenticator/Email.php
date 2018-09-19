<?php

namespace App\Model\Authenticator;

use App\Model\DuplicateNameException;
use App\Model\IdentityManager;
use App\Model\IdentityNotFoundException;
use App\Model\PasswordMismatchException;
use App\Model\TokenInvalidException;
use App\Model\UserNotFoundException;
use App\Orm\Identity;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\Random;

class Email
{
    const PLATFORM_KEY = 'email';

    /**
     * @var IdentityManager
     */
    private $identityManager;


    /**
     * Email constructor.
     * @param IdentityManager $identityManager
     */
    public function __construct(IdentityManager $identityManager)
    {
        $this->identityManager = $identityManager;
    }


    /**
     * @param $email
     * @param $password
     * @return Identity
     * @throws PasswordMismatchException
     * @throws UserNotFoundException
     */
    public function getIdentityByAuth($email, $password)
    {
        try {
            /** @var Identity $identity */
            $identity = $this->getIdentityByEmail($email);
        } catch (IdentityNotFoundException $e) {
            throw new UserNotFoundException();
        }

        $this->verifyIdentityPassword($identity, $password);

        return $identity;
    }


    /**
     * @param $email
     * @param $token
     * @return Identity
     * @throws TokenInvalidException
     * @throws UserNotFoundException
     * @throws \Nette\Utils\JsonException
     */
    public function getIdentityByResetPasswordToken($email, $token)
    {
        try {
            /** @var Identity $identity */
            $identity = $this->getIdentityByEmail($email);
        } catch (IdentityNotFoundException $e) {
            throw new UserNotFoundException();
        }

        $this->verifyIdentityResetPasswordToken($identity, $token);

        return $identity;
    }


    /**
     * @param Identity $identity
     * @param string $password
     * @throws PasswordMismatchException
     */
    protected function verifyIdentityPassword(Identity $identity, $password)
    {
        if (password_verify($password, $identity->token) === false) {
            throw new PasswordMismatchException();
        }
    }


    /**
     * @param Identity $identity
     * @param string $token
     * @throws TokenInvalidException
     * @throws \Nette\Utils\JsonException
     */
    protected function verifyIdentityResetPasswordToken(Identity $identity, $token)
    {
        $details = [];
        if ($identity->identity) {
            $details = Json::decode($identity->identity, Json::FORCE_ARRAY);
        }

        if (!isset($details['resetPassword']['token'])) {
            throw new TokenInvalidException('Token does not exists');
        }

        if ($details['resetPassword']['token'] !== $token) {
            throw new TokenInvalidException("Token mismatch ()");
        }

        $expirationDate = new DateTime($details['resetPassword']['expiration']);
        $now = new DateTime();
        if ($expirationDate < $now) {
            throw new TokenInvalidException('Token already expired');
        }
    }


    /**
     * @param string $email
     * @param string $password
     * @return Identity
     * @throws DuplicateNameException
     */
    public function createNewIdentity($email, $password)
    {
        $identity = null;

        try {
            /** @var Identity $identity */
            $identity = $this->getIdentityByEmail($email);
        } catch (IdentityNotFoundException $e) {
            // required
        }

        if ($identity instanceof Identity) {
            throw new DuplicateNameException();
        }

        $identity = new Identity();

        $identity->platform = self::PLATFORM_KEY;
        $identity->key = $email;
        $identity->token = password_hash($password, PASSWORD_DEFAULT);

        return $identity;
    }


    /**
     * @param $email
     * @return string
     * @throws IdentityNotFoundException
     * @throws \Nette\Utils\JsonException
     */
    public function createResetPasswordToken($email)
    {
        $identity = $this->getIdentityByEmail($email);

        $details = [];
        if ($identity->identity) {
            $details = Json::decode($identity->identity, Json::FORCE_ARRAY);
        }


        $token = Random::generate(30);
        $details['resetPassword'] = [
            'token' => $token,
            'expiration' => date('c', time() + (3600 * 24)),
        ];

        $identity->identity = Json::encode($details);
        $this->identityManager->save($identity);

        return $token;
    }


    /**
     * @param Identity $identity
     * @throws \Nette\Utils\JsonException
     */
    public function invalidateResetPasswordToken(Identity $identity)
    {
        $details = [];
        if ($identity->identity) {
            $details = Json::decode($identity->identity, Json::FORCE_ARRAY);
        }

        unset($details['resetPassword']);

        $identity->identity = Json::encode($details);
        $this->identityManager->save($identity);
    }


    /**
     * @param Identity $identity
     * @param string $password
     */
    public function setPassword(Identity $identity, $password)
    {
        $identity->token = password_hash($password, PASSWORD_DEFAULT);
        $this->identityManager->save($identity);
    }


    /**
     * @param string $email
     * @return Identity
     * @throws IdentityNotFoundException
     */
    protected function getIdentityByEmail($email)
    {
        return $this->identityManager->getIdentity(self::PLATFORM_KEY, $email);
    }

}
