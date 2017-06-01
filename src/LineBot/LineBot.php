<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\Bot;
use MessengerFramework\MessageBuilder;
use MessengerFramework\HttpClient\Curl;

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
    $sample = hash_hmac('sha256', $requestBody, LINE_CHANNEL_SECRET, true);
    return hash_equals(base64_encode($sample), $signature);
  }

  public function parseEvents(String $requestBody) {
    return \json_decode($requestBody);
  }

  private function getReplyEndpoint() {
    return $this->endpoint . 'v2/bot/message/reply';
  }

  private function getPushEndpoint() {
    return $this->endpoint . 'v2/bot/message/push';
  }

}
