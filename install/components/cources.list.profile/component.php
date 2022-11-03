<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
$module_id = "openregion.ispringintegration";
if (!CModule::IncludeModule($module_id)) {
    echo "Модуль курсов не установлен";
    return;
}

use \Openregion\Ispringintegration\CSettingsHelper;
use \Openregion\Ispringintegration\CApiHelper;

$currentUserEmail = CSettingsHelper::getCurrentUserEmail();
$userList = CApiHelper::getUsersList();
$currentLearnerIds = CApiHelper::getUserId($userList, $currentUserEmail);
$enrollmentsForCurrentUser = CApiHelper::getEnrollmentForCurrentStudent($currentLearnerIds);

$courseList = CApiHelper::getCourses();

$courseInfo = [];
foreach ($enrollmentsForCurrentUser as $enrollment) {
    $courseInfo[] = [
        'CURRENT_USER_ID' => $enrollment['LEARNERID'],
        'COURSE_ID' => $enrollment['COURSEID'],
        'ISSUED_CERTIFICATE' => $enrollment['ISSUEDCERTIFICATEID'],
        'INFO' => $courseList[$enrollment['COURSEID']],
        'STATUS' => CApiHelper::getFinalResultOfCourse($enrollment['COURSEID']),
    ];
};


$arResult['COURSE_INFO'] = $courseInfo;
?>

<?
$this->IncludeComponentTemplate();
