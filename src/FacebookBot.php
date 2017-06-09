<?php

namespace MessengerFramework;

class FacebookBot implements Bot {

  private static $FACEBOOK_APP_SECRET;

  private static $FACEBOOK_ACCESS_TOKEN;

  private $endPoint = 'https://graph.facebook.com/';

  private $httpClient;

  private $templates = [];

  public function __construct(Curl $curl) {
    self::$FACEBOOK_APP_SECRET = getenv('FACEBOOK_APP_SECRET') ?: 'develop';
    self::$FACEBOOK_ACCESS_TOKEN = getenv('FACEBOOK_ACCESS_TOKEN') ?: 'develop';
    $this->httpClient = $curl;
  }

  public function replyMessage(String $to) {
    return $this->sendMessage($to);
  }

  public function pushMessage(String $to) {
    return $this->sendMessage($to);
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

  // ファイル名 => バイナリ文字列
  public function getFiles($messaging) {
    if (!isset($messaging->message->attachments)) {
      return null;
    }
    $files = [];
    foreach ($messaging->message->attachments as $attachment) {
      $url = $attachment->payload->url;
      $files[$this->getKey($url)] = $this->httpClient->get($url);
    }
    return $files;
  }

  public function setText(String $message) {
    array_push($this->templates, [
      'text' => $message
    ]);
  }

  // 書式の確認はAPI側がやってくれるのでここでは適当なデフォルト値を設定してAPIに検査は任せる
  public function setGeneric(Array $columns) {
    $elements = [];
    foreach ($columns as $column) {
      array_push($elements, $this->buildColumn($column));
    }
    array_push($this->templates, [
      'attachment' => [
        'type' => 'template',
        'payload' => [
          'template_type' => 'generic',
          'elements' => $elements
        ]
      ]
    ]);
  }

  private function buildColumn($source) {
    $buttons = [];
    foreach ($source[3] as $button) {
      array_push($buttons, $this->buildButton($button));
    }

    $column = [
      'title' => $source[0],
      'subtitle' => $source[1],
      'buttons' => $buttons
    ];

    if (!is_null($source[2])) {
      $column['image_url'] = $source[2];
    }

    return $column;
  }

  private function buildButton($source) {
    $button = [
      'type' => $source['action'],
      'title' => $source['title']
    ];
    switch ($source['action']) {
      case 'postback' :
      $button['payload'] = $source['data'];
      break;
      case 'url' :
      $button['type'] = 'web_url';
      $button['url'] = $source['url'];
      break;
      default :
    }
    return $button;
  }

  private function setAttachment($type, $url) {
    array_push($this->templates, [
      'attachment' => [
        'type' => $type,
        'payload' => [
          'url' => $url
        ]
      ]
    ]);
  }

  public function setImage(String $url) {
    $this->setAttachment('image', $url);
  }

  public function setVideo(String $url) {
    $this->setAttachment('video', $url);
  }

  public function setAudio(String $url) {
    $this->setAttachment('audio', $url);
  }

  private function sendMessage(String $to) {
    $body = [
      'recipient' => [
        'id' => $to
      ],
      'message' => array_shift($this->templates)
    ];
    return $this->httpClient->post($this->getMessageEndpoint(), null, $body, true);
  }

  private function getMessageEndpoint() {
    return $this->endPoint . 'v2.6/me/messages' . '?access_token=' . self::$FACEBOOK_ACCESS_TOKEN;
  }

  private function getProfileEndpoint($userId) {
    return $this->endPoint .'v2.6/' . $userId . '?access_token=' . self::$FACEBOOK_ACCESS_TOKEN;
  }

  private function getKey($url) {
    preg_match('/(.*\/)+([^¥?]+)\?*/', $url, $result);
    return $result[2];
  }

}