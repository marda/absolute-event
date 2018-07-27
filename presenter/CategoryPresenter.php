<?php

namespace Absolute\Module\Event\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;

class CategoryPresenter extends EventBasePresenter
{

    /** @var \Absolute\Module\Category\Manager\CategoryManager @inject */
    public $categoryManager;

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
                        $this->_getCategoryRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getCategoryListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postCategoryRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteCategoryRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getCategoryListRequest($idEvent)
    {
        //if ($this->eventManager->canUserView($idEvent, $this->user->id))
        //{
            $ret = $this->categoryManager->getEventList($idEvent);
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
        //}
        //else
        //    $this->httpResponse->setCode(Response::S403_FORBIDDEN);
    }

    private function _getCategoryRequest($teamId, $labelId)
    {
        //if ($this->eventManager->canUserView($teamId, $this->user->id))
        //{
            $ret = $this->categoryManager->getEventItem($teamId, $labelId);
            if (!$ret)
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            else
            {
                $this->jsonResponse->payload = $ret->toJson();
                $this->httpResponse->setCode(Response::S200_OK);
            }
        //}
        //else
        //    $this->httpResponse->setCode(Response::S403_FORBIDDEN);
    }

    private function _postCategoryRequest($urlId, $urlId2)
    {
        if ($this->eventManager->canUserEdit($urlId, $this->user->id))
        {
            $ret = $this->categoryManager->categoryEventCreate($urlId, $urlId2);
            if (!$ret)
                $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
            else
                $this->httpResponse->setCode(Response::S201_CREATED);
        }else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

    private function _deleteCategoryRequest($urlId, $urlId2)
    {
        if (!isset($urlId) || !isset($urlId2))
        {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }

        if ($this->eventManager->canUserEdit($urlId, $this->user->id))
        {
            $ret = $this->categoryManager->categoryEventDelete($urlId, $urlId2);
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
