<?

namespace Openregion\Ispringintegration;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Engine\CurrentUser;

class CSettingsHelper
{
    private const mid = 'openregion.ispringintegration';

    public static function getSecretKey()
    {
        $module_id = self::mid;
        return Option::get($module_id, 'SECRET_KEY', null);
    }
    public static function getAdminPassword()
    {
        $module_id = self::mid;
        return Option::get($module_id, 'PASSWORD', null);
    }

    public static function getAdminEmail()
    {
        $module_id = self::mid;
        return Option::get($module_id, 'EMAIL', null);
    }

    public static function getCurrentUserEmail()
    {
        return CurrentUser::get()->getEmail();
    }
    public static function getCurrentUserId()
    {
        return CurrentUser::get()->getId();
    }
}
