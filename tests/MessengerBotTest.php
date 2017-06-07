<?php

namespace MessengerFramework\Test;

use PHPUnit\Framework\TestCase;
use MessengerFramework;
use MessengerFramework\MessengerBot;
use MessengerFramework\FacebookBot\FacebookBot;
use MessengerFramework\LineBot\LineBot;
use MessengerFramework\HttpClient\Curl;

require_once './tests/utils/GLOBAL_file_get_contents-mock.php';

class MessengerBotTest extends TestCase {

  public $facebookBotMock;

  public $lineBotMock;

  private $curlMock;

  public function setUp() {
    $this->curlMock = $this->getMockBuilder(Curl::class)
      ->setMethods([ 'post', 'get' ])
      ->getMock();
    $this->curlMock->method('post')->willReturn('{"status":"success"}');
    $this->curlMock->method('get')->willReturn('{"status":"success"}');
    $this->facebookBotMock = $this->getMockBuilder(FacebookBot::class)
      ->setConstructorArgs([$this->curlMock])
      ->setMethods(['testSignature'])
      ->getMock();
    $this->lineBotMock = $this->getMockBuilder(LineBot::class)
      ->setConstructorArgs([$this->curlMock])
      ->setMethods(['testSignature'])
      ->getMock();
  }

  public function testConstructorUnsupportedPlatform() {
    try {
      new MessengerBot('unknown-messenger');
      $this->fail('サポートされていないプラットフォームなのにエラーが出ませんでした。');
    } catch (\InvalidArgumentException $e) {
      $this->addToAssertionCount(1);
    }
  }

  /**
   * @backupGlobals enabled
   */
  public function testGetEventsValidFacebook() {
    $this->setValidRequestBodyFacebook();
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'this is a valid signature';
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
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
  public function testGetEventsInvalidFacebook() {
    $this->setValidRequestBodyFacebook();
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'this is not a valid signature';
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(false);
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
  public function testGetEventsNoSignatureFacebook() {
    $this->setValidRequestBodyFacebook();
    $bot = new MessengerBot('facebook');

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
  public function testGetEventsValidLine() {
    $this->setValidRequestBodyLine();
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = 'this is a valid signature';
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
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
  public function testGetEventsInvalidLine() {
    $this->setValidRequestBodyLine();
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = 'this is not a valid signature';
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(false);
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
  public function testGetEventsNoSignatureLine() {
    $this->setValidRequestBodyLine();
    $bot = new MessengerBot('line');

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
  public function testGetEventsFacebook($requestBody) {
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    $this->assertContainsOnly(MessengerFramework\FacebookEvent::class, $bot->getEvents());
  }

  public function facebookRequestProvider() {
    /*
      data case => [ requestBody ]
    */
    return [
      'facebook text message' => [ '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}' ],
      'facebook image message' => [ '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"./tests/resources/message_image.jpg"}}]}}]}]}' ],
      'facebook postback' => [ '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"sender":{"id":"1000000000000000"},"postback":{"payload":"text=Postback1\u0025E3\u002582\u002592\u0025E6\u00258A\u0025BC\u0025E3\u002581\u002597\u0025E3\u002581\u0025BE\u0025E3\u002581\u002597\u0025E3\u002581\u00259F"}}]}]}' ]
    ];
  }

  /**
   * @dataProvider facebookNotSupportedRequestProvider
   * @backupGlobals enabled
   */
  public function testGetEventsFacebookNotSupportedFormats($requestBody) {
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    try {
      $bot->getEvents();
      $this->fail('おかしなリクエストボディなのにエラーが出ませんでした。');
    } catch(\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }
  }

  public function facebookNotSupportedRequestProvider() {
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
  public function testGetEventsFacebookNotSupportedEvent($requestBody) {
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    $events = $bot->getEvents();
    $this->assertEquals([null], $events);
  }

  public function facebookNotSupportedEventRequestProvider() {
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
  public function testGetEventsLine($requestBody) {
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    $this->assertContainsOnly(MessengerFramework\LineEvent::class, $bot->getEvents());
  }

  public function lineRequestProvider() {
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
  public function testGetEventsLineNotSupportedFormat($requestBody) {
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    try {
      $bot->getEvents();
      $this->fail('おかしなリクエストボディなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }
  }

  public function lineNotSupportedRequestProvider() {
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
  public function testGetEventsLineNotSupportedEvent($requestBody) {
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    $events = $bot->getEvents();
    $this->assertEquals([null], $events);
  }

  public function lineNotSupportedEventRequestProvider() {
    /*
      data case => [ requestBody ]
    */
    return [
      'line incompatible event' => [ '{"events":[{"hoge":"fuga"}]}' ]
    ];
  }

  /**
   * @backupGlobals enabled
   */
  public function testTextReplyFacebook() {
    $this->setValidRequestBodyFacebook();
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addText($event->text . ' reply test');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testTextPushFacebook() {
    $bot = new MessengerBot('facebook');
    $bot->core = $this->facebookBotMock;

    $bot->addText('test message');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider templateMessageArgumentProvider
   * @backupGlobals enabled
   */
  public function testTemplateReplyFacebook($templateArg) {
    $this->setValidRequestBodyFacebook();
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addTemplate($templateArg);
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  /**
   * @dataProvider templateMessageArgumentProvider
   */
  public function testTemplatePushFacebook($templateArg) {
    $bot = new MessengerBot('facebook');
    $bot->core = $this->facebookBotMock;

    $bot->addTemplate($templateArg);
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testImageReplyFacebook() {
    $this->setValidRequestBodyFacebook();
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addImage('https://www.sampleimage.com/sample.jpg');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testImagePushFacebook() {
    $bot = new MessengerBot('facebook');
    $bot->core = $this->facebookBotMock;

    $bot->addImage('https://www.sampleimage.com/sample.jpg');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testVideoReplyFacebook() {
    $this->setValidRequestBodyFacebook();
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addVideo('https://www.sampleimage.com/sample.mp4');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testVideoPushFacebook() {
    $bot = new MessengerBot('facebook');
    $bot->core = $this->facebookBotMock;

    $bot->addVideo('https://www.sampleimage.com/sample.mp4');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testAudioReplyFacebook() {
    $this->setValidRequestBodyFacebook();
    $bot = new MessengerBot('facebook');
    $this->setFacebookBotMockTestSignatureForce(true);
    $bot->core = $this->facebookBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addAudio('https://www.sampleimage.com/sample.mp3');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testAudioPushFacebook() {
    $bot = new MessengerBot('facebook');
    $bot->core = $this->facebookBotMock;

    $bot->addVideo('https://www.sampleimage.com/sample.mp3');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testTextReplyLine() {
    $this->setValidRequestBodyLine();
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addText($event->text . ' reply test');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testTextPushLine() {
    $bot = new MessengerBot('line');
    $bot->core = $this->lineBotMock;

    $bot->addText('test message');
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider templateMessageArgumentProvider
   * @backupGlobals enabled
   */
  public function testTemplateReplyLine($templateArg) {
    $this->setValidRequestBodyLine();
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addTemplate($templateArg);
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  /**
   * @dataProvider templateMessageArgumentProvider
   */
  public function testTemplatePushLine($templateArg) {
    $bot = new MessengerBot('line');
    $bot->core = $this->lineBotMock;

    $bot->addTemplate($templateArg);
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testImageReplyLine() {
    $this->setValidRequestBodyLine();
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testImagePushLine() {
    $bot = new MessengerBot('line');
    $bot->core = $this->lineBotMock;

    $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testVideoReplyLine() {
    $this->setValidRequestBodyLine();
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testVideoPushLine() {
    $bot = new MessengerBot('line');
    $bot->core = $this->lineBotMock;

    $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testAudioReplyLine() {
    $this->setValidRequestBodyLine();
    $bot = new MessengerBot('line');
    $this->setLineBotMockTestSignatureForce(true);
    $bot->core = $this->lineBotMock;

    foreach ($bot->getEvents() as $event) {
      $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testAudioPushLine() {
    $bot = new MessengerBot('line');
    $bot->core = $this->lineBotMock;

    $bot->addVideo('https://www.sampleimage.com/sample.m4a', 10000);
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function templateMessageArgumentProvider() {
    return [
      'template height unmatched' => [
        [['テンプレートタイトル', 'テンプレートの説明', 'https://www.sampleimage.com/thumbnail1.jpg', [
          'title' => 'Postbackボタン',
          'action' => 'postback',
          'data' => 'key1=value1&key2=value2'
        ], [
          'title' => 'Messageボタン',
          'action' => 'url',
          'url' => 'http://hoge.com/fuga.jpg'
        ]],['テンプレートタイトル2', 'テンプレートの説明2', 'https://www.sampleimage.com/thumbnail2.jpg', [
          'title' => 'Postbackボタン2',
          'action' => 'postback',
          'data' => 'key1=value1&key2=value2'
        ], [
          'title' => 'Messageボタン2',
          'action' => 'url',
          'url' => 'http://hoge.com/fuga.jpg'
        ], [
          'title' => 'Messageボタン3',
          'action' => 'url',
          'url' => 'http://hoge.com/fuga.jpg'
        ]]]
      ]
    ];

  }

  /**
   * @dataProvider profileDataProvider
   */
  public function testGetProfile($platform, $userId) {
    $bot = new MessengerBot($platform);
    try {
      $bot->getProfile($userId);
      $this->fail('おかしなリクエストを送ったのにエラーになっていないです。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }
  }

  public function profileDataProvider() {
    return [
      'facebook profile' => [
        'facebook', '1000000000000000'
      ],
      'line profile' => [
        'line', '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
      ]
    ];
  }

  private function setFacebookBotMockTestSignatureForce(Bool $isSuccess) {
    $this->facebookBotMock->expects($this->once())
      ->method('testSignature')
      ->willReturn($isSuccess);
  }

  private function setLineBotMockTestSignatureForce(Bool $isSuccess) {
    $this->lineBotMock->expects($this->once())
      ->method('testSignature')
      ->willReturn($isSuccess);
  }

  private function setValidRequestBodyFacebook() {
    global $file_get_contents_rtv;
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $file_get_contents_rtv = $requestBody;
  }

  private function setValidRequestBodyLine() {
    global $file_get_contents_rtv;
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $file_get_contents_rtv = $requestBody;
  }

}
