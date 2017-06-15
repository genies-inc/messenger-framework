<?php
/**
 * FacebookBotを定義
 */

namespace MessengerFramework;

/**
 * [API] FacebookのMessengerのAPIを扱うためのクラス
 *
 * @access public
 * @package MessengerFramework
 */
class FacebookBot implements Bot {

  // MARK : Constructor

  /**
   * FacebookBot constructor
   *
   * @param Curl $curl
   */
  public function __construct(Curl $curl) {
    self::$FACEBOOK_APP_SECRET = getenv('FACEBOOK_APP_SECRET') ?: 'develop';
    self::$FACEBOOK_ACCESS_TOKEN = getenv('FACEBOOK_ACCESS_TOKEN') ?: 'develop';
    $this->httpClient = $curl;
  }

  // MARK : Bot Interface の実装

  // TODO: レスポンスラッパーはこの層のこの時点で返す(Eventラッパーもこの層になったから)
  /**
   * Facebookで送信予定のメッセージを返信する
   *
   * @param String $to
   */
  public function replyMessage(String $to) {
    return $this->sendMessage($to);
  }

  /**
   * Facebookで送信予定のメッセージを送信する
   *
   * pushもreplyもやっていることは一緒だがあえて残している
   * FacebookのAPIを意識するというコンセプトであればこれを消して
   * sendMessageにまとめ、同じようにメッセージの同時送信を非対応にさせるべき
   * しかしこのフレームワークはそうではない
   *
   * @param String $to
   */
  public function pushMessage(String $to) {
    return $this->sendMessage($to);
  }

  /**
   * FacebookのWebhookリクエストを差異を吸収したEventの配列へ変換する
   *
   * @param String $requestBody
   */
  public function parseEvents(String $requestBody) {
    return self::convertFacebookEvents(\json_decode($requestBody));
  }

  /**
   * FacebookからのWebhookリクエストかどうかを確認する
   *
   * @param String $requestBody
   * @param String $signature
   */
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

  /**
   * Facebookのユーザーのプロフィールを差異を吸収したものへ変換する
   *
   * @param String $userId
   */
  public function getProfile(String $userId) {
    $res = $this->httpClient->get($this->getProfileEndpoint($userId));
    $profile = json_decode($res);
    if (!isset($profile->first_name)) {
      throw new \UnexpectedValueException('プロフィールが取得できませんでした。');
    }
    return [
      'name' => $profile->first_name . ' ' . $profile->last_name,
      'profilePic' => $profile->profile_pic,
      'rawProfile' => $profile
    ];
  }

  /**
   * FacebookのEvent(メッセージ)中に含まれるファイルを取得する
   *
   * @param Event $event
   */
  public function getFiles(Event $event) {
    $messaging = $event->rawData;
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

  // MARK : Public FacebookBotのメソッド

  /**
   * テキストメッセージを送信予定に追加する
   *
   * @param String $message
   */
  public function addText(String $message) {
    array_push($this->templates, [
      'text' => $message
    ]);
  }

  /**
   * 画像を送信予定に追加する
   *
   * @param String $url
   */
  public function addImage(String $url) {
    array_push($this->templates, $this->buildAttachment('image', [ 'url' => $url ]));
  }

  /**
   * 動画を送信予定に追加する
   *
   * @param String $url
   */
  public function addVideo(String $url) {
    array_push($this->templates, $this->buildAttachment('video', [ 'url' => $url ]));
  }

  /**
   * 音声を送信予定に追加する
   *
   * @param String $url
   */
  public function addAudio(String $url) {
    array_push($this->templates, $this->buildAttachment('audio', [ 'url' => $url ]));
  }

  /**
   * Genericメッセージを送信予定に追加する
   *
   * 書式の確認はAPI側がやってくれるのでここでは適当なデフォルト値を設定してAPIに検査は任せる
   *
   * @param Array $columns
   */
  public function addGeneric(Array $columns) {
    array_push($this->templates, $this->buildAttachment('template', $this->buildCarouselTemplate($columns)));
  }

  /**
   * Buttonメッセージを送信予定に追加する
   *
   * @param String $text
   * @param Array $replies
   */
  public function addButton(String $text, Array $replies) {
    array_push($this->templates, $this->buildAttachment('template', $this->buildButtonTemplate($text, $replies)));
  }

  // MARK : Private

  private static $FACEBOOK_APP_SECRET;

  private static $FACEBOOK_ACCESS_TOKEN;

  private $endPoint = 'https://graph.facebook.com/';

  private $httpClient;

  private $templates = [];

  private static function buildCurlErrorResponse(\Exception $e) {
    $err = new \stdClass();
    $err->message = $e->getMessage();
    $err->code = $e->getCode();
    return $err;
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
          $event = self::parseMessaging($messaging);
          array_push($events, $event);
        } catch (\InvalidArgumentException $e) {
          array_push($events, null);
        }
      }
    }

    return $events;
  }

  private static function parseMessaging($messaging) {
    $text = null;
    $postbackData = null;
    if (isset($messaging->message)) {
      if (isset($messaging->message->attachments)) {
        $type = 'Message.File';
      } elseif (isset($messaging->message->text)) {
        $type = 'Message.Text';
        $text = $messaging->message->text;
      } else {
        throw new \InvalidArgumentException('サポートされていない形式のMessaging#Messageです。');
      }
    } elseif (isset($messaging->postback)) {
      $type = 'Postback';
      $postbackData = $messaging->postback->payload;
    } else {
      throw new \InvalidArgumentException('サポートされていない形式のMessagingです。');
    }
    $userId = $messaging->sender->id;
    $replyToken = $messaging->sender->id;
    $rawData = $messaging;
    return new Event($replyToken, $userId, $type, $rawData, $text, $postbackData);
  }

  private function sendMessage(String $to) {
    $responses = [];
    foreach ($this->templates as $template) {
      $body = [
        'recipient' => [
          'id' => $to
        ],
        'message' => $template
      ];
      try {
        $res = $this->httpClient->post($this->getMessageEndpoint(), null, $body, true);
      } catch (\RuntimeException $e) {
        $res = self::buildCurlErrorResponse($e);
      }
      array_push($responses, $res);
    }
    $this->templates = [];
    return json_encode($responses);
  }

  private function buildAttachment($type, $payload) {
    return [
      'attachment' => [
        'type' => $type,
        'payload' => $payload
      ]
    ];
  }

  private function buildCarouselTemplate(Array $columns) {
    $elements = [];
    foreach ($columns as $column) {
      array_push($elements, $this->buildColumn($column));
    }
    return [
      'template_type' => 'generic',
      'elements' => $elements
    ];
  }

  private function buildButtonTemplate(String $text, Array $replies) {
    $buttons = [];
    foreach ($replies as $reply) {
      array_push($buttons, $this->buildButton($reply));
    }
    return [
      'template_type' => 'button',
      'text' => $text,
      'buttons' => $buttons
    ];
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
