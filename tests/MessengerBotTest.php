<?php

namespace MessengerFramework\Test;

use PHPUnit\Framework\TestCase;
use MessengerFramework;
use MessengerFramework\MessengerBot;

require_once './tests/utils/GLOBAL_file_get_contents-mock.php';
require_once './tests/utils/GLOBAL_curl_exec-mock.php';

class MessengerBotTest extends TestCase {

  const IMAGE_PATH = './tests/resources/message_image.jpg';

  const FACEBOOK_APP_SECRET = 'develop';

  const LINE_CHANNEL_SECRET = 'develop';

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
  public function testGetEventsValidationFacebook() {
    global $file_get_contents_rtv;

    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $file_get_contents_rtv = $requestBody;

    try {
      $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
      $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
      $bot = new MessengerBot('facebook');
      $bot->getEvents();
      $this->addToAssertionCount(1);
    } catch (\UnexpectedValueException $e) {
      $this->fail('有効なシグネチャなのにエラーが出ました。');
    }

    unset($_SERVER['HTTP_X_HUB_SIGNATURE']);
    try {
      $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'hoge=invalid signature';
      $bot = new MessengerBot('facebook');
      $bot->getEvents();
      $this->fail('無効なハッシュ化アルゴリズムなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

    unset($_SERVER['HTTP_X_HUB_SIGNATURE']);
    try {
      $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'invalid signature';
      $bot = new MessengerBot('facebook');
      $bot->getEvents();
      $this->fail('無効なシグネチャなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

    unset($_SERVER['HTTP_X_HUB_SIGNATURE']);
    try {
      $bot = new MessengerBot('facebook');
      $bot->getEvents();
      $this->fail('シグネチャが無いのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }
  }

  /**
   * @backupGlobals enabled
   */
  public function testGetEventsValidationLine() {
    global $file_get_contents_rtv;

    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $file_get_contents_rtv = $requestBody;

    try {
      $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));
      $bot = new MessengerBot('line');
      $bot->getEvents();
      $this->addToAssertionCount(1);
    } catch (\UnexpectedValueException $e) {
      $this->fail('有効なシグネチャなのにエラーが出ました。');
    }

    unset($_SERVER['HTTP_X_LINE_SIGNATURE']);
    try {
      $_SERVER['HTTP_X_LINE_SIGNATURE'] = 'invalid signature';
      $bot = new MessengerBot('line');
      $bot->getEvents();
      $this->fail('無効なシグネチャなのにエラーが出ませんでした。');
    } catch (\UnexpectedValueException $e) {
      $this->addToAssertionCount(1);
    }

    unset($_SERVER['HTTP_X_LINE_SIGNATURE']);
    try {
      $bot = new MessengerBot('line');
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
    // Facebookからのリクエストとして設定する
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('facebook');
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
    // Facebookからのリクエストとして設定する
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
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
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
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
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    // 内部でファイル(画像)を取得する時返す画像を設定
    global $curl_exec_rtv;
    $curl_exec_rtv = file_get_contents(self::IMAGE_PATH);

    $bot = new MessengerBot('line');
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
    // Lineからのリクエストとして設定をする
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));
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
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));
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

  /**
   * @backupGlobals enabled
   */
  public function testTextReplyFacebook() {
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    // Facebookからのリクエストとして設定する
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト返信用にわざわざ作っているのを必要な部分だけにする
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');

    foreach ($bot->getEvents() as $event) {
      $bot->addText($event->text . ' reply test');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testTextPushFacebook() {
    $bot = new MessengerBot('facebook');

    $bot->addText('test message');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider templateMessageArgumentProvider
   * @backupGlobals enabled
   */
  public function testTemplateReplyFacebook($templateArg) {
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    // Facebookからのリクエストとして設定する
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト返信用にわざわざ作っているのを必要な部分だけにする
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');

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

    $bot->addTemplate($templateArg);
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testImageReplyFacebook() {
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    // Facebookからのリクエストとして設定する
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト返信用にわざわざ作っているのを必要な部分だけにする
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');

    foreach ($bot->getEvents() as $event) {
      $bot->addImage('https://www.sampleimage.com/sample.jpg');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testImagePushFacebook() {
    // Facebookからのリクエストとして設定する
    $bot = new MessengerBot('facebook');

    $bot->addImage('https://www.sampleimage.com/sample.jpg');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testVideoReplyFacebook() {
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    // Facebookからのリクエストとして設定する
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト返信用にわざわざ作っているのを必要な部分だけにする
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');

    foreach ($bot->getEvents() as $event) {
      $bot->addVideo('https://www.sampleimage.com/sample.mp4');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testVideoPushFacebook() {
    // Facebookからのリクエストとして設定する
    $bot = new MessengerBot('facebook');

    $bot->addVideo('https://www.sampleimage.com/sample.mp4');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testAudioReplyFacebook() {
    $requestBody = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    // Facebookからのリクエストとして設定する
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト返信用にわざわざ作っているのを必要な部分だけにする
    $signature = hash_hmac('sha1', $requestBody, self::FACEBOOK_APP_SECRET);
    $_SERVER['HTTP_X_HUB_SIGNATURE'] = 'sha1=' . $signature;
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('facebook');

    foreach ($bot->getEvents() as $event) {
      $bot->addAudio('https://www.sampleimage.com/sample.mp3');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testAudioPushFacebook() {
    // Facebookからのリクエストとして設定する
    $bot = new MessengerBot('facebook');

    $bot->addVideo('https://www.sampleimage.com/sample.mp3');
    $response = $bot->push('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testTextReplyLine() {
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト送信用にわざわざ作っているのを必要な部分だけにする
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));

    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');

    foreach ($bot->getEvents() as $event) {
      $bot->addText($event->text . ' reply test');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testTextPushLine() {
    $bot = new MessengerBot('line');

    $bot->addText('test message');
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider templateMessageArgumentProvider
   * @backupGlobals enabled
   */
  public function testTemplateReplyLine($templateArg) {
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト送信用にわざわざ作っているのを必要な部分だけにする
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));

    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');

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

    $bot->addTemplate($templateArg);
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testImageReplyLine() {
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト送信用にわざわざ作っているのを必要な部分だけにする
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));

    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');

    foreach ($bot->getEvents() as $event) {
      $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testImagePushLine() {
    $bot = new MessengerBot('line');

    $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testVideoReplyLine() {
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト送信用にわざわざ作っているのを必要な部分だけにする
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));

    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');

    foreach ($bot->getEvents() as $event) {
      $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testVideoPushLine() {
    $bot = new MessengerBot('line');

    $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
    $response = $bot->push('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  /**
   * @backupGlobals enabled
   */
  public function testAudioReplyLine() {
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    // TODO: リクエスト検証処理をスタブして有効なリクエストボディをテキスト送信用にわざわざ作っているのを必要な部分だけにする
    $_SERVER['HTTP_X_LINE_SIGNATURE'] = base64_encode(hash_hmac('sha256', $requestBody, self::LINE_CHANNEL_SECRET, true));

    global $file_get_contents_rtv;
    $file_get_contents_rtv = $requestBody;
    $bot = new MessengerBot('line');

    foreach ($bot->getEvents() as $event) {
      $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
      $bot->reply($event->replyToken);
      $this->addToAssertionCount(1);
    }
  }

  public function testAudioPushLine() {
    $bot = new MessengerBot('line');

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


}
