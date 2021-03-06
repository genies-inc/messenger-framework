<?php
/**
 * Curlを定義
 *
 * @copyright Genies, Inc. All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Rintaro Ishikawa
 * @version 1.6.0
 */

namespace Genies\MessengerFramework;

/**
 * Curlを扱うラッパークラス
 *
 * Webhookリクエストに応答するためにタイムアウトが設定してある
 *
 * @access public
 * @package MessengerFramework
 */
class Curl
{

  // MARK : Public Curlクラスのメソッド

    /**
     * getリクエストを送る
     *
     * 環境変数PROXY_URLがセットされていたらそれをプロキシとして使う
     *
     * @param String $url
     * @param Array $headers
     * @param Array $queryArray
     *
     * @return String レスポンスボディ
     * @throws RuntimeException curl_exec()時にエラーが発生した時（curl_errno()の戻り値がOK出ない時）
     */
    public function get(String $url, array $headers = [], array $queryArray = [])
    {
        $query = http_build_query($queryArray);
        $url .= "?{$query}";
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $this->_toHeaderArray($headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_TIMEOUT => self::$_AWAIT_SECOND
        ]);

        $this->_setProxyCurl($ch);

        $response =  curl_exec($ch);
        $code = curl_errno($ch);
        if ($code !== CURLE_OK) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException($message, $code);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * postリクエストを送る
     *
     * 環境変数PROXY_URLがセットされていたらそれをプロキシとして使う
     *
     * @param String $url
     * @param Array $headers
     * @param Array $bodyArray
     * @param Bool $isJSON
     *
     * @return String レスポンスボディ
     * @throws RuntimeException curl_exec()時にエラーが発生した時（curl_errno()の戻り値がOK出ない時）
     */
    public function post(String $url, array $headers = [], array $bodyArray = [], Bool $isJSON = false)
    {
        $ch = curl_init($url);
        if ($isJSON) {
            $headers['Content-Type'] = 'application/json';
        }

        $this->_setProxyCurl($ch);

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $this->_toHeaderArray($headers ?? []),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $isJSON ? json_encode($bodyArray) : \http_build_query($bodyArray),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::$_AWAIT_SECOND
        ]);
        $response = curl_exec($ch);
        $code = curl_errno($ch);
        if ($code !== CURLE_OK) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException($message, $code);
        }
        curl_close($ch);
        return $response;
    }

    // MARK : Private

    private static $_AWAIT_SECOND = 12;

    private function _toHeaderArray(array $from)
    {
        $header = [];
        foreach ($from as $key => $value) {
            array_push($header, join(': ', [$key, $value]));
        }
        return $header;
    }

    private function _setProxyCurl($ch)
    {
        $fixieUrl = getenv('PROXY_URL');
        if ($fixieUrl === false) {
            return;
        }
        $parsedFixieUrl = parse_url($fixieUrl);
        $proxy = $parsedFixieUrl['host'].":".$parsedFixieUrl['port'];
        $proxyAuth = $parsedFixieUrl['user'].":".$parsedFixieUrl['pass'];
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
        return $ch;
    }
}
