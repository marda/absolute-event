<?php

namespace Absolute\Module\Event\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Core\Presenter\BaseRestPresenter;

class TeamPresenter extends EventBasePresenter
{

    /** @var \Absolute\Module\Team\Manager\TeamManager @inject */
    public $teamManager;

    /** @var \Absolute\Module\Event\Manager\EventManager @inject */
    public $eventManager;

    public function startup()
    {
        parent::startup();
    }

    //LABEL

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
                        $this->_getTeamRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getTeamListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postTeamRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteTeamRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getTeamListRequest($idEvent)
    {
        //if ($this->eventManager->canUserView($idEvent, $this->user->id))
        {
            $ret = $this->teamManager->getEventList($idEvent);
            if (!$ret)
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            else
            {
                $this->jsonResponse->payload = array_map(function($n)
                {
                    return $n->toJson();
                }, $ret);
                $this->httpResponse->setCode(Response::S200_OK);
            }
        }
        //else
        //    $this->httpResponse->setCode(Response::S403_FORBIDDEN);
    }

    private function _getTeamRequest($eventId, $teamId)
    {
        //if ($this->eventManager->canUserView($eventId, $this->user->id))
        {
            $ret = $this->teamManager->getEventItem($eventId, $teamId);
            if (!$ret)
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            else
            {
                $this->jsonResponse->payload = $ret->toJson();
                $this->httpResponse->setCode(Response::S200_OK);
            }
        }
        //else
        //    $this->httpResponse->setCode(Response::S403_FORBIDDEN);
    }

    private function _postTeamRequest($urlId, $urlId2)
    {
        if ($this->eventManager->canUserEdit($urlId, $this->user->id))
        {
            $ret = $this->teamManager->teamEventCreate($urlId, $urlId2);
            if (!$ret)
                $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
            else
                $this->httpResponse->setCode(Response::S201_CREATED);
        }else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

    private function _deleteTeamRequest($urlId, $urlId2)
    {
        if ($this->eventManager->canUserEdit($urlId, $this->user->id))
        {
            $ret = $this->teamManager->teamEventDelete($urlId, $urlId2);
            if (!$ret)
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            else
                $this->httpResponse->setCode(Response::S200_OK);
        }else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

}
