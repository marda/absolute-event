<?php

namespace Absolute\Module\Event\Classes;

class Event 
{

  private $id;
  private $title;
  private $note;
  private $allDay;
  private $startDate;
  private $endDate;
  private $repeat;
  private $location;
  private $created;
  private $gpsLat;
  private $gpsLng;
  private $userId;

  private $users = [];
  private $teams = [];
  private $categories = [];
  private $notifications = [];

  private $editable = true;

	public function __construct($id, $userId, $title, $allDay, $startDate, $endDate, $repeat, $location, $gpsLat, $gpsLng, $note, $created) 
  {
    $this->id = $id;
    $this->userId = $userId;
		$this->title = $title;
    $this->note = $note;
    $this->startDate = $startDate;
    $this->endDate = $endDate;
    $this->created = $created;
    $this->gpsLat = $gpsLat;
    $this->gpsLng = $gpsLng;
    $this->allDay = ($allDay) ? true : false;
    $this->repeat = $repeat;
    $this->location = $location;
	}

  public function getId() 
  {
    return $this->id;
  }

  public function getUserId()
  {
    return $this->userId;
  }

  public function getTitle() 
  {
    return $this->title;
  }

  public function getNote() 
  {
    return $this->note;
  }

  public function getAllDay() 
  {
    return $this->allDay;
  }

  public function getStartDate() 
  {
    return $this->startDate;
  }

  public function getEndDate() 
  {
    return $this->endDate;
  }

  public function getCreated() 
  {
    return $this->created;
  }

  public function getRepeat() 
  {
    return $this->repeat;
  }

  public function getLocation() 
  {
    return $this->location;
  }

  public function getGpsLat()
  {
    return $this->gpsLat;
  }

  public function getGpsLng()
  {
    return $this->gpsLng;
  }

  public function getNotifications() 
  {
    return $this->notifications;
  }

  public function getToStart() 
  {
    if (!$this->startDate) 
    {
      return false;
    }
    try 
    {
      $now = new \DateTime;
      $interval = $this->startDate->diff($now);
      return [
        "started" => ($interval->format('%R') == "-") ? false : true,
        "hours" => $interval->format('%G'),
        "minutes" => $interval->format('%i')
      ];      
    } 
    catch (\Exception $e) 
    {
      return false;
    }
  }

  public function getUsers() 
  {
    return $this->users;
  }
  
  public function getTeams()
  {
    return $this->teams;
  }

  public function getCategories()
  {
    return $this->categories;
  }

  // IS?

  public function isEnd() 
  {
    $now = new \DateTime;
    if ($this->endDate && $this->endDate < $now) 
    {
      return true;
    }
    return false;
  }

  public function isEditable() 
  {
    return ($this->editable) ? true : false;
  }

  // SETTERS

  public function setEditable($editable) 
  {
    $this->editable = $editable;
  }

  // ADDERS

  public function addUser($user) 
  {
    $this->users[$user->getId()] = $user;
  }

  public function addTeam($team) 
  {
    $this->teams[$team->getId()] = $team;
  }

  public function addCategory($category) 
  {
    $this->categories[$category->getId()] = $category;
  }

  public function addNotification($notification) 
  {
    $this->notifications[] = $notification;
  }

  // OTHER METHODS  

  public function toCalendarJson() 
  {
    return array(
      "id" => $this->id,
      "editable" => $this->editable,
      "allDay" => $this->getAllDay(),
      "start" => ($this->startDate) ? $this->startDate->format("Y-m-d H:i") : null,
      "end" => ($this->endDate) ? $this->endDate->format("Y-m-d H:i") : null,
      "title" => $this->title,
      "location" => $this->location,
      "repeat" => $this->repeat,
      "note" => $this->note,
      "gpsLat" => $this->gpsLat,
      "gpsLng" => $this->gpsLng,
      "users" => array_values(array_map(function($user) { return $user->toCalendarJson(); }, $this->users)),
      "teams" => array_values(array_map(function($team) { return $team->toJson(); }, $this->teams)),
      "categories" => array_values(array_map(function($category) { return $category->toJson(); }, $this->categories)),
      "notifications" => array_map(function($notification) { return $notification->toCalendarJson(); }, $this->notifications),
    );
  }

  // for array unique
  public function __toString()
  {
    return (string)$this->id;
  }
}

