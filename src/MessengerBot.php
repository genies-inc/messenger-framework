<?php

namespace Framework;

use Framework\HttpClient\Curl;
use Framework\FacebookBot\FacebookBot;

// MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
class MessengerBot {

  private $requestBody = '';

  private $type = '';

  private $httpClient;

  private $core;

  public function __construct($botType) {
    $this->requestBody = file_get_contents("php://input");
    $this->type = strtolower($botType);

    switch ($this->type) {
      case 'facebook' :
      $this->core = new FacebookBot(new Curl());
      break;
      case 'line' :
      require_once './src/config/line.config.php';
      break;
      default :
      throw new \InvalidArgumentException("指定されたプラットフォームはサポートされていません。", 1);
    }

  }

  public function getEvents() {

    if (!$this->validateSignature($this->type, $this->requestBody)) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }

    switch ($this->type) {
      case 'facebook':
      $rawEvent = $this->core->parseEvents($this->requestBody);
      return self::convertFacebookEvent($rawEvent);
      case 'line':
      return self::buildLineEvents($this->requestBody);
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function reply(String $replyToken) {
    switch ($this->type) {
      case 'facebook' :
      break;
      case 'line' :
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function push(String $recipientId) {
    switch ($this->type) {
      case 'facebook' :
      break;
      case 'line' :
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addText(String $message) {
    switch ($this->type) {
      case 'facebook' :
      break;
      case 'line' :
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addTemplate(Array $columns) {
    switch ($this->type) {
      case 'facebook' :
      break;
      case 'line' :
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addFile(String $fileUrl, ...$options) {
    switch ($this->type) {
      case 'facebook' :
      break;
      case 'line' :
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function getProfile($userId) {
    switch ($this->type) {
      case 'facebook' :
      return tester($userId);
      break;
      case 'line' :
      return tester($userId);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  // TODO: LineのラッパーができたらBotインタフェースで何とかする
  private function validateSignature($type, $body) {

    switch ($type) {
      case 'facebook' :
      if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
        return false;
      }
      return $this->core->testSignature($body, $_SERVER['HTTP_X_HUB_SIGNATURE']);
      case 'line' :
      if (!isset($_SERVER['HTTP_X_LINE_SIGNATURE'])) {
        return false;
      }
      $sample = hash_hmac('sha256', $body, LINE_CHANNEL_SECRET, true);
      return hash_equals(base64_encode($sample), $_SERVER['HTTP_X_LINE_SIGNATURE']);
      break;

      default :
      break;
    }

    return false;

  }

  private static function convertFacebookEvent($rawEvent) {
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($rawEvent->entry) || !is_array($rawEvent->entry)) {
      throw new \UnexpectedValueException('Entryがない、またはEntryがサポートされていない形式です。');
    }

    foreach ($rawEvent->entry as $entry) {

      // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
      if (!isset($entry->messaging) || !is_array($entry->messaging)) {
        throw new \UnexpectedValueException('Messagingがない、またはMessagingがサポートされていない形式です。');
      }

      foreach ($entry->messaging as $messaging) {
        try {
          $event = new FacebookEvent($messaging);
          array_push($events, $event);
        } catch (\InvalidArgumentException $e) {
          array_push($events, null);
        }
      }
    }

    return $events;
  }

  private static function buildLineEvents($jsonString) {
    $requestBody = json_decode($jsonString);
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($requestBody->events) || !is_array($requestBody->events)) {
      throw new \UnexpectedValueException('Eventsがない、またはEventsがサポートされていない形式です。');
    }

    foreach ($requestBody->events as $rawEvent) {
      try {
        $event = new LineEvent($rawEvent);
        array_push($events, $event);
      } catch (\InvalidArgumentException $e) {
        array_push($events, null);
      }
    }

    return $events;

  }

}
