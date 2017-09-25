<?php

namespace Genies\MessengerFramework\Test;

use \PHPUnit\Framework\TestCase;
use \Genies\MessengerFramework\Curl;

class CurlTest extends TestCase
{

    private static $_NODE_PID;

    private static $_NODE_PORT = '8080';

    private static $_NODE_HOST = 'localhost';

    private static $_NODE_DIR = 'node';

    private static $_URL;

    public static function setUpBeforeClass()
    {
        $cmd = 'PORT=' . self::$_NODE_PORT . ' HOST=' . self::$_NODE_HOST . ' ' . self::$_NODE_DIR . ' ./devtool/mirror_server/mirror.js >/dev/null 2>&1 & echo $!';
        exec($cmd, $output);
        self::$_NODE_PID = $output[0];

        if (self::$_NODE_PID <= 0) {
            echo 'ミラーサーバーの起動でエラーが出ました。';
            return;
        }

        self::$_URL = 'http://' . self::$_NODE_HOST . ':' . self::$_NODE_PORT;

      // TODO: サーバーの起動を待っているスリープをもう少しマシな方法に置き換える
        usleep(500000);
    }

    public static function tearDownAfterClass()
    {
        if (self::$_NODE_PID <= 0 || empty(self::$_NODE_PID)) {
            echo 'ミラーサーバーが起動していません。';
            return;
        }

        \passthru('kill ' . self::$_NODE_PID, $ret);

        if ($ret !== 0) {
            echo 'ミラーサーバーを正しく終了できませんでした。';
            return;
        }
    }

    public function setUp()
    {
        if (empty(self::$_NODE_PID)) {
            $this->fail('ミラーサーバーが起動していません。');
        }
    }

    public function testGet()
    {
        $curl = new Curl();
        $res = $curl->get(self::$_URL);
        $jsonObject = \json_decode($res);
        $this->assertEquals("GET", $jsonObject->request->method);
    }

    public function testGetQuery()
    {
        $curl = new Curl();
        $query = ['key1' => 'value1'];
        $res = $curl->get(self::$_URL, null, $query);
        $jsonObject = \json_decode($res, true);
        $this->assertEquals("GET", $jsonObject['request']['method']);
        $this->assertEquals($query, $jsonObject['request']['query']);
    }

    public function testHeader()
    {
        $curl = new Curl();
        $header = [
        'TEST' => 'value'
        ];
        $res = $curl->get(self::$_URL, $header);
        $jsonObject = json_decode($res, true);
        $this->assertEquals("GET", $jsonObject['request']['method']);
        $responseHeader = $jsonObject['header'];
        foreach ($header as $key => $value) {
            $this->assertArrayHasKey(strtolower($key), $responseHeader);
            $this->assertEquals($value, $responseHeader[strtolower($key)]);
        }
    }

    public function testPostJSONBody()
    {
        $curl = new Curl();
        $body = [
        'key1' => 'value1',
        'key2' => 'value2'
        ];
        $res = $curl->post(self::$_URL, null, $body, true);
        $jsonObject = \json_decode($res);
        $this->assertEquals("POST", $jsonObject->request->method);
        $this->assertEquals($body, json_decode($jsonObject->body, true));
    }

    public function testPostURLEncodedBody()
    {
        $curl = new Curl();
        $body = [
        'key1' => 'value1',
        'key2' => 'value2'
        ];
        $res = $curl->post(self::$_URL, null, $body);
        $jsonObject = \json_decode($res);
        $this->assertEquals("POST", $jsonObject->request->method);
        $this->assertEquals(\http_build_query($body), $jsonObject->body);
    }

    public function testHeaderContentJSON()
    {
        $curl = new Curl();
        $res = $curl->post(self::$_URL, null, null, true);
        $jsonObject = \json_decode($res, true);
        $returnedRequestHeader = $jsonObject['header'];
        $this->assertArrayHasKey('content-type', $returnedRequestHeader);
        $this->assertEquals('application/json', $returnedRequestHeader['content-type']);
    }
}
