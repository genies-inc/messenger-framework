<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\Bot;
use MessengerFramework\HttpClient\Curl;

class LineBot implements Bot {

  private static $LINE_CHANNEL_SECRET;

  private static $LINE_ACCESS_TOKEN;

  private $httpClient;

  private $endpoint = 'https://api.line.me/';

  private $templates = [];

  public function __construct(Curl $httpClient) {
    self::$LINE_CHANNEL_SECRET = getenv('LINE_CHANNEL_SECRET') ?: 'develop';
    self::$LINE_ACCESS_TOKEN = getenv('LINE_ACCESS_TOKEN') ?: 'develop';
    $this->httpClient = $httpClient;
  }

  // FIXME: 汚い
  private function popRecent3Messages() {
    $messages = [];
    $i = 0;
    while ($i < 3) {
      $message = array_pop($this->templates);
      if (is_null($message)) {
        return $messages;
      }
      array_unshift($messages, $message);
      $i += 1;
    }
    return $messages;
  }

  public function replyMessage(String $to) {

    return $this->httpClient->post($this->getReplyEndpoint(), [
      'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN
    ], [
      'replyToken' => $to,
      'messages' => $this->popRecent3Messages()
    ], true);
  }

  public function pushMessage(String $to) {
    return $this->httpClient->post($this->getPushEndpoint(), [
      'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN
    ], [
      'to' => $to,
      'messages' => $this->popRecent3Messages()
    ], true);
  }

  public function addText(String $message) {
    array_push($this->templates, [
      'type' => 'text',
      'text' => $message
    ]);
  }

  public function addCarousel(Array $columns) {
    array_push($this->templates, [
      'type' => 'template',
      'altText' => 'alt text for carousel',
      'template' => $this->buildCarousel($columns)
    ]);
  }

  private function buildCarousel($source) {
    $columns = [];
    foreach ($source as $column) {
      array_push($columns, $this->buildColumn($column));
    }
    return [
      'type' => 'carousel',
      'columns' => $columns,
    ];
  }

  private function buildColumn($source) {
    $actions = [];
    foreach ($source[3] as $button) {
      array_push($actions, $this->buildAction($button));
    }
    return [
      'thumbnailImageUrl' => $source[2],
      'title' => $source[0],
      'text' => $source[1],
      'actions' => $actions,
    ];
  }

  private function buildAction($source) {
    $action = [
      'type' => $source['action'],
      'label' => $source['title']
    ];
    switch ($source['action']) {
      case 'postback' :
      $action['data'] = $source['data'];
      break;
      case 'url' :
      $action['uri'] = $source['url'];
      $action['type'] = 'uri';
      break;
      default :
    }
    return $action;
  }

  public function addImage(String $url, String $previewUrl) {
    array_push($this->templates, [
      'type' => 'image',
      'originalContentUrl' => $url,
      'previewImageUrl' => $previewUrl,
    ]);
  }

  public function addVideo(String $url, String $previewUrl) {
    array_push($this->templates, [
      'type' => 'video',
      'originalContentUrl' => $url,
      'previewImageUrl' => $previewUrl,
    ]);
  }

  public function addAudio(String $url, Int $duration) {
    array_push($this->templates, [
      'type' => 'audio',
      'originalContentUrl' => $url,
      'duration' => $duration,
    ]);
  }

  public function testSignature(String $requestBody, String $signature) {
    $sample = hash_hmac('sha256', $requestBody, self::$LINE_CHANNEL_SECRET, true);
    return hash_equals(base64_encode($sample), $signature);
  }

  public function parseEvents(String $requestBody) {
    return \json_decode($requestBody);
  }

  public function getProfile(String $userId) {
    $res = $this->httpClient->get(
      $this->getProfileEndpoint($userId),
      ['Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN]
    );
    return json_decode($res);
  }

  // ファイル名 => バイナリ文字列
  public function getFile($event) {
    if (!isset($event->message->type) || $event->message->type === 'text') {
      return null;
    }
    switch ($event->message->type) {
      case 'image' :
      $ext = '.jpg';
      break;
      case 'video' :
      $ext = '.mp4';
      break;
      case 'audio' :
      $ext = '.m4a';
      break;
      default :
      break;
    }
    $file = $this->httpClient->get(
      $this->getContentEndpoint($event->message->id),
      [ 'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN ]
    );
    return [ $event->message->id . $ext => $file ];
  }

  private function getReplyEndpoint() {
    return $this->endpoint . 'v2/bot/message/reply';
  }

  private function getPushEndpoint() {
    return $this->endpoint . 'v2/bot/message/push';
  }

  private function getProfileEndpoint($userId) {
    return $this->endpoint . 'v2/bot/profile/' . $userId;
  }

  private function getContentEndpoint($messageId) {
    return 'https://api.line.me/v2/bot/message/' . $messageId . '/content';
  }

}
