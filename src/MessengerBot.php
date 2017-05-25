<?php

namespace Framework;

// MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
class MessengerBot {

  private $requestBody = '';

  private $type = '';

  public function __construct($botType) {
    $this->requestBody = file_get_contents("php://input");
    $this->type = strtolower($botType);

    switch ($this->type) {
      case 'facebook' :
      require_once './src/config/facebook.config.php';
      break;
      case 'line' :
      require_once './src/config/line.config.php';
      break;
      default :
      throw new \InvalidArgumentException("指定されたプラットフォームはサポートされていません。", 1);
    }

  }

  public function getEvents() {

    if (!self::validateSignature($this->type, $this->requestBody)) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }

    switch ($this->type) {
      case 'facebook':
      return self::buildFacebookEvents($this->requestBody);
      case 'line':
      return self::buildLineEvents($this->requestBody);
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  private static function validateSignature($type, $body) {

    switch ($type) {
      case 'facebook' :
      if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
        return false;
      }
      $array = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2);
      $algo = $array[0] ?? 'sha1';
      $signature = $array[1] ?? 'invalidString';
      if (!in_array($algo, hash_algos())) {
        return false;
      }
      $sample = hash_hmac($algo, $body, FACEBOOK_APP_SECRET);
      return $signature === $sample;
      break;

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

  private static function buildFacebookEvents($jsonString) {
    $requestBody = json_decode($jsonString);
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($requestBody->entry) || !is_array($requestBody->entry)) {
      throw new \UnexpectedValueException('Entryがない、またはEntryがサポートされていない形式です。');
    }

    foreach ($requestBody->entry as $entry) {

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
