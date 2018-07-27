<?php

namespace Absolute\Module\Event\Manager;

use Absolute\Core\Manager\BaseCRUDManager;
use Nette\Database\Context;
use Absolute\Core\Helper\DateHelper;

class EventCRUDManager extends BaseCRUDManager
{

    public function __construct(Context $database)
    {
        parent::__construct($database);
    }

    // OTHER METHODS

    public function createNotifications($id, $date, $values, $types, $periods)
    {
        if (count($values) !== count($types) || count($values) !== count($periods))
        {
            return false;
        }
        $date = DateHelper::validateDate($date);
        if (!$date)
        {
            return false;
        }
        $data = [];
        $now = new \DateTime;
        foreach ($values as $key => $value)
        {
            if (!array_key_exists($key, $types) || !array_key_exists($key, $periods))
            {
                continue;
            }
            try
            {
                $notificationDate = clone $date;
                if ($periods[$key] == "a")
                {
                    $notificationDate->add(new \DateInterval('P' . ((strtoupper($types[$key]) != "D") ? "T" : "") . $value . strtoupper($types[$key])));
                }
                else
                {
                    $notificationDate->sub(new \DateInterval('P' . ((strtoupper($types[$key]) != "D") ? "T" : "") . $value . strtoupper($types[$key])));
                }
            }
            catch (\Exception $e)
            {
                continue;
            }
            $data[] = array(
                "notification_date" => $notificationDate,
                "event_id" => $id,
                "value" => $value,
                "period" => $periods[$key],
                "type" => $types[$key],
                "sent" => ($notificationDate < $now) ? 1 : 0,
                "created" => new \DateTime,
            );
        }
        $this->database->table('event_notification')->where('event_id', $id)->delete();
        if (!empty($data))
        {
            $this->database->table('event_notification')->insert(
                    $data
            );
        }
        return true;
    }

    public function updateNotifications($id, $date)
    {
        $date = DateHelper::validateDate($date);
        if (!$date)
        {
            return false;
        }
        $data = [];
        $now = new \DateTime;
        $result = $this->database->table('event_notification')->where('event_id', $id);
        foreach ($result as $db)
        {
            try
            {
                $notificationDate = clone $date;
                if ($db->period == "a")
                {
                    $notificationDate->add(new \DateInterval('P' . ((strtoupper($db->type) != "D") ? "T" : "") . $db->value . strtoupper($db->type)));
                }
                else
                {
                    $notificationDate->sub(new \DateInterval('P' . ((strtoupper($db->type) != "D") ? "T" : "") . $db->value . strtoupper($db->type)));
                }
            }
            catch (\Exception $e)
            {
                continue;
            }
            $data[] = array(
                "notification_date" => $notificationDate,
                "event_id" => $id,
                "value" => $db->value,
                "period" => $db->period,
                "type" => $db->type,
                "sent" => ($notificationDate < $now) ? 1 : 0,
                "created" => new \DateTime,
            );
        }
        $this->database->table('event_notification')->where('event_id', $id)->delete();
        if (!empty($data))
        {
            $this->database->table('event_notification')->insert(
                    $data
            );
        }
        return true;
    }

    public function updateNotificationsSent()
    {
        return $this->database->table('event_notification')->where('sent', false)->where('notification_date <= NOW()')->update(array(
                    'sent' => true,
        ));
    }

    // CONNECT METHODS

    public function connectUsers($id, $users)
    {
        $users = array_unique(array_filter($users));
        // DELETE
        $this->database->table('event_user')->where('event_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($users as $userId)
        {
            $data[] = array(
                "event_id" => $id,
                "user_id" => $userId,
            );
        }

        if (!empty($data))
        {
            $this->database->table('event_user')->insert($data);
        }
        return true;
    }

    public function connectTeams($id, $teams)
    {
        $teams = array_unique(array_filter($teams));
        // DELETE
        $this->database->table('event_team')->where('event_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($teams as $team)
        {
            $data[] = [
                "team_id" => $team,
                "event_id" => $id,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("event_team")->insert($data);
        }
        return true;
    }

    public function connectCategories($id, $categories)
    {
        $categories = array_unique(array_filter($categories));
        // DELETE
        $this->database->table('event_category')->where('event_id', $id)->delete();
        // INSERT NEW
        $data = [];
        foreach ($categories as $category)
        {
            $data[] = [
                "category_id" => $category,
                "event_id" => $id,
            ];
        }
        if (!empty($data))
        {
            $this->database->table("event_category")->insert($data);
        }
        return true;
    }

    public function connectProject($id, $projectId)
    {
        $this->database->table('project_event')->where('event_id', $id)->delete();
        return $this->database->table('project_event')->insert(array(
                    "event_id" => $id,
                    "project_id" => $projectId
        ));
    }

    // CUD METHODS

    public function create($userId, $title, $allDay, $startDate, $endDate, $repeat, $location, $gpsLat, $gpsLng, $note)
    {
        $result = $this->database->table('event')->insert(array(
            'user_id' => $userId,
            'title' => $title,
            'note' => $note,
            'all_day' => $allDay,
            'repeat' => $repeat,
            'location' => $location,
            'gps_lat' => $gpsLat,
            'gps_lng' => $gpsLng,
            'start_date' => DateHelper::validateDate($startDate),
            'end_date' => DateHelper::validateDate($endDate),
            'created' => new \DateTime(),
        ));
        return $result;
    }

    public function delete($id)
    {
        $this->database->table('project_event')->where('event_id', $id)->delete();
        $this->database->table('event_category')->where('event_id', $id)->delete();
        $this->database->table('event_notification')->where('event_id', $id)->delete();
        $this->database->table('event_team')->where('event_id', $id)->delete();
        $this->database->table('event_user')->where('event_id', $id)->delete();
        return $this->database->table('event')->where('id', $id)->delete();
    }

    public function update($id, $title, $allDay, $startDate, $endDate, $repeat, $location, $gpsLat, $gpsLng, $note)
    {
        return $this->database->table('event')->where('id', $id)->update(array(
                    'title' => $title,
                    'note' => $note,
                    'all_day' => $allDay,
                    'repeat' => $repeat,
                    'location' => $location,
                    'gps_lat' => $gpsLat,
                    'gps_lng' => $gpsLng,
                    'start_date' => DateHelper::validateDate($startDate),
                    'end_date' => DateHelper::validateDate($endDate),
        ));
    }

    public function updateWithArray($id, $array)
    {
        unset($array['id']);
        unset($array['create']);
        unset($array['user_id']);
        if (isset($array['start_date']))
            $array['start_date'] = DateHelper::validateDate($array['start_date']);
        if (isset($array['end_date']))
            $array['end_date'] = DateHelper::validateDate($array['end_date']);
        
        return $this->database->table('event')->where('id', $id)->update($array);
    }

    public function updateDate($id, $startDate, $endDate)
    {
        return $this->database->table('event')->where('id', $id)->update(array(
                    'start_date' => DateHelper::validateDate($startDate),
                    'end_date' => DateHelper::validateDate($endDate),
        ));
    }

}
