<?php

namespace MessengerFramework;

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

  public function replyMessage(String $to) {
    $templates = $this->templates;
    $this->templates = [];
    try {
      $res = $this->httpClient->post($this->getReplyEndpoint(), [
        'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN
      ], [
        'replyToken' => $to,
        'messages' => $templates
      ], true);
    } catch (\RuntimeException $e) {
      $res = self::buildCurlErrorResponse($e);
    }
    return json_encode($res);
  }

  public function pushMessage(String $to) {
    $templates = $this->templates;
    $this->templates = [];
    try {
      $res = $this->httpClient->post($this->getPushEndpoint(), [
        'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN
      ], [
        'to' => $to,
        'messages' => $templates
      ], true);
    } catch (\RuntimeException $e) {
      $res = self::buildCurlErrorResponse($e);
    }
    return json_encode($res);
  }

  public function addText(String $message) {
    array_push($this->templates, [
      'type' => 'text',
      'text' => $message
    ]);
  }

  public function addCarousel(Array $columns) {
    array_push($this->templates, $this->buildTemplate(
      'alt text for carousel',
      $this->buildCarousel($columns)
    ));
  }

  private function buildTemplate(String $altText, Array $template) {
    return [
      'type' => 'template',
      'altText' => $altText,
      'template' => $template
    ];
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

  public function addConfirm(String $text, Array $buttons) {
    $confirm = [
      'type' => 'confirm',
      'text' => $text,
      'actions' => $this->buildAction($buttons)
    ];
    array_push($this->templates, $this->buildTemplate(
      'alt text for confirm',
      $confirm
    ));
  }

  public function testSignature(String $requestBody, String $signature) {
    $sample = hash_hmac('sha256', $requestBody, self::$LINE_CHANNEL_SECRET, true);
    return hash_equals(base64_encode($sample), $signature);
  }

  public function parseEvents(String $requestBody) {
    return self::convertLineEvents(\json_decode($requestBody));
  }

  public function getProfile(String $userId) {
    $res = $this->httpClient->get(
      $this->getProfileEndpoint($userId),
      ['Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN]
    );
    return json_decode($res);
  }

  // ファイル名 => バイナリ文字列
  public function getFiles(Event $event) {
    $rawEvent = $event->rawData;
    if (!isset($rawEvent->message->type) || $rawEvent->message->type === 'text') {
      return null;
    }
    switch ($rawEvent->message->type) {
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
      $this->getContentEndpoint($rawEvent->message->id),
      [ 'Authorization' => 'Bearer ' . self::$LINE_ACCESS_TOKEN ]
    );
    return [ $rawEvent->message->id . $ext => $file ];
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

  private static function buildCurlErrorResponse(\Exception $e) {
    $err = new \stdClass();
    $err->message = $e->getMessage();
    $err->code = $e->getCode();
    return $err;
  }

  private static function convertLineEvents($rawEvents) {
    $events = [];

    // 最下層まで展開してイベントとしての判断ができない時はからの配列を返す
    if (!isset($rawEvents->events) || !is_array($rawEvents->events)) {
      throw new \UnexpectedValueException('Eventsがない、またはEventsがサポートされていない形式です。');
    }

    foreach ($rawEvents->events as $rawEvent) {
      try {
        $event = self::parseEvent($rawEvent);
        array_push($events, $event);
      } catch (\InvalidArgumentException $e) {
        array_push($events, null);
      }
    }

    return $events;

  }

  private static function parseEvent($event) {
    $text = null;
    $postbackData = null;
    if (!isset($event->type)) {
      throw new \InvalidArgumentException('このタイプのイベントには対応していません。');
    }

    switch ($event->type) {
      case 'message' :
      if ($event->message->type === 'text') {
        $type = 'Message.Text';
        $text = $event->message->text;
        break;
      }
      $type = 'Message.File';
      break;
      case 'postback' :
      $type = 'Postback';
      $postbackData = $event->postback->data;
      break;
      default :
      throw new \InvalidArgumentException('このタイプのイベントには対応していません。');
    }
    $userId = $event->source->userId;
    $replyToken = $event->replyToken;
    $rawData = $event;
    return new Event($replyToken, $userId, $type, $rawData, $text, $postbackData);
  }

}
