<?php

namespace Framework\Test;

use PHPUnit\Framework\TestCase;
use Framework\MessengerBot;

require_once './tests/utils/GLOBAL_file_get_contents-mock.php';

class MessengerBotTest extends TestCase {

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

}
