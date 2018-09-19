<?php

namespace App\ApiModule\Presenters;

use App\Model\ApiTokenManager;
use App\Model\TokenInvalidException;
use Nette\Application\UI\Presenter;
use Nette\Http\Response;
use Nextras\Application\UI\SecuredLinksPresenterTrait;

class BasePresenter extends Presenter
{
    use SecuredLinksPresenterTrait;

    /**
     * @var ApiTokenManager
     */
    private $apiTokenManager;


    public function injectApiTokenManager(ApiTokenManager $apiTokenManager)
    {
        $this->apiTokenManager = $apiTokenManager;
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Utils\JsonException
     */
    protected function startup()
    {
        parent::startup();

        $token = $this->getHttpRequest()->getHeader('authtoken');

        try {
            $this->apiTokenManager->validateToken($token);
        } catch (TokenInvalidException $e) {
            $this->sendErrorResponse('Authentication failed', Response::S403_FORBIDDEN);
        }
    }


    /**
     * @param string $message
     * @param int|null $code
     * @throws \Nette\Application\AbortException
     */
    protected function sendErrorResponse($message, $code = null)
    {
        if ($code) {
            $this->getHttpResponse()->setCode($code);
        }

        $this->sendJson([
            'status' => false,
            'errror' => [
                'message' => $message,
            ],
        ]);
    }


    /**
     * @param mixed|null $data
     * @param string|null $message
     * @throws \Nette\Application\AbortException
     */
    protected function sendSuceessResponse($data = null, $message = null)
    {
        $response = [
            'status' => true,
        ];

        if ($data) {
            $response['data'] = $data;
        }
        if ($message) {
            $response['message'] = $message;
        }
        $this->sendJson($response);
    }


}
