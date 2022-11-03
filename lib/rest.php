<?

namespace Openregion\Ispringintegration;

use Openregion\Ispringintegration\XmlConverter;

class Rest
{
    public static function get($host, $endpointFromApi, $headers)
    {
        $ch = curl_init($host . $endpointFromApi);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);

        $arRes = curl_exec($ch);
        if ($arRes === false) {
            return [];
        }

        $xmlToArray = new XmlConverter();
        return $xmlToArray->parse($arRes);
    }


    public static function post($host, $endpointFromApi, $headers, $data = [])
    {

        $ch = curl_init($host . $endpointFromApi);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, XmlConverter::compose($data));
        $out = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($out === false) {
            return $err;
        }

        $xmlToArray = new XmlConverter();
        $result = $xmlToArray->parse($out);

        if (empty($result)) {
            $result = 'OK, 200';
        }

        return $result;
    }


    public static function delete($host, $endpointFromApi, $headers)
    {
        $ch = curl_init($host . $endpointFromApi);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        return $result;
    }
};
