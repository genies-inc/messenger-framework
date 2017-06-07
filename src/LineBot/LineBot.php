<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\Bot;
use MessengerFramework\MessageBuilder;
use MessengerFramework\HttpClient\Curl;

class LineBot implements Bot {

  private static $LINE_CHANNEL_SECRET;

  private static $LINE_ACCESS_TOKEN;

  private $httpClient;

  private $endpoint = 'https://api.line.me/';

  public function __construct(Curl $httpClient) {
    self::$LINE_CHANNEL_SECRET = getenv('LINE_CHANNEL_SECRET') ?: 'develop';
    self::$LINE_ACCESS_TOKEN = getenv('LINE_ACCESS_TOKEN') ?: 'develop';
    $this->httpClient = $httpClient;
  }

  public function replyMessage(String $to, MessageBuilder $builder) {
    return $this->httpClient->post($this->getReplyEndpoint(), [
      'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN
    ], [
      'replyToken' => $to,
      'messages' => $builder->buildMessage()
    ], true);
  }

  public function pushMessage(String $to, MessageBuilder $builder) {
    return $this->httpClient->post($this->getPushEndpoint(), [
      'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN
    ], [
      'to' => $to,
      'messages' => $builder->buildMessage()
    ], true);
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
  public function getFiles($event) {
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
