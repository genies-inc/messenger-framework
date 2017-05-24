<?php

namespace Framework\Test;

use PHPUnit\Framework\TestCase;
use Framework;
use Framework\MessengerBot;

require_once './tests/utils/GLOBAL_file_get_contents-mock.php';
require_once './tests/utils/GLOBAL_curl_exec-mock.php';

class MessengerBotTest extends TestCase {

  const IMAGE_PATH = './tests/resources/message_image.jpg';

  /**
   * @backupGlobals enabled
   */
  public function testConstructorFacebook() {
    global $file_get_contents_rtv;

    require_once './src/config/facebook.config.php';
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $file_get_contents_rtv = $requestBody;

    try {
      $signature = hash_hmac('sha1', $requestBody, FACEBOOK_APP_SECRET);
      $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
      $bot = new MessengerBot('facebook');
      $this->addToAssertionCount(1);
    } catch (\UnexpectedValueException $e) {
      $this->fail('有効なシグネチャなのにエラーが出ました。');
    }

    unset($_SERVER['HTTP_X_HUB_SIGNATURE']);
    try {
      $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'hoge=invalid signature';
      $bot = new MessengerBot('facebook');
      $this->fail('無効なハッシュ化アルゴリズムなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

    unset($_SERVER['HTTP_X_HUB_SIGNATURE']);
    try {
      $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'invalid signature';
      $bot = new MessengerBot('facebook');
      $this->fail('無効なシグネチャなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

    unset($_SERVER['HTTP_X_HUB_SIGNATURE']);
    try {
      $bot = new MessengerBot('facebook');
      $this->fail('シグネチャが無いのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

  }

  /**
   * @backupGlobals enabled
   */
  public function testConstructorLine() {
    global $file_get_contents_rtv;

    require_once './src/config/line.config.php';
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $file_get_contents_rtv = $requestBody;

    try {
      $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, LINE_CHANNEL_SECRET, true));
      $bot = new MessengerBot('line');
      $this->addToAssertionCount(1);
    } catch (\UnexpectedValueException $e) {
      $this->fail('有効なシグネチャなのにエラーが出ました。');
    }

    unset($_SERVER['HTTP_X_LINE_SIGNATURE']);
    try {
      $_SERVER['HTTP_X_LINE_SIGNATURE'] = 'invalid signature';
      $bot = new MessengerBot('line');
      $this->fail('無効なシグネチャなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

    unset($_SERVER['HTTP_X_LINE_SIGNATURE']);
    try {
      $bot = new MessengerBot('line');
      $this->fail('シグネチャが無いのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

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
   * @dataProvider facebookRequestProvider
   * @backupGlobals enabled
   */
  public function testGetEventsFacebook($requestBody) {
    // Facebookからのリクエストとして設定する
    require_once './src/config/facebook.config.php';
    $signature = hash_hmac('sha1', $requestBody, FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('facebook');
    $this->assertContainsOnly(Framework\FacebookEvent::class, $bot->getEvents());
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
    // Facebookからのリクエストとして設定する
    require_once './src/config/facebook.config.php';
    $signature = hash_hmac('sha1', $requestBody, FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('facebook');
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
    // Facebookからのリクエストとして設定する
    require_once './src/config/facebook.config.php';
    $signature = hash_hmac('sha1', $requestBody, FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('facebook');
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
    // Lineからのリクエストとして設定をする
    require_once './src/config/line.config.php';
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, LINE_CHANNEL_SECRET, true));
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('line');
    $this->assertContainsOnly(Framework\LineEvent::class, $bot->getEvents());
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
    // Lineからのリクエストとして設定をする
    require_once './src/config/line.config.php';
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, LINE_CHANNEL_SECRET, true));
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('line');

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
    // Lineからのリクエストとして設定をする
    require_once './src/config/line.config.php';
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, LINE_CHANNEL_SECRET, true));
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('line');
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

}
