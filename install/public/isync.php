<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
\Bitrix\Main\Loader::IncludeModule('openregion.ispringintegration');

global $USER;

use Openregion\Ispringintegration\CStructureHelper;
use Openregion\Ispringintegration\CApiHelper;
use \Bitrix\Main\UserTable;

$userList =  CApiHelper::getUsersList();
$arUsers = CStructureHelper::getUsers();
$arOldEnrollments = CStructureHelper::getOldEnrollments();
$arEnrollments = CApiHelper::getAllEnrollments();
$arCurses = CApiHelper::getCourses();

foreach ($arEnrollments['RESPONSE']['ENROLLMENT'] as $enrollment) {
	if ($enrollment['ACCESSDATE'] >= date("Y-m-d")) {

		$arFields =
			[
				'ENROLLMENTID' =>  $enrollment['ENROLLMENTID'],
				'ACCESSDATE' =>  $enrollment['ACCESSDATE'],
				'DUEDATE' =>  $enrollment['DUEDATE'],
				'COURSEID' => $enrollment['COURSEID'],
			];
		if ($arTraining = CApiHelper::getTrainig($enrollment['COURSEID'])) {
			$arFields['COURSENAME'] = $arTraining['TITLE'];
			$arFields['ORGANIZER'] = $arTraining['ORGANIZER'];
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

			echo 'ID: ' . $enrollment['ENROLLMENTID'];
			echo '<pre>';
			print_r($arTraining);
			echo '</pre>';

			$arSessions = CApiHelper::getTrainigSessions($enrollment['COURSEID']);
			if ($arSessions['TITLE']) {
				$arFields['ACCESSDATE'] = $arSessions['STARTTIME'];
				$arFields['DUEDATE'] = strtotime($arSessions['STARTTIME'] . '+' . $arSessions['DURATION'] . 'seconds');
			}
			echo '<pre>';
			print_r($arFields);
			echo '</pre>';
		}
	}
}

?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>



