<?

namespace Openregion\Ispringintegration;

use Openregion\Ispringintegration\CSettingsHelper;

class CJwtTokenHelper
{
    private const PROTOCOL_STRING = 'https://';
    private const ISPRING_LEARN_DOMAIN = 'marksgroup.ispringlearn.ru';
    private const ISPRING_JWT_LOGIN_URL = '/sso/login/jwt?jwt=';
    private const TOKEN_EXPIRE_DELAY_SEC = 60;

    private static function createTokenId()
    {
        if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION < 7) {
            return base64_encode(mcrypt_create_iv(32));
        }
        return base64_encode(random_bytes(32));
    }

    private static function create($email)
    {
        $issuedAt = time();
        $tokenId = self::createTokenId();
        $expire = $issuedAt + self::TOKEN_EXPIRE_DELAY_SEC;
        $payload = [
            'iat' => $issuedAt,
            'jti' => $tokenId,
            'exp' => $expire,
            'email' => $email,
        ];
        $key = CSettingsHelper::getSecretKey();
        $alg = 'HS256';
        $keyId = null;
        $head = null;
        $jwt = \Bitrix\Main\Web\JWT::encode($payload, $key, $alg, $keyId, $head);
        return $jwt;
    }

    public static function createUrlForCurrentUser($userEmail)
    {
        $userEmail = CSettingsHelper::getCurrentUserEmail();
        $jwt = self::create($userEmail);
        return self::PROTOCOL_STRING . self::ISPRING_LEARN_DOMAIN . self::ISPRING_JWT_LOGIN_URL . $jwt;
    }
}
