<?php

namespace Absolute\Module\Event\Classes;

class Notification {

  private $id;
  private $value;
  private $notificationDate;
  private $type;
  private $period;
  private $sent;
  private $created;

	public function __construct($id, $value, $notificationDate, $type, $period, $sent, $created) {
    $this->id = $id;
		$this->value = $value;
    $this->type = $type;
    $this->notificationDate = $notificationDate;
    $this->period = $period;
    $this->sent = ($sent) ? true : false;
    $this->created = $created;
	}

  public function getId() {
    return $this->id;
  }

  public function getValue() {
    return $this->value;
  }

  public function getType() {
    return $this->type;
  }

  public function getNotificationDate() {
    return $this->notificationDate;
  }

  public function getPeriod() {
    return $this->period;
  }

  public function getSent() {
    return $this->sent;
  }

  public function getCreated() {
    return $this->created;
  }

  // SETTERS

  // ADDERS

  // OTHER METHODS  

  public function toCalendarJson() {
    return array(
      "type" => $this->type,
      "value" => $this->value,
      "id" => $this->id,
      "period" => $this->period,
    );
  }

}

