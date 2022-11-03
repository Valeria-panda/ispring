<?php

namespace Openregion\Ispringintegration;

\Bitrix\Main\Loader::includeModule('im');

class CCourseNotifier
{
    private static function sendNotification($userId, $message)
    {
        if (!\Bitrix\Main\Loader::includeModule('im')) {
            return;
        }
        $arMessageFields = [
            // получатель
            "TO_USER_ID" => $userId,
            // отправитель (может быть >0)
            "FROM_USER_ID" => 0,
            // тип уведомления
            "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
            // модуль запросивший отправку уведомления
            "NOTIFY_MODULE" => "openregion.ispringintegration",
            // символьный тэг для группировки (будет выведено только одно сообщение), если это не требуется - не задаем параметр
            "NOTIFY_TAG" => "ISPRING_NEW_ENROLLMENT",
            // текст уведомления на сайте (доступен html и бб-коды)
            "NOTIFY_MESSAGE" => $message, //'[b]:[/b] необходимо проверить и указать корректный путь до социальной сети в настройках модуля “Мгновенные сообщения и уведомления”',
            // текст уведомления для отправки на почту (или XMPP), если различий нет - не задаем параметр
            "NOTIFY_MESSAGE_OUT" => $message,
        ];
        $arResult = \CIMNotify::Add($arMessageFields);

        return $arResult;
    }


    private static function composeMessage(array $fields)
    {

        $startDay = $fields['ACCESSDATE'];
        $finishDay = $fields['DUEDATE'];
        if ($organizer = $fields['ORGANIZER']) {

            $head = 'Вам назначено мероприятие: ' . $fields['COURSENAME'];
            $message = "[b] $head [/b] <br> 
            Дата начала: $startDay
            Дата окончания: $finishDay
            
            Организатор мероприятия $organizer";
        } else {
            $link = $fields['LINK'];
            $head = 'Вам назначен курс: ' . $fields['COURSENAME'];
            $message = "[b] $head [/b] <br> 
                Дата начала обучения: $startDay
                Дата окончания обучения: $finishDay
                <a href=$link > Ссылка на курс </a> ";
        }
        return $message;
    }

    public static function notifyUser(array $fields)
    {
        $arResult['message'] =  self::composeMessage($fields);
        $arResult['id'] = self::sendNotification($fields['UID'], $arResult['message']);
        return $arResult['message'];
    }
}
