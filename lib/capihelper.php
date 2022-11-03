<?php

namespace Openregion\Ispringintegration;

use Bitrix\Main\Config\Option;
use Openregion\Ispringintegration\Rest;

class CApiHelper
{
    private const MODULE_ID = 'openregion.ispringintegration';
    private const BASE_URL = 'https://marksgroup.ispringlearn.ru';
    private const HOST = 'https://marksgroup.ispringlearn.ru/api/v2';

    private static function getHeaders()
    {
        $baseUrl = self::BASE_URL;
        $password = Option::get(self::MODULE_ID, "PASSWORD", null);
        $adminEmail = Option::get(self::MODULE_ID, 'EMAIL', null);
        $headers = [
            "X-Auth-Account-Url: $baseUrl",
            "X-Auth-Email: $adminEmail",
            "X-Auth-Password: $password",
            "Content-Type: application/xml",
        ];
        return $headers;
    }


    public static function getEnrollmentForCurrentStudent($learnerIds)
    {
        $endpoint = '/enrollment?learnerIds[]=' . $learnerIds;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult['RESPONSE']['ENROLLMENT'];
    }


    public static function getTraectory($trackId)
    {
        $endpoint = '/api/v2/learning_track/courses?learningTrackIds=' . $trackId;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult;
    }

    public static function getInfoAboutCourse($courseId)
    {
        $endpoint = '/content/' . $courseId;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult['RESPONSE']['CONTENTITEM'];
    }

    public static function getFinalResultOfCourse($courseId)
    {
        $endpoint = '/content/' . $courseId . '/final_statuses';
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult['RESPONSE']['STATUS'];
    }

    public static function getAllEnrollments()
    {
        $endpoint = '/enrollment';
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult;
    }

    public static function getAchivements()
    {
        $endpoint = '/gamification/points';
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult;
    }

    // course methods
    public static function getCourses()
    {
        $endpoint = '/content';
        $arCourses = Rest::get(self::HOST, $endpoint, self::getHeaders());
        foreach ($arCourses['RESPONSE']['CONTENTITEM'] as $item) {
            $arResult[$item['CONTENTITEMID']] = $item;
        }
        return $arResult;
    }


    private static function buildTree($items, $parentId = null, &$arIds = [])
    {
        $treeItems = [];
        foreach ($items as $key => $item) {
            if (!in_array($items[$key]['CONTENTITEMID'], $arIds)) {
                if ((empty($parentId) && empty($item['PARENTID'])) || ($item['PARENTID'] == $parentId)) {
                    $arIds[] = $items[$key]['CONTENTITEMID'];
                    $items[$key]['CHILDS'] = self::buildTree($items, $items[$key]['CONTENTITEMID'], $arIds);
                    $treeItems[] = $items[$key];
                }
            }
        }

        return $treeItems;
    }


    // получение подробной структуры с папками и ParentID
    public static function getCoursesTree()
    {
        $endpoint = '/courses_tree';
        $arResponse = Rest::get(self::HOST, $endpoint, self::getHeaders());
        $arResult = self::buildTree($arResponse['RESPONSE']['CONTENTITEM']);

        return $arResult;
    }


    public static function getCourseDetail($content_item_id)
    {
        $endpoint = '/content/' . $content_item_id;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());

        return $arResult;
    }


    // group methods
    public static function addGroup($name, $userIds = [])
    {
        $data = [
            'name' => $name,
            'userIds' => $userIds,
        ];
        $endpoint = '/group';
        $idGroup = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $idGroup['RESPONSE'];
    }


    public static function getGroupId($groupList, $name)
    {
        foreach ($groupList as $value) {
            if ($value['NAME'] == $name) {
                $result = $value['GROUPID'];
            }
        }
        return $result;
    }


    public static function editGroup($nameGroup, $groupId)
    {

        $data = ['name' => $nameGroup];
        $endpoint = '/group/' . $groupId;
        $result = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $result;
    }


    public static function getGroupsList()
    {
        $endpoint = '/group';
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult['RESPONSE']['GROUP'];
    }


    public static function deleteGroup($groupId)
    {
        $endpoint = '/group/' . $groupId;
        $result = Rest::delete(self::HOST, $endpoint, self::getHeaders());
        return $result;
    }


    public static function getGroup($groupId)
    {
        $endpoint = '/group/' . $groupId;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult['RESPONSE']['GROUP'];
    }


    public static function setGroupMembers($groupId, $userIds = [])
    {
        $data = [
            'userIds' => $userIds,
        ];
        $endpoint = '/group/' . $groupId . '/members';
        $result = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $result;
    }


    // user methods 
    private static function changeViewUserData($userData)
    {
        $fields = [
            'login' => $userData['login'],
            'phone' => $userData['phone'],
            'email' => $userData['email'],
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'job_title' => $userData['job_title'],
        ];

        $arResult = array_diff($userData,  $fields);
        $arResult['fields'] = $fields;

        return $arResult;
    }


    public static function addUser($userData)
    {
        $data = self::changeViewUserData($userData);
        $endpoint = '/user';
        $userId = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $userId['RESPONSE'];
    }


    public static function editUser($userId, $userData)
    {
        $data = self::changeViewUserData($userData);
        $endpoint = '/user/' . $userId;

        $result = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $result;
    }


    public static function setUserPassword($userId, $password)
    {
        $data = ['password' => $password];
        $endpoint = '/user/' . $userId . '/password';

        $result = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $result;
    }


    public static function getUser($userId)
    {
        $endpoint = '/user/' . $userId;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());
        return $arResult['RESPONSE']['USERPROFILE'];
    }


    public static function getUsersList($departments = [], $groups = [])
    {
        if (!empty($departments)) {
            $params = 'departments[]=' . $departments;
        }
        if (!empty($groups)) {
            $params = $params . '&groups[]=' . $groups;
        }

        $endpoint = (empty($params)) ? '/user' : ('/user?' . $params);

        $userList = Rest::get(self::HOST, $endpoint, self::getHeaders());
        $arUsers = $userList['RESPONSE']['USERPROFILE'];
        foreach ($arUsers as $userKey => $user) {
            $arResult[$userKey] = $user;
            foreach ($user as $key => $value) {
                if ($key == 'FIELDS') {
                    $arResult[$userKey]['FIELDS'] = $value[$userKey]['FIELD'];
                }
            }
        }

        return $arResult;
    }


    public static function deleteUser($userId)
    {
        $endpoint = '/user/' . $userId;
        $result = Rest::delete(self::HOST, $endpoint, self::getHeaders());
        return $result;
    }


    public static function removeUserGroups($userId, $groupIds)
    {
        $data = ['groupIds' => $groupIds];
        $endpoint = '/user/' . $userId . '/groups/remove';

        $result = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $result;
    }


    public static function getUserId($userList, $field)
    {
        foreach ($userList as $arUser) {
            foreach ($arUser['FIELDS'] as $value) {
                if ($value['VALUE'] == $field) {
                    $result = $arUser['USERID'];
                }
            }
        }
        return $result;
    }

    public static function getUserEmail($userList, $userId)
    {
        foreach ($userList as $arUser) {
            if ($arUser['USERID'] == $userId)
                foreach ($arUser['FIELDS'] as $item) {
                    if ($item['NAME'] == 'EMAIL') {
                        return $item['VALUE'];
                    }
                }
        }
    }


    // department methods
    public static function addDepartment($name, $parentId, $code)
    {
        $data =
            [
                'name' => $name,
                'parentDepartmentId' => $parentId,
                'code' => $code,
            ];
        $endpoint = '/department';

        $departmentId = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $departmentId['RESPONSE'];
    }


    public static function editDepartment($name, $parentId, $departmentId, $code)
    {
        $data =
            [
                'name' => $name,
                'parentDepartmentId' => $parentId,
                'code' => $code,
            ];
        $endpoint = '/department/' . $departmentId;

        $result = Rest::post(self::HOST, $endpoint, self::getHeaders(), $data);

        return $result;
    }


    public static function getDepartment($departmentId)
    {
        $endpoint = '/department/' . $departmentId;

        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());

        return $arResult['RESPONSE']['DEPARTMENT'];
    }


    public static function getDepartmentList()
    {
        $endpoint = '/department';

        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());

        return $arResult['RESPONSE']['DEPARTMENT'];
    }


    public static function deleteDepartment($departmentId)
    {
        $endpoint = '/department/' . $departmentId;
        $result = Rest::delete(self::HOST, $endpoint, self::getHeaders());
        return $result;
    }


    public static function getDepartmentId($departmentList, $field)
    {
        foreach ($departmentList as $item) {
            foreach ($item as $value) {
                if ($value == $field) {
                    $result = $item['DEPARTMENTID'];
                }
            }
        }
        return $result;
    }


    public static function getTrainig($trainingId)
    {
        $endpoint = '/training/' . $trainingId;
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());

        return $arResult['RESPONSE']['TRAINING'];
    }


    public static function getTrainigSessions($trainingId)
    {
        $endpoint = '/training/' . $trainingId . '/sessions';
        $arResult = Rest::get(self::HOST, $endpoint, self::getHeaders());

        return $arResult;
    }
}
