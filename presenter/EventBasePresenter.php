<?php

namespace Absolute\Module\Event\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class EventBasePresenter extends BaseRestPresenter
{

    public function startup()
    {
        parent::startup();
        /*if (!$this->user->isAllowed('backend'))
        {
            $this->jsonResponse->payload = (['message' => 'Unauthorized!']);
            $this->httpResponse->setCode(Response::S401_UNAUTHORIZED);
        }*/
    }

    // CONTROL
    // HANDLERS
    // SUBMITS
    // VALIDATION

    function _require($array, $index)
    {
        if (!isset($array[$index]))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            $this->jsonResponse = ["message" => "$index missing"];
            $this->sendResponse(new JsonResponse(
                    $this->jsonResponse->toJson(), "application/json;charset=utf-8"
            ));
        }
    }

    function _set($array, $index, $defaultValue)
    {
        if (!isset($array[$index]))
        {
            $array[$index]=$defaultValue;
        }
        return $array;
    }
    // COMPONENTS
}
