<?php

namespace Absolute\Module\Event\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;
use Absolute\Module\Event\Presenter\EventBasePresenter;

class DefaultPresenter extends EventBasePresenter
{

    /** @var \Absolute\Module\Event\Manager\EventCRUDManager @inject */
    public $eventCRUDManager;

    /** @var \Absolute\Module\Event\Manager\EventManager @inject */
    public $eventManager;

    public function startup()
    {
        parent::startup();
    }

    public function renderDefault($resourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if ($resourceId != null)
                    $this->_getRequest($resourceId);
                else
                    $this->_getListRequest($this->getParameter('offset'), $this->getParameter('limit'));
                break;
            case 'POST':
                $this->_postRequest();
                break;
            case 'PUT':
                $this->_putRequest($resourceId);
                break;
            case 'DELETE':
                $this->_deleteRequest($resourceId);
            default:

                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getRequest($id)
    {
        //if ($this->eventManager->canUserView($id, $this->user->id))
        //{
        $label = $this->eventManager->getById($id);
        if (!$label)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            return;
        }
        $this->jsonResponse->payload = $label->toCalendarJson();
        $this->httpResponse->setCode(Response::S200_OK);
        //}
        //else
        //    $this->httpResponse->setCode(Response::S403_FORBIDDEN);
    }

    private function _getListRequest($offset, $limit)
    {
        //EVENT
        $labels = $this->eventManager->getList($this->user->id, $offset, $limit);
        $this->jsonResponse->payload = array_map(function($n)
        {
            return $n->toCalendarJson();
        }, $labels);
        $this->httpResponse->setCode(Response::S200_OK);
    }

    private function _putRequest($id)
    {
        $post = json_decode($this->httpRequest->getRawBody(), true);
        if ($id == null)
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
        else if ($this->eventManager->canUserEdit($id, $this->user->id))
        {
            $ret = $this->eventCRUDManager->updateWithArray($id, $post);
            if ($ret)
                $this->httpResponse->setCode(Response::S200_OK);
            else
                $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

    private function _postRequest()
    {
        $post = json_decode($this->httpRequest->getRawBody(), true);
        
        $this->_require($post,'title');
        $post=$this->_set($post,'all_day',0);
        $post=$this->_set($post,'start_date',null);
        $post=$this->_set($post,'end_date',null);
        $post=$this->_set($post,'repeat','');
        $post=$this->_set($post,'location','');
        $post=$this->_set($post,'gps_lat',0);
        $post=$this->_set($post,'gps_lng',0);
        $post=$this->_set($post,'note','');
        
        $ret = $this->eventCRUDManager->create(
                $this->user->id, 
                $post['title'], 
                $post['all_day'], 
                $post['start_date'], 
                $post['end_date'], 
                $post['repeat'], 
                $post['location'], 
                $post['gps_lat'], 
                $post['gps_lng'], 
                $post['note']);

        if (!$ret)
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        else
            $this->httpResponse->setCode(Response::S201_CREATED);
    }

    private function _deleteRequest($id)
    {
        if ($this->eventManager->canUserEdit($id, $this->user->id) || true)
        {
            $ret = $this->eventCRUDManager->delete($id);
            if ($ret)
                $this->httpResponse->setCode(Response::S200_OK);
            else
                $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }
        else
        {
            $this->httpResponse->setCode(Response::S403_FORBIDDEN);
        }
    }

}
