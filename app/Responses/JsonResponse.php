<?php
declare(strict_types=1);

namespace App\Responses;

use Nette\Application\Responses\JsonResponse as NetteJsonResponse;

class JsonResponse extends NetteJsonResponse
{
    /**
     * @var int
     */
    private $code = \Nette\Http\IResponse::S200_OK;


    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }


    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }


    /**
     * @param \Nette\Http\IRequest $httpRequest
     * @param \Nette\Http\IResponse $httpResponse
     */
    public function send(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse): void
    {
        $httpResponse->setCode($this->code);
        parent::send($httpRequest, $httpResponse);
    }

}
