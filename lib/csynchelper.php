<?php

namespace Openregion\Ispringintegration;

\Bitrix\Main\Loader::IncludeModule("tasks");

use \Bitrix\Tasks\Item\Task;
use \Bitrix\Main\UserTable;
use Openregion\Ispringintegration\CApiHelper;
use Openregion\Ispringintegration\CStructureHelper;
use Openregion\Ispringintegration\CCourseNotifier;
use Openregion\Ispringintegration\CCalendarHelper;


class CSyncHelper
{
	private const IBLOCKID = 160;

	public static function syncUsers($arDepartment)
	{
		$arUsers = CStructureHelper::getUsers();
		$userList =  CApiHelper::getUsersList();
		foreach ($arUsers as $arUser) {
			foreach ($arDepartment as $key => $item) {
				if ($key == $arUser['departmentId']) {
					$arUser['departmentId'] = $arDepartment[$key]['id'];
				}
				if ($arDepartment[$key]['head'] == $arUser['userId']) {
					$arUser['role'] = 'department_administrator';
					$arUser['manageableDepartmentIds'][] = $arDepartment[$key]['id'];
				}
			}
			$userId = CApiHelper::getUserId($userList, $arUser['email']);
			if (empty($arUser['departmentId'])) {
				$arUser['departmentId'] = 'b3d5f0c2-f5ca-11ec-bd69-6629c28a3a9b';
			}
			if ($userId) {
				unset($arUser['role']);
				CApiHelper::editUser($userId, $arUser);
				$arUser['userId'] =  $userId;
			} else {
				$arUser['userId'] = CApiHelper::addUser($arUser);
			}
			$arResult[] = $arUser;
		}

		return $arResult;
	}


	public static function syncGroups($arUsers)
	{
		$arBxGroup = CStructureHelper::getDepartment(self::IBLOCKID, $arUsers);
		$groupList =  CApiHelper::getGroupsList();

		foreach ($arBxGroup as $group) {

			if ($groupId = CApiHelper::getGroupId($groupList, $group['NAME'])) {
				$arGroupId[] = $groupId;
				$arRespose['SET_USERS'][] = CApiHelper::setGroupMembers($groupId, $group['USERS']);
			} else {
				$arRespose['ADD_GROUP'][] = CApiHelper::addGroup($group['NAME'], $group['USERS']);
			}
		}

		return $arRespose;
	}


	public static function syncDepartment($arDepartment, $arResult = [], $deep = 0)
	{

		$departmentList =  CApiHelper::getDepartmentList();
		$arNew = [];
		foreach ($arDepartment as $item) {
			$departmentId = CApiHelper::getDepartmentId($departmentList, $item['name']);
			$parentId = CApiHelper::getDepartmentId($departmentList, $item['parent']);
			if (empty($item['parent'])) {
				$parentId = 'b3d5f0c2-f5ca-11ec-bd69-6629c28a3a9b';
			}

			if ($deep > 50) {
				print_r($arResult);
				exit('>50');
			}

			if (empty($departmentId)) {
				if ($parentId) {
					$arResult[$item['code']][] = 'add';
					$arResult[$item['code']]['name'] = $item['name'];
					$arResult[$item['code']]['head'] = $item['head'];
					$arResult[$item['code']]['id'] = CApiHelper::addDepartment($item['name'], $parentId, $item['code']);
					$arResult[$item['code']]['parent'] = $parentId;
				} else {
					$arNew[] = $item;
				}
			} else {
				$arResult[$item['code']][] = 'edit';
				$arResult[$item['code']]['name'] = $item['name'];
				$arResult[$item['code']]['id'] = $departmentId;
				$arResult[$item['code']]['head'] = $item['head'];
				$arResult[$item['code']]['response'] = CApiHelper::editDepartment($item['name'], $parentId, $departmentId, $item['code']);
			}

			if (!empty($arNew)) {
				$deep += 1;
				$arResult = self::syncDepartment($arNew, $arResult, $deep);
			}
		}
		return $arResult;
	}


	public static function syncAll()
	{
		$arDepartment = CStructureHelper::getDepartment(self::IBLOCKID);
		$arResult = [];
		$arResult['DEPARTMENT'] = self::syncDepartment($arDepartment);
		$arResult['USERS'] = self::syncUsers($arResult['DEPARTMENT']);
		return '\Openregion\Ispringintegration\CSyncHelper::syncAll();';
	}


	public static function syncEnrollments()
	{
		$userList =  CApiHelper::getUsersList();
		$arOldEnrollments = CStructureHelper::getOldEnrollments();
		$arEnrollments = CApiHelper::getAllEnrollments();
		$arCourses = CApiHelper::getCourses();

		foreach ($arEnrollments['RESPONSE']['ENROLLMENT'] as $enrollment) {
			if ($enrollment['ACCESSDATE'] >= date("Y-m-d")) {
				if (!in_array($enrollment['ENROLLMENTID'], $arOldEnrollments)) {
					$arFields =
						[
							'ENROLLMENTID' =>  $enrollment['ENROLLMENTID'],
							'ACCESSDATE' =>  $enrollment['ACCESSDATE'],
							'DUEDATE' =>  $enrollment['DUEDATE'],
							'COURSEID' => $enrollment['COURSEID'],
						];
					foreach ($userList as $user) {
						if ($user['USERID'] == $enrollment['LEARNERID']) //USERID
						{
							$email = CApiHelper::getUserEmail($userList, $user['USERID']);
							$arUser = UserTable::getList([
								'select' => ['ID', 'UF_DEPARTMENT'],
								'filter' => ['EMAIL' => $email]
							])->fetch();
							$arFields['UID'] = $arUser['ID'];
							$arFilter =
								[
									"IBLOCK_ID" => "160",
									'GLOBAL_ACTIVE' => 'Y',
									"ID" => $arUser['UF_DEPARTMENT']['0'],
								];
							$department = \CIBlockSection::GetList([], $arFilter, false, ['UF_HEAD'])->Fetch();
							$arFields['HEAD'] = $department['UF_HEAD'];
						}
					}
					if ($arCourse =  $arCourses[$enrollment['COURSEID']]) {
						$arFields['COURSENAME'] = $arCourse['TITLE'];
						$arFields['LINK'] = $arCourse['VIEWURL'];
					} elseif ($arTraining = CApiHelper::getTrainig($enrollment['COURSEID'])) {
						$arFields['COURSENAME'] = $arTraining['TITLE'];
						foreach ($userList as $user) {
							if ($user['USERID'] == $arTraining['ORGANIZER']) //USERID
							{
								$email = CApiHelper::getUserEmail($userList, $user['USERID']);
								$user = UserTable::getList([
									'select' => ['LAST_NAME', 'NAME', 'SECOND_NAME'],
									'filter' => ['EMAIL' => $email]
								])->fetch();
								$arFields['ORGANIZER'] = implode(' ', $user);
							}
						}
					} else {
						$arFields['COURSENAME'] = 'Траектория: ' . $enrollment['COURSEID'];
					} //continue;}

					$arResult['add'][] = CStructureHelper::addEnrollment($arFields['COURSENAME'], $arFields);
					$arFields['MESSAGE'] = CCourseNotifier::notifyUser($arFields);
					if (!empty($arFields['ORGANIZER'])) {
						$arResult['calendar'][] = CCalendarHelper::addTrainingEvent($arFields);
					} else {
						$arTaskFields =
							[
								"TITLE" => $arFields['COURSENAME'],
								"DESCRIPTION" => $arFields['MESSAGE'],
								"RESPONSIBLE_ID" => $arFields['UID'],
								"START_DATE_PLAN" => $arFields['ACCESSDATE'],
								"END_DATE_PLAN" => (empty($arFields['DUEDATE'])) ? $arFields['ACCESSDATE'] : $arFields['DUEDATE'],
								"AUDITORS" => [$arFields['HEAD']],
								"GROUP_ID" => 325,
							];
						$task = new Task($arTaskFields, 1);
						$arResult['tasks'][] = $task->save();
					}
				}
			}
		}
		return '\Openregion\Ispringintegration\CSyncHelper::syncEnrollments();';
	}
}
