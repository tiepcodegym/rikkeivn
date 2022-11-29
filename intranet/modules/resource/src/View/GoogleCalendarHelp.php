<?php

namespace Rikkei\Resource\View;

use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\Employee;

Class GoogleCalendarHelp
{
    const ORGANIZATION = 'rikkeisoft.com';
    const TOKEN_NAME = 'google_access_token';
    const TIME_ZONE = 'Asia/Saigon';
    const LAST_ACTIVITY = 'last_activity';
    const TIME_EXPIRE = 600;
    const EVENT_CANCELLED = 'cancelled';

    /**
     * Init client for google calendar API
     *
     * @return Google_Client
     */
    public static function initClient()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path() . '/google_client/client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(Google_Service_Calendar::CALENDAR);
        return $client;
    }

    /**
     * Insert/Update a calendar event
     *
     * @param Google_Client $client
     * @param array $dataInsert
     * @param string $calendarId
     * @param string|null $eventId
     * @return Google_Service_Calendar_Event
     */
    public static function saveEvent($client, $dataInsert, $calendarId, $eventId = null)
    {
        //Guests of event
        $interviewers = Employee::getEmpByIds($dataInsert['attendeesId']);
        $attendees = [];
        foreach ($interviewers as $item) {
            $attendees[] = [
                'email' => $item->email,
            ];
        }
        //Add room as guest
        $attendees[] = [
            'email' => $dataInsert['roomId'],
        ];

        $service = new Google_Service_Calendar($client);
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $dataInsert['title'],
            'description' => $dataInsert['description'],
            'location' => $dataInsert['location'],
            'start' => array(
                'dateTime' => static::formatDate($dataInsert['startDate']),
                'timeZone' => self::TIME_ZONE,
            ),
            'end' => array(
                'dateTime' => static::formatDate($dataInsert['endDate']),
                'timeZone' => self::TIME_ZONE,
            ),
            'attendees' => $attendees,
            'reminders' => array(
                'useDefault' => true,
            ),
            'visibility' => 'public',
        ));

        $params = [
            'sendNotifications' => true,
        ];

        if ($eventId) {
            return $service->events->update($calendarId, $eventId, $event, $params);
        } else {
            return $service->events->insert($calendarId, $event, $params);
        }
    }

    /**
     * get list ids of calendar room unavailable
     *
     * @param array $calendarList
     * @param string|date $minDate
     * @param string|date $maxDate
     * @return array
     */
    public static function getRoomUnavailable($service, $calendarList, $minDate = null, $maxDate = null)
    {
        $roomAvailableIds = [];
        if ($calendarList) {
            $optParams = [];
            if ($maxDate) {
                $optParams['timeMax'] = static::formatDate($maxDate);
            }
            if ($minDate) {
                $optParams['timeMin'] = static::formatDate($minDate);
            }
            foreach ($calendarList as $calendar) {
                $events = $service->events->listEvents($calendar->getId(), $optParams);
                if (count($events)) {
                    $roomAvailableIds[] = $calendar->getId();
                }
            }
        }

        return $roomAvailableIds;
    }

    /**
     * Group calendar by location
     *
     * @param calendarList $calendarList
     * @return array
     */
    public static function groupCalendar($calendarList)
    {
        $grouped = [];
        foreach ($calendarList as $calendar) {
            if (static::startsWith($calendar->getId(), self::ORGANIZATION)) {
                $group = static::getStringBeforeChart($calendar->getSummary());
                $grouped[$group][] = [
                    'id' => $calendar->getId(),
                    'summary' => $calendar->getSummary(),
                ];
            }
        }
        return $grouped;
    }

    public static function getStringBeforeChart($string, $needle = '-')
    {
        return explode($needle, $string, 2)[0];
    }

    public static function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * get start date, end date default, using when create a new calendar event
     *
     * @return array
     */
    public static function getStartEndDateDefault()
    {
        $curDate = Carbon::now();
        $curHour = $curDate->copy()->hour;
        $curMinute = $curDate->copy()->minute;
        $startMinute = (int)$curMinute <= 30 ? 30 : 0;
        $startHour = (int)$curMinute <= 30 ? $curHour : (int)$curHour + 1;
        $startDate = $curDate->copy()->hour($startHour)->minute($startMinute)->second(0);
        $endDate = $startDate->copy()->addHour();

        return [
            'startDate' => $startDate->format('Y-m-d H:i'),
            'endDate' => $endDate->format('Y-m-d H:i'),
        ];
    }

    /**
     * get start date, end date default, using when create a new calendar event
     *
     * @return array
     */
    public static function getStartEndDateDefaultNew($id)
    {
        $candidate = Candidate::getCandidateById($id);
        $interview1 = $candidate->interview_plan;
        $interview2 = $candidate->interview2_plan;
        $now = Carbon::now()->format('Y-m-d H:i:s');
        if($now <= $interview1) {
            $startDate = $interview1;
        } elseif ($now <= $interview2) {
            $startDate = $interview2;
        } else {
           return static::getStartEndDateDefault();
        }
        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $startDate);
        return  [
            'startDate' => $startDate->format('Y-m-d H:i'),
            'endDate' => $startDate->copy()->addHour()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Format date Y-m-d\TH:i:sT
     * example 2018-03-20T13:44:09+07
     *
     * @param date|string $date
     * @param boolean $revert
     * @return date
     */
    public static function formatDate($date, $revert = false)
    {
        $format = $revert ? 'Y-m-d H:i:s' : 'Y-m-d\TH:i:s';
        return date($format, strtotime($date)) . '+07';
    }

    /**
     * check google access token has expire
     *
     * @return boolean
     */
    public static function isTokenHasExpire($request)
    {
        return !$request->session()->has(GoogleCalendarHelp::LAST_ACTIVITY)
                || (time() - $request->session()->get(GoogleCalendarHelp::LAST_ACTIVITY) > self::TIME_EXPIRE);
    }

    /**
     * Flush session google access token
     *
     * @return void
     */
    public static function flushSession($request)
    {
        $request->session()->forget(GoogleCalendarHelp::TOKEN_NAME);
        $request->session()->forget(GoogleCalendarHelp::LAST_ACTIVITY);
    }

    /**
     * Get room of event from attendees
     *
     * @param Google_Service_Calendar_Event $event
     * @return string|null
     */
    public static function getRoomOfEvent($event)
    {
        $attendees = $event->getAttendees();
        if (count($attendees)) {
            foreach ($attendees as $attendee) {
                if ($attendee->resource) {
                    return $attendee->email;
                }
            }
        }
        return null;
    }
}
