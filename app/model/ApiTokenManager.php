<?php

namespace App\Model;

class ApiTokenManager
{
    /**
     * @var ConfigManager
     */
    private $configManager;
    /**
     * @var string
     */
    private $secretKey;


    /**
     * ApiTokenManager constructor.
     * @param string $secretKey
     * @param ConfigManager $configManager
     */
    public function __construct($secretKey, ConfigManager $configManager)
    {
        $this->configManager = $configManager;
        $this->secretKey = $secretKey;
    }


    /**
     * @param $token
     * @throws TokenInvalidException
     * @throws \Nette\Utils\JsonException
     */
    public function validateToken($token)
    {
        if (empty($token) || !$this->isTokenValid($token)) {
            throw new TokenInvalidException('Token invalid');
        }
    }


    /**
     * @param $token
     * @return bool
     * @throws \Nette\Utils\JsonException
     */
    private function isTokenValid($token)
    {
        $hash = hash_hmac('sha256', $token, $this->secretKey);

        $hashes = $this->getTokenHashes();

        foreach ($hashes as $hashItem) {
            if ($hashItem['value'] == $hash) {
                return true;
            }
        }

        return false;
    }


    /**
     * @return mixed
     * @throws \Nette\Utils\JsonException
     */
    public function getTokenHashes()
    {
        $hashes = $this->configManager->get('api.token.hashes', []);

        return $hashes;
    }

}
