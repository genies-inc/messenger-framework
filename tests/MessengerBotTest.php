<?php

namespace Genies\MessengerFramework\Test;

use \PHPUnit\Framework\TestCase;
use \Genies\MessengerFramework\MessengerBot;
use \Genies\MessengerFramework\FacebookBot;
use \Genies\MessengerFramework\LineBot;
use \Genies\MessengerFramework\Curl;
use \Genies\MessengerFramework\Config;
use \Genies\MessengerFramework\Event;

require_once './tests/utils/GLOBAL_file_get_contents-mock.php';

class MessengerBotTest extends TestCase
{

    public $facebookBotMock;

    public $lineBotMock;

    private $_curlMock;

    private $_facebookConfig;

    private $_lineConfig;

    public function setUp()
    {
        $this->curlMock = $this->getMockBuilder(Curl::class)
        ->setMethods([ 'post', 'get' ])
        ->getMock();
        $this->_facebookConfig = new Config('facebook', 'develop', 'develop');
        $this->_lineConfig = new Config('line', 'develop', 'develop');

        $this->facebookBotMock = $this->getMockBuilder(FacebookBot::class)
        ->setConstructorArgs([ $this->curlMock, $this->_facebookConfig ])
        ->setMethods([ 'testSignature', 'getProfile' ])
        ->getMock();
        $this->lineBotMock = $this->getMockBuilder(LineBot::class)
        ->setConstructorArgs([ $this->curlMock, $this->_lineConfig ])
        ->setMethods(['testSignature', 'getProfile' ])
        ->getMock();
    }

    public function testConstructorUnsupportedPlatform()
    {
        try {
            new MessengerBot(new Config('unknown', 'develop', 'develop'));
            $this->fail('サポートされていないプラットフォームなのにエラーが出ませんでした。');
        } catch (\InvalidArgumentException $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function testGetPlatform()
    {
        foreach ([ 'facebook', 'line' ] as $platform) {
            $bot = new MessengerBot(new Config($platform, 'develop', 'develop'));
            $this->assertEquals($platform, $bot->getPlatform());
        }
    }

  /**
   * @backupGlobals enabled
   */
    public function testGetEventsValidFacebook()
    {
        $this->_setValidRequestBodyFacebook();
        $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'this is a valid signature';
        $bot = new MessengerBot($this->_facebookConfig);
        $this->_setFacebookBotMockTestSignatureForce(true);
        $bot->core = $this->facebookBotMock;

        try {
            $bot->getEvents();
            $this->addToAssertionCount(1);
        } catch (\UnexpectedValueException $e) {
            $this->fail('有効なシグネチャなのにエラーが出ました。');
        }
    }

  /**
   * @backupGlobals enabled
   */
    public function testGetEventsInvalidFacebook()
    {
        $this->_setValidRequestBodyFacebook();
        $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'this is not a valid signature';
        $bot = new MessengerBot($this->_facebookConfig);
        $this->_setFacebookBotMockTestSignatureForce(false);
        $bot->core = $this->facebookBotMock;

        try {
            $bot->getEvents();
            $this->fail('無効なシグネチャなのにエラーが出ませんでした。');
        } catch (\UnexpectedValueException $e) {
            $this->addToAssertionCount(1);
        }
    }

  /**
   * @backupGlobals enabled
   */
    public function testGetEventsNoSignatureFacebook()
    {
        $this->_setValidRequestBodyFacebook();
        $bot = new MessengerBot($this->_facebookConfig);

        try {
            $bot->getEvents();
            $this->fail('シグネチャが無いのにエラーが出ませんでした。');
        } catch (\UnexpectedValueException $e) {
            $this->addToAssertionCount(1);
        }
    }

  /**
   * @backupGlobals enabled
   */
    public function testGetEventsValidLine()
    {
        $this->_setValidRequestBodyLine();
        $_SERVER['HTTP_X_LINE_SIGNATURE'] = 'this is a valid signature';
        $bot = new MessengerBot($this->_lineConfig);
        $this->_setLineBotMockTestSignatureForce(true);
        $bot->core = $this->lineBotMock;

        try {
            $bot->getEvents();
            $this->addToAssertionCount(1);
        } catch (\UnexpectedValueException $e) {
            $this->fail('有効なシグネチャなのにエラーが出ました。');
        }
    }

  /**
   * @backupGlobals enabled
   */
    public function testGetEventsInvalidLine()
    {
        $this->_setValidRequestBodyLine();
        $_SERVER['HTTP_X_LINE_SIGNATURE'] = 'this is not a valid signature';
        $bot = new MessengerBot($this->_lineConfig);
        $this->_setLineBotMockTestSignatureForce(false);
        $bot->core = $this->lineBotMock;

        try {
            $bot->getEvents();
            $this->fail('無効なシグネチャなのにエラーが出ませんでした。');
        } catch (\UnexpectedValueException $e) {
            $this->addToAssertionCount(1);
        }
    }

  /**
   * @backupGlobals enabled
   */
    public function testGetEventsNoSignatureLine()
    {
        $this->_setValidRequestBodyLine();
        $bot = new MessengerBot($this->_lineConfig);

        try {
            $bot->getEvents();
            $this->fail('シグネチャが無いのにエラーが出ませんでした。');
        } catch (\UnexpectedValueException $e) {
            $this->addToAssertionCount(1);
        }
    }

  /**
   * @dataProvider facebookRequestProvider
   * @backupGlobals enabled
   */
    public function testGetEventsFacebook($requestBody)
    {
        global $file_get_contents_rtv;
        $file_get_contents_rtv = $requestBody;
        $bot = new MessengerBot($this->_facebookConfig);
        $this->_setFacebookBotMockTestSignatureForce(true);
        $bot->core = $this->facebookBotMock;

        $this->assertContainsOnly(Event::class, $bot->getEvents());
    }

    public function facebookRequestProvider()
    {
      /*
        data case => [ requestBody ]
      */
        return [
            'facebook text message' => [ '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}' ],
            'facebook image message' => [ '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"https://www.sampleimage.com/sample.jpg"}}]}}]}]}' ],
            'facebook postback' => [ '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"sender":{"id":"1000000000000000"},"postback":{"payload":"text=Postback1\u0025E3\u002582\u002592\u0025E6\u00258A\u0025BC\u0025E3\u002581\u002597\u0025E3\u002581\u0025BE\u0025E3\u002581\u002597\u0025E3\u002581\u00259F"}}]}]}' ]
        ];
    }

  /**
   * @dataProvider facebookNotSupportedRequestProvider
   * @backupGlobals enabled
   */
    public function testGetEventsFacebookNotSupportedFormats($requestBody)
    {
        global $file_get_contents_rtv;
        $file_get_contents_rtv = $requestBody;
        $bot = new MessengerBot($this->_facebookConfig);
        $this->_setFacebookBotMockTestSignatureForce(true);
        $bot->core = $this->facebookBotMock;

        try {
            $bot->getEvents();
            $this->fail('おかしなリクエストボディなのにエラーが出ませんでした。');
        } catch (\UnexpectedValueException $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function facebookNotSupportedRequestProvider()
    {
      /*
        data case => [ requestBody ]
      */
        return [
            'facebook invalid messagings' => [ '{"entry":[{"messaging":"hoge"}]}' ],
            'facebook incompatible entry' => [ '{"entry":[{"hoge":"fuga"}]}' ],
            'facebook invalid entry' => [ '{"entry":"hoge"}' ],
            'facebook invalid body' => [ '{"hoge": "fuga"}' ]
        ];
    }

  /**
   * @dataProvider facebookNotSupportedEventRequestProvider
   * @backupGlobals enabled
   */
    public function testGetEventsFacebookNotSupportedEvent($requestBody)
    {
        global $file_get_contents_rtv;
        $file_get_contents_rtv = $requestBody;
        $bot = new MessengerBot($this->_facebookConfig);
        $this->_setFacebookBotMockTestSignatureForce(true);
        $bot->core = $this->facebookBotMock;

        $events = $bot->getEvents();
        $this->assertContainsOnly(Event::class, $events);
        foreach ($events as $event) {
            $this->assertEquals('Unsupported', $event->type);
        }
    }

    public function facebookNotSupportedEventRequestProvider()
    {
      /*
        data case => [ requestBody ]
      */
        return [
            'facebook incompatible messaging' => [ '{"entry":[{"messaging":[{"hoge":"fuga"}]}]}' ],
        ];
    }

  /**
   * @dataProvider lineRequestProvider
   * @backupGlobals enabled
   */
    public function testGetEventsLine($requestBody)
    {
        global $file_get_contents_rtv;
        $file_get_contents_rtv = $requestBody;
        $bot = new MessengerBot($this->_lineConfig);
        $this->_setLineBotMockTestSignatureForce(true);
        $bot->core = $this->lineBotMock;

        $this->assertContainsOnly(Event::class, $bot->getEvents());
    }

    public function lineRequestProvider()
    {
      /*
        data case => [ requestBody ]
      */
        return [
            'line text message' => [ '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}' ],
            'line image message' => [ '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}]}' ],
            'line postback' => [ '{"events":[{"type":"postback","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"postback":{"data":"key1=value1&key2=value2&key3=value3"}}]}' ],
        ];
    }

  /**
   * @dataProvider lineNotSupportedRequestProvider
   * @backupGlobals enabled
   */
    public function testGetEventsLineNotSupportedFormat($requestBody)
    {
        global $file_get_contents_rtv;
        $file_get_contents_rtv = $requestBody;
        $bot = new MessengerBot($this->_lineConfig);
        $this->_setLineBotMockTestSignatureForce(true);
        $bot->core = $this->lineBotMock;

        try {
            $bot->getEvents();
            $this->fail('おかしなリクエストボディなのにエラーが出ませんでした。');
        } catch (\UnexpectedValueException $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function lineNotSupportedRequestProvider()
    {
      /*
        data case => [ requestBody ]
      */
        return [
            'line invalid events' => [ '{"events":"hoge"}' ],
            'line invalid body' => [ '{"hoge": "fuga"}' ]
        ];
    }

  /**
   * @dataProvider lineNotSupportedEventRequestProvider
   * @backupGlobals enabled
   */
    public function testGetEventsLineNotSupportedEvent($requestBody)
    {
        global $file_get_contents_rtv;
        $file_get_contents_rtv = $requestBody;
        $bot = new MessengerBot($this->_lineConfig);
        $this->_setLineBotMockTestSignatureForce(true);
        $bot->core = $this->lineBotMock;

        $events = $bot->getEvents();
        $this->assertContainsOnly(Event::class, $events);
        foreach ($events as $event) {
            $this->assertEquals('Unsupported', $event->type);
        }
    }

    public function lineNotSupportedEventRequestProvider()
    {
      /*
        data case => [ requestBody ]
      */
        return [
            'line incompatible event' => [ '{"events":[{"hoge":"fuga"}]}' ]
        ];
    }

    private function _setFacebookBotMockTestSignatureForce(Bool $isSuccess)
    {
        $this->facebookBotMock->expects($this->once())
        ->method('testSignature')
        ->willReturn($isSuccess);
    }

    private function _setLineBotMockTestSignatureForce(Bool $isSuccess)
    {
        $this->lineBotMock->expects($this->once())
        ->method('testSignature')
        ->willReturn($isSuccess);
    }

    private function _setValidRequestBodyFacebook()
    {
        global $file_get_contents_rtv;
        $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
        $file_get_contents_rtv = $requestBody;
    }

    private function _setValidRequestBodyLine()
    {
        global $file_get_contents_rtv;
        $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
        $file_get_contents_rtv = $requestBody;
    }
}
