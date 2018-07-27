<?php

namespace Absolute\Module\Event\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class NotificationPresenter extends EventBasePresenter
{

    /** @var \Absolute\Module\Notification\Manager\NotificationManager @inject */
    public $notificationManager;

    /** @var \Absolute\Module\Event\Manager\EventManager @inject */
    public $eventManager;

    public function startup()
    {
        parent::startup();
    }

    //NOTIFICATION

    public function renderDefault($resourceId, $subResourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if (!isset($resourceId))
                    $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
                else
                {
                    if (isset($subResourceId))
                    {
                        $this->_getEventNotificationRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getEventNotificationListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postEventNotificationRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteEventNotificationRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    //Event
    private function _getEventNotificationListRequest($eventId)
    {
        if ($this->eventManager->canUserView($eventId, $this->user->id))
        {
            $eventsList = $this->notificationManager->getEventList($eventId);
            if (!$eventsList)
            {
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            }
            else
            {
                $this->jsonResponse->payload = array_map(function($n)
                {
                    return $n->toJson();
                }, $eventsList);
                $this->httpResponse->setCode(Response::S200_OK);
            }
        }
        else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

    private function _getEventNotificationRequest($eventId, $notificationId)
    {
        if ($this->eventManager->canUserView($eventId, $this->user->id))
        {
            $ret = $this->notificationManager->getEventItem($eventId, $notificationId);
            if (!$ret)
            {
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            }
            else
            {
                $this->jsonResponse->payload = $ret->toJson();
                $this->httpResponse->setCode(Response::S200_OK);
            }
        }
        else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

    private function _postEventNotificationRequest($urlId, $urlId2)
    {
        if ($this->eventManager->canUserView($urlId, $this->user->id))
        {
            $ret = $this->notificationManager->notificationEventCreate($urlId, $urlId2);
            if (!$ret)
            {
                $this->jsonResponse->payload = [];
                $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
            }
            else
            {
                $this->jsonResponse->payload = [];
                $this->httpResponse->setCode(Response::S201_CREATED);
            }
        }
        else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

    private function _deleteEventNotificationRequest($urlId, $urlId2)
    {
        if ($this->eventManager->canUserView($urlId, $this->user->id))
        {
            $ret = $this->notificationManager->notificationEventDelete($urlId, $urlId2);
            if (!$ret)
            {
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            }
            else
            {
                $this->httpResponse->setCode(Response::S200_OK);
            }
        }
        else
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
    }

}
