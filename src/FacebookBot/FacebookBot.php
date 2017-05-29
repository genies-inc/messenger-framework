<?php

namespace Framework\FacebookBot;

use Framework\Bot;
use Framework\MessageBuilder;
use Framework\HttpClient\Curl;

class FacebookBot implements Bot {

  private $endPoint = 'https://graph.facebook.com/';

  private $httpClient;

  public function __construct(Curl $curl) {
    require_once './src/config/facebook.config.php';
    $this->httpClient = $curl;
  }

  public function replyMessage(String $to, MessageBuilder $builder) {
    return $this->sendMessage($to, $builder);
  }

  public function pushMessage(String $to, MessageBuilder $builder) {
    return $this->sendMessage($to, $builder);
  }

  public function parseEvents(String $requestBody) {
    return $obj = \json_decode($requestBody);
  }

  public function testSignature(String $requestBody, String $signature) {
    $array = explode('=', $signature, 2);
    // FIXME: 汚い
    $algo = $array[0] ?? 'sha1';
    $target = $array[1] ?? 'invalidString';
    if (!in_array($algo, hash_algos())) {
      return false;
    }
    $sample = hash_hmac($algo, $requestBody, FACEBOOK_APP_SECRET);

    return $sample === $target;
  }

  private function sendMessage(String $to, MessageBuilder $builder) {
    $body = [
      'recipient' => [
        'id' => $to
      ],
      'message' => $builder->buildMessage()
    ];
    return $this->httpClient->post($this->getMessageEndpoint(), null, $body, true);
  }

  private function getMessageEndpoint() {
    return $this->endPoint . 'v2.6/me/messages' . '?access_token=' . FACEBOOK_ACCESS_TOKEN;
  }

}
