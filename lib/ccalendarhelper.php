<?php

namespace Openregion\Ispringintegration;

\Bitrix\Main\Loader::includeModule('calendar');

use \Bitrix\Calendar\CCalendar;

class CCalendarHelper
{
    private static function addEvent($fields)
    {
        $fromTS = MakeTimeStamp($fields['from'], "YYYYS-MM-DD");
        $toTS = MakeTimeStamp($fields['to'], "YYYYS-MM-DD");
        $arFields =
            [
                "CAL_TYPE" => 'user',
                "OWNER_ID" => $fields['ownerId'],
                "NAME" => $fields['eventName'],
                "DESCRIPTION" => $fields['description'],
                "IS_MEETING" => false,
                "RRULE" => false,
                "ACCESSIBILITY" => 'free',
            ];
        $arFields['DATE_FROM'] = CCalendar::Date($fromTS);
        $arFields['DATE_TO'] = CCalendar::Date($toTS);
        $eventId = CCalendar::SaveEvent(
            [
                'arFields' => $arFields,
                'autoDetectSection' => true,
                'autoCreateSection' => true
            ]
        );

        return $eventId;
    }

    public static function addTrainingEvent($arFields)
    {
        $arFieldsCalend =
            [
                'from' => $arFields['ACCESSDATE'],
                'to' => (empty($arFields['DUEDATE'])) ? $arFields['ACCESSDATE'] : $arFields['DUEDATE'],
                'eventName' =>  $arFields['COURSENAME'],
                'description' => $arFields['MESSAGE'],
                'ownerId' => $arFields['UID'],
            ];
        $arResult = self::addEvent($arFieldsCalend);
        return $arResult;
    }
}
