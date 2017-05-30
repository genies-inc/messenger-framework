<?php

namespace Framework\LineBot;

use Framework\Bot;
use Framework\MessageBuilder;
use Framework\HttpClient\Curl;

class LineBot implements Bot {

  private $httpClient;

  private $endpoint = 'https://api.line.me/';

  public function __construct(Curl $httpClient) {
    require_once './src/config/line.config.php';
    $this->httpClient = $httpClient;
  }

  public function replyMessage(String $to, MessageBuilder $builder) {
    return $this->httpClient->post($this->getReplyEndpoint(), [
      'Authorization' => 'Bearer ' . LINE_ACCESS_TOKEN
    ], [
      'replyToken' => $to,
      'messages' => $builder->buildMessage()
    ], true);
  }

  public function pushMessage(String $to, MessageBuilder $builder) {
    return $this->httpClient->post($this->getPushEndpoint(), [
      'Authorization' => 'Bearer ' . LINE_ACCESS_TOKEN
    ], [
      'to' => $to,
      'messages' => $builder->buildMessage()
    ], true);
  }

  public function testSignature(String $requestBody, String $signature) {
    throw new \BadMethodCallException('まだ実装されていません。');
  }

  public function parseEvents(String $requestBody) {
    throw new \BadMethodCallException('まだ実装されていません。');
  }

  private function getReplyEndpoint() {
    return $this->endpoint . 'v2/bot/message/reply';
  }

  private function getPushEndpoint() {
    return $this->endpoint . 'v2/bot/message/push';
  }

}
