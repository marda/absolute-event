<?php

namespace Absolute\Module\Event\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class NotificationPresenter extends EventBasePresenter
{

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
                $this->_postEventNotificationRequest($resourceId);
                break;
            case 'PUT':
                $this->_putEventNotificationRequest($resourceId, $subResourceId);
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
        $eventsList = $this->eventManager->getNotificationList($eventId);
        if (!$eventsList)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }
        else
        {
            $this->jsonResponse->payload = array_map(function($n)
            {
                return $n->toCalendarJson();
            }, $eventsList);
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _getEventNotificationRequest($eventId, $notificationId)
    {
        $ret = $this->eventManager->getNotificationById($eventId, $notificationId);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }
        else
        {
            $this->jsonResponse->payload = $ret->toCalendarJson();
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postEventNotificationRequest($urlId)
    {
        if(!isset($urlId)){
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return ;
        }
        $json= json_decode($this->httpRequest->getRawBody(),true);
        $ret = $this->eventManager->createNotification($urlId, $json["notification_date"], $json["value"], $json["type"], $json["period"], $json["sent"]);
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

    private function _putEventNotificationRequest($urlId,$urlId2)
    {
        if(!isset($urlId)||!isset($urlId2)){
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return ;
        }
        $json= json_decode($this->httpRequest->getRawBody(),true);
        $ret = $this->eventManager->updateNotification($urlId,$urlId2, $json);
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

    private function _deleteEventNotificationRequest($urlId, $urlId2)
    {
        if(!isset($urlId)||!isset($urlId2)){
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return ;
        }
        $ret = $this->eventManager->deleteNotification($urlId, $urlId2);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }
        else
        {
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

}
