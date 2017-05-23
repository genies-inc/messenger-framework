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

    if (!self::validateSignature($this->type, $this->requestBody)) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }
  }

  public function getEvents() {
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
      return [];
    }

    foreach ($requestBody->entry as $entry) {

      // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
      if (!isset($entry->messaging) || !is_array($entry->messaging)) {
        return [];
      }

      foreach ($entry->messaging as $messaging) {
        $event = new \stdClass();
        $event->userId = $messaging->sender->id ?? null;
        $event->replyToken = $messaging->sender->id ?? null;
        $event->rawData = $requestBody;

        if (isset($messaging->message)) {
          if (isset($messaging->message->attachments)) {
            $event->text = null;
            $event->files = [];
            $event->postbackData = null;
            $typeTmp = 'Message.Image';
            foreach ($messaging->message->attachments as $attachment) {
              if ($attachment->type !== 'image') {
                $typeTmp = 'invalid';
                continue;
              }
              array_push($event->files, file_get_contents($attachment->payload->url));
            }
            $event->type = $typeTmp;
            if ($event->type !== 'Message.Image') {
              $event = null;
            }
          } elseif (isset($messaging->message->text)) {
            $event->type = 'Message.Text';
            $event->text = $messaging->message->text;
            $event->files = null;
            $event->postbackData = null;
          } else {
            $event = null;
          }
        } elseif (isset($messaging->postback)) {
          $event->type = 'Postback';
          $event->text = null;
          $event->files = null;
          $event->postbackData = $messaging->postback->payload;
        } else {
          $event = null;
        }

        array_push($events, $event);
      }
    }

    return $events;
  }

  private static function buildLineEvents($jsonString) {
    $requestBody = json_decode($jsonString);
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($requestBody->events) || !is_array($requestBody->events)) {
      return [];
    }

    foreach ($requestBody->events as $baseEvent) {
      $event = new \stdClass();
      $event->userId = $baseEvent->source->userId ?? null;
      $event->replyToken = $baseEvent->replyToken ?? null;
      $event->rawData = $requestBody;

      if (isset($baseEvent->message)) {
        $event->postbackData = null;
        switch ($baseEvent->message->type) {
          case 'text' :
          $event->type = 'Message.Text';
          $event->files = null;
          $event->text = $baseEvent->message->text;
          break;
          case 'image' :
          $event->type = 'Message.Image';
          $curl = curl_init('https://api.line.me/v2/bot/message/{$baseEvent->message->id}/content');
          $options = [
            CURLOPT_RETURNTRANSFER => true
          ];
          curl_setopt_array($curl, $options);
          $event->files = [curl_exec($curl)];
          $event->text = null;
          break;
          default :
          $event = null;
          break;
        }
      } elseif (isset($baseEvent->postback)) {
        $event->type = 'Postback';
        $event->files = null;
        $event->text = null;
        $event->postbackData = $baseEvent->postback->data;
      } else {
        $event = null;
      }

      array_push($events, $event);
    }

    return $events;

  }

}
