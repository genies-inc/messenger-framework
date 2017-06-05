<?php

namespace MessengerFramework\FacebookBot;

use MessengerFramework\Bot;
use MessengerFramework\MessageBuilder;
use MessengerFramework\HttpClient\Curl;

class FacebookBot implements Bot {

  private static $FACEBOOK_APP_SECRET;

  private static $FACEBOOK_ACCESS_TOKEN;

  private $endPoint = 'https://graph.facebook.com/';

  private $httpClient;

  public function __construct(Curl $curl) {
    self::$FACEBOOK_APP_SECRET = getenv('FACEBOOK_APP_SECRET') ?: 'develop';
    self::$FACEBOOK_ACCESS_TOKEN = getenv('FACEBOOK_ACCESS_TOKEN') ?: 'develop';
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
    $sample = hash_hmac($algo, $requestBody, self::$FACEBOOK_APP_SECRET);

    return $sample === $target;
  }

  public function getProfile(String $userId) {
    $res = $this->httpClient->get($this->getProfileEndpoint($userId));
    return json_decode($res);
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
    return $this->endPoint . 'v2.6/me/messages' . '?access_token=' . self::$FACEBOOK_ACCESS_TOKEN;
  }

  private function getProfileEndpoint($userId) {
    return $this->endPoint .'v2.6/' . $userId . '?access_token=' . self::$FACEBOOK_ACCESS_TOKEN;
  }

}
