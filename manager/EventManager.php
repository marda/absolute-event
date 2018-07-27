<?php

namespace Absolute\Module\Event\Manager;

use Absolute\Core\Manager\BaseManager;
use Nette\Database\Context;
use Absolute\Module\Event\Classes\Event;
use Absolute\Module\Event\Classes\Notification;
use Absolute\Module\Team\Manager\TeamManager;
use Absolute\Module\User\Manager\UserManager;
use Absolute\Module\Category\Manager\CategoryManager;

class EventManager extends BaseManager
{

    /** @var \Absolute\Module\Team\Manager\TeamManager  */
    public $teamManager;

    /** @var \Absolute\Module\Category\Manager\CategoryManager */
    public $categoryManager;

    /** @var \Absolute\Module\User\Manager\UserManager */
    public $userManager;

    public function __construct(Context $database, TeamManager $teamManager, CategoryManager $categoryManager, UserManager $userManager)
    {
        parent::__construct($database);
        $this->teamManager = $teamManager;
        $this->categoryManager = $categoryManager;
        $this->userManager = $userManager;
    }

    protected function _getEvent($db)
    {
        if ($db == false)
        {
            return false;
        }
        $object = new Event($db->id, $db->user_id, $db->title, $db->all_day, $db->start_date, $db->end_date, $db->repeat, $db->location, $db->gps_lat, $db->gps_lng, $db->note, $db->created);
        foreach ($db->related('event_user') as $userDb)
        {
            $user = $this->userManager->getUser($userDb->user);
            if ($user)
            {
                $object->addUser($user);
            }
        }
        foreach ($db->related('event_team') as $teamDb)
        {
            $team = $this->teamManager->getTeam($teamDb->team);
            if ($team)
            {
                $object->addTeam($team);
            }
        }
        foreach ($db->related('event_category') as $categoryDb)
        {
            $category = $this->categoryManager->getCategory($categoryDb->category);
            if ($category)
            {
                $object->addCategory($category);
            }
        }
        foreach ($db->related('event_notification') as $eventDb)
        {
            $notification = $this->_getNotification($eventDb);
            if ($notification)
            {
                $object->addNotification($notification);
            }
        }
        return $object;
    }

    protected function _getNotification($db)
    {
        if ($db == false)
        {
            return false;
        }
        $object = new Classes\Notification($db->id, $db->value, $db->notification_date, $db->type, $db->period, $db->sent, $db->created);
        return $object;
    }

    /* INTERNAL/EXTERNAL INTERFACE */

    public function _getById($id)
    {
        $resultDb = $this->database->table('event')->get($id);
        return $this->_getEvent($resultDb);
    }

    private function _getList($userId, $offset, $limit)
    {
        $ret = array();
        $resultDb = $this->database->table('event')->limit($limit, $offset);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            if ($this->_canUserEdit($object->getId(), $userId))
                $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserList($userId)
    {
        $ret = array();
        $resultDb = $this->database->table('event')->where('user_id', $userId);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectList($userId)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'role');
        $ret = array();
        $resultDb = $this->database->table('event')->where(':project_event.project_id', array_keys($projects));
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $projectDb = $db->related('project_event')->fetch();
            if ($projectDb && array_key_exists($projectDb->project_id, $projects))
            {
                $role = $projects[$projectDb->project_id];
                if ($role != "manager" && $role != "owner")
                {
                    $object->setEditable(false);
                }
            }
            else
            {
                $object->setEditable(false);
            }
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getRangeList($startDate, $endDate)
    {
        $ret = array();
        $resultDb = $this->database->table('event')->where('start_date BETWEEN ? AND ?', $startDate, $endDate);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getProjectRangeList($projectId, $startDate, $endDate)
    {
        $ret = array();
        $resultDb = $this->database->table('event')->where('start_date BETWEEN ? AND ?', $startDate, $endDate)->where(":project_event.project_id", $projectId);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserRangeList($userId, $startDate, $endDate)
    {
        $ret = array();
        $resultDb = $this->database->table('event')->where('user_id', $userId)->where('start_date BETWEEN ? AND ?', $startDate, $endDate);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectRangeList($userId, $startDate, $endDate)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'role');
        $ret = array();
        $resultDb = $this->database->table('event')->where(':project_event.project_id', array_keys($projects))->where('start_date BETWEEN ? AND ?', $startDate, $endDate);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $projectDb = $db->related('project_event')->fetch();
            if ($projectDb && array_key_exists($projectDb->project_id, $projects))
            {
                $role = $projects[$projectDb->project_id];
                if ($role != "manager" && $role != "owner")
                {
                    $object->setEditable(false);
                }
            }
            else
            {
                $object->setEditable(false);
            }
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserDateList($userId, $date)
    {
        $ret = array();
        $resultDb = $this->database->table('event')->where('user_id', $userId)->where('DATE(start_date) = DATE(?) OR DATE(end_date) = DATE(?) OR DATE(?) BETWEEN DATE(start_date) AND DATE(end_date)', $date, $date, $date);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectDateList($userId, $date)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'role');
        $ret = array();
        $resultDb = $this->database->table('event')->where(':project_event.project_id', array_keys($projects))->where('DATE(start_date) = DATE(?) OR DATE(end_date) = DATE(?) OR DATE(?) BETWEEN DATE(start_date) AND DATE(end_date)', $date, $date, $date);
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectRecentList($userId)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'role');
        $ret = array();
        $resultDb = $this->database->table('event')->where(':project_event.project_id', array_keys($projects))->where('UNIX_TIMESTAMP(created) > (UNIX_TIMESTAMP(NOW()) - 24 * 60 * 60)');
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getProjectDateCount($projectId, $date)
    {
        return $this->database->table('event')->where(':project_event.project_id', $projectId)->where('DATE(start_date) = DATE(?) OR DATE(end_date) = DATE(?) OR DATE(?) BETWEEN DATE(start_date) AND DATE(end_date)', $date, $date, $date)->count("*");
    }

    private function _getUserThisWeekCount($userId)
    {
        return $this->database->table('event')->where('event.user_id ? OR :event_user.user_id ?', $userId, $userId)->where('YEARWEEK(start_date) = YEARWEEK(NOW()) OR YEARWEEK(end_date) = YEARWEEK(NOW()) OR YEARWEEK(NOW()) BETWEEN YEARWEEK(start_date) AND YEARWEEK(end_date)')->count("DISTINCT(event.id)");
    }

    private function _getUserPlannedCount($userId)
    {
        return $this->database->table('event')->where('event.user_id ? OR :event_user.user_id ?', $userId, $userId)->where('DATE(start_date) > DATE(NOW())')->count("DISTINCT(event.id)");
    }

    private function _getUnsentNotification()
    {
        $ret = array();
        $resultDb = $this->database->table('event')->where(':event_notification.sent', false)->where(':event_notification.notification_date <= NOW()');
        foreach ($resultDb as $db)
        {
            $object = $this->_getEvent($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _canUserEdit($id, $userId)
    {
        $db = $this->database->table('event')->get($id);
        if (!$db)
        {
            return false;
        }
        if ($db->user_id === $userId)
        {
            return true;
        }
        $projectsInManagement = $this->database->table('project_user')->where('user_id', $userId)->where('role', array('owner', 'manager'))->fetchPairs('project_id', 'project_id');
        $projects = $this->database->table('project_event')->where('event_id', $id)->fetchPairs('project_id', 'project_id');
        return (!empty(array_intersect($projects, $projectsInManagement))) ? true : false;
    }

    private function _getNotificationList($eventId)
    {
        
    }

    private function _getNotificationItem($eventId, $notificationId)
    {
        
    }

    private function _createNotification($notificationId, $array)
    {
        
    }

    private function _updateNotification($notificationId, $array)
    {
        
    }

    public function getNotifications($eventId)
    {
        return $this->getNotifications($eventId);
    }

    public function getNotification($eventId, $notificationId)
    {
        return $this->_getNotification($eventId, $notificationId);
    }

    public function createNotification($eventId, $array)
    {
        return $this->_createNotification($eventId, $array);
    }

    public function updateNotification($eventId, $array)
    {
        return $this->_updateNotification($eventId, $array);
    }

    /* EXTERNAL METHOD */

    public function getById($id)
    {
        return $this->_getById($id);
    }

    public function getList($userId, $offset, $limit)
    {
        return $this->_getList($userId, $offset, $limit);
    }

    public function getUserList($userId)
    {
        return $this->_getUserList($userId);
    }

    public function getUserProjectList($userId)
    {
        return $this->_getUserProjectList($userId);
    }

    public function getRangeList($startDate, $endDate)
    {
        return $this->_getRangeList($startDate, $endDate);
    }

    public function getProjectRangeList($projectId, $startDate, $endDate)
    {
        return $this->_getProjectRangeList($projectId, $startDate, $endDate);
    }

    public function getUserRangeList($userId, $startDate, $endDate)
    {
        return $this->_getUserRangeList($userId, $startDate, $endDate);
    }

    public function getUserProjectRangeList($userId, $startDate, $endDate)
    {
        return $this->_getUserProjectRangeList($userId, $startDate, $endDate);
    }

    public function getUserDateList($userId, $date)
    {
        return $this->_getUserDateList($userId, $date);
    }

    public function getUserProjectDateList($userId, $date)
    {
        return $this->_getUserProjectDateList($userId, $date);
    }

    public function getUserProjectRecentList($userId)
    {
        return $this->_getUserProjectRecentList($userId);
    }

    public function getProjectDateCount($projectId, $date)
    {
        return $this->_getProjectDateCount($projectId, $date);
    }

    public function getUnsentNotification()
    {
        return $this->_getUnsentNotification();
    }

    public function canUserEdit($id, $userId)
    {
        return $this->_canUserEdit($id, $userId);
    }

    public function getUserThisWeekCount($userId)
    {
        return $this->_getUserThisWeekCount($userId);
    }

    public function getUserPlannedCount($userId)
    {
        return $this->_getUserPlannedCount($userId);
    }

}
