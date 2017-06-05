<?php

namespace  MessengerFramework;

use  MessengerFramework\HttpClient\Curl;
use  MessengerFramework\FacebookBot\FacebookBot;
use  MessengerFramework\LineBot\LineBot;

use  MessengerFramework\FacebookBot as FB;
use  MessengerFramework\LineBot as Line;

// MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
class MessengerBot {

  private $requestBody = '';

  private $type = '';

  private $httpClient;

  private $core;

  private $messageWillSent = [];

  public function __construct($botType) {
    $this->requestBody = file_get_contents("php://input");
    $this->type = strtolower($botType);

    switch ($this->type) {
      case 'facebook' :
      $this->core = new FacebookBot(new Curl());
      break;
      case 'line' :
      $this->core = new LineBot(new Curl());
      break;
      default :
      throw new \InvalidArgumentException("指定されたプラットフォームはサポートされていません。", 1);
    }

  }

  public function getEvents() {
    if (!$this->validateSignature()) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }

    $rawEvents = $this->core->parseEvents($this->requestBody);
    switch ($this->type) {
      case 'facebook':
      return self::convertFacebookEvents($rawEvents);
      case 'line':
      return self::convertLineEvents($rawEvents);
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function reply(String $replyToken) {
    switch ($this->type) {
      case 'facebook' :
      $responses = [];
      foreach ($this->messageWillSent as $message) {
        $res = $this->core->replyMessage($replyToken, $message);
        array_push($responses, \json_decode($res));
      }
      $this->messageWillSent = [];
      return \json_encode($responses);
      break;
      case 'line' :
      $multiMessage = new Line\MultiMessageBuilder();
      foreach ($this->messageWillSent as $message) {
        $multiMessage->add($message);
      }
      $res = $this->core->replyMessage($replyToken, $multiMessage);
      $this->messageWillSent = [];
      return $res;
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function push(String $recipientId) {
    switch ($this->type) {
      case 'facebook' :
      $responses = [];
      foreach ($this->messageWillSent as $message) {
        $res = $this->core->pushMessage($recipientId, $message);
        array_push($responses, \json_decode($res));
      }
      $this->messageWillSent = [];
      return \json_encode($responses);
      break;
      case 'line' :
      $multiMessage = new Line\MultiMessageBuilder();
      foreach ($this->messageWillSent as $message) {
        $multiMessage->add($message);
      }
      $res = $this->core->pushMessage($recipientId, $multiMessage);
      $this->messageWillSent = [];
      return $res;
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addText(String $message) {
    switch ($this->type) {
      case 'facebook' :
      array_push($this->messageWillSent, new FB\TextMessageBuilder($message));
      break;
      case 'line' :
      array_push($this->messageWillSent, new Line\TextMessageBuilder($message));
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addTemplate(Array $columns) {
    switch ($this->type) {
      case 'facebook' :
      array_push($this->messageWillSent, new FB\GenericMessageBuilder($columns));
      break;
      case 'line' :
      array_push($this->messageWillSent, new Line\CarouselMessageBuilder($columns));
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addImage(String $fileUrl, String $previewUrl = null) {
    switch ($this->type) {
      case 'facebook' :
      array_push($this->messageWillSent, new FB\AttachmentMessageBuilder('image', $fileUrl));
      break;
      case 'line' :
      array_push($this->messageWillSent, new Line\FileMessageBuilder('image', $fileUrl, $previewUrl));
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function getProfile($userId) {
    switch ($this->type) {
      case 'facebook' :
      return $this->core->getProfile($userId);
      break;
      case 'line' :
      return $this->core->getProfile($userId);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  private function validateSignature() {
    switch ($this->type) {
      case 'facebook' :
      $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? 'invalid';
      break;
      case 'line' :
      $signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? 'invalid';
      break;
      default :
      break;
    }

    return $this->core->testSignature($this->requestBody, $signature);
  }

  private static function convertFacebookEvents($rawEvents) {
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($rawEvents->entry) || !is_array($rawEvents->entry)) {
      throw new \UnexpectedValueException('Entryがない、またはEntryがサポートされていない形式です。');
    }

    foreach ($rawEvents->entry as $entry) {

      // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
      if (!isset($entry->messaging) || !is_array($entry->messaging)) {
        throw new \UnexpectedValueException('Messagingがない、またはMessagingがサポートされていない形式です。');
      }

      foreach ($entry->messaging as $messaging) {
        try {
          $event = new FacebookEvent($messaging, new Curl());
          array_push($events, $event);
        } catch (\InvalidArgumentException $e) {
          array_push($events, null);
        }
      }
    }

    return $events;
  }

  private static function convertLineEvents($rawEvents) {
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($rawEvents->events) || !is_array($rawEvents->events)) {
      throw new \UnexpectedValueException('Eventsがない、またはEventsがサポートされていない形式です。');
    }

    foreach ($rawEvents->events as $rawEvent) {
      try {
        $event = new LineEvent($rawEvent, new Curl());
        array_push($events, $event);
      } catch (\InvalidArgumentException $e) {
        array_push($events, null);
      }
    }

    return $events;

  }

}
