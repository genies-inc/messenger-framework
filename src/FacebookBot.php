<?php
/**
 * FacebookBotを定義
 *
 * @copyright Genies, Inc. All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Rintaro Ishikawa
 */

namespace MessengerFramework;

/**
 * [API] FacebookのMessengerのAPIを扱うためのクラス
 *
 * 引数として受けるEventや結果として返すファイルの配列は各プラットフォームのものが統一されている
 *
 * @access public
 * @package MessengerFramework
 */
class FacebookBot {

  // MARK : Constructor

  /**
   * FacebookBot constructor
   *
   * @param Curl $curl
   */
  public function __construct(Curl $curl) {
    self::$_FACEBOOK_APP_SECRET = getenv('FACEBOOK_APP_SECRET') ?: 'develop';
    self::$_FACEBOOK_ACCESS_TOKEN = getenv('FACEBOOK_ACCESS_TOKEN') ?: 'develop';
    $this->_httpClient = $curl;
  }

  // MARK : Bot Interface の実装

  // TODO: レスポンスラッパーはこの層のこの時点で返す(Eventラッパーもこの層になったから)
  /**
   * Facebookで送信予定のメッセージを返信する
   *
   * @param String $to
   * @return String APIからのレスポンスやCurlのエラーをまとめた配列のJSON
   */
  public function replyMessage(String $to) {
    return $this->_sendMessage($to);
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
   * @return String APIからのレスポンスやCurlのエラーをまとめた配列のJSON
   */
  public function pushMessage(String $to) {
    return $this->_sendMessage($to);
  }

  /**
   * FacebookのWebhookリクエストを差異を吸収したEventの配列へ変換する
   *
   * @param String $requestBody
   * @return Event|null[] Eventクラスの配列
   */
  public function parseEvents(String $requestBody) {
    return self::_convertFacebookEvents(\json_decode($requestBody));
  }

  /**
   * FacebookからのWebhookリクエストかどうかを確認する
   *
   * @param String $requestBody
   * @param String $signature
   * @return Bool 正しい送信元からのリクエストかどうか
   */
  public function testSignature(String $requestBody, String $signature) {
    $array = explode('=', $signature, 2);
    // FIXME: 汚い
    $algo = $array[0] ?? 'sha1';
    $target = $array[1] ?? 'invalidString';
    if (!in_array($algo, hash_algos())) {
      return false;
    }
    $sample = hash_hmac($algo, $requestBody, self::$_FACEBOOK_APP_SECRET);

    return $sample === $target;
  }

  /**
   * Facebookのユーザーのプロフィールを差異を吸収したものへ変換する
   *
   * @param String $userId
   * @return Array プラットフォームの差異が吸収されたプロフィールを表す連想配列
   */
  public function getProfile(String $userId) {
    $res = $this->_httpClient->get($this->_getProfileEndpoint($userId));
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
   * @return Array ファイル名 => バイナリ文字列 の連想配列
   */
  public function getFiles(Event $event) {
    $messaging = $event->rawData;
    $files = [];
    foreach ($messaging->message->attachments as $attachment) {
      $url = $attachment->payload->url;
      $files[$this->_getKey($url)] = $this->_httpClient->get($url);
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
    array_push($this->_templates, [
      'text' => $message
    ]);
  }

  /**
   * 画像を送信予定に追加する
   *
   * @param String $url
   */
  public function addImage(String $url) {
    array_push($this->_templates, $this->_buildAttachment('image', [ 'url' => $url ]));
  }

  /**
   * 動画を送信予定に追加する
   *
   * @param String $url
   */
  public function addVideo(String $url) {
    array_push($this->_templates, $this->_buildAttachment('video', [ 'url' => $url ]));
  }

  /**
   * 音声を送信予定に追加する
   *
   * @param String $url
   */
  public function addAudio(String $url) {
    array_push($this->_templates, $this->_buildAttachment('audio', [ 'url' => $url ]));
  }

  /**
   * Genericメッセージを送信予定に追加する
   *
   * 書式の確認はAPI側がやってくれるのでここでは適当なデフォルト値を設定してAPIに検査は任せる
   *
   * @param Array $columns
   */
  public function addGeneric(Array $columns) {
    array_push($this->_templates, $this->_buildAttachment('template', $this->_buildCarouselTemplate($columns)));
  }

  /**
   * Buttonメッセージを送信予定に追加する
   *
   * @param String $text
   * @param Array $replies
   */
  public function addButton(String $text, Array $replies) {
    array_push($this->_templates, $this->_buildAttachment('template', $this->_buildButtonTemplate($text, $replies)));
  }

  // MARK : Private

  private static $_FACEBOOK_APP_SECRET;

  private static $_FACEBOOK_ACCESS_TOKEN;

  private $_endPoint = 'https://graph.facebook.com/';

  private $_httpClient;

  private $_templates = [];

  private static function _buildCurlErrorResponse(\Exception $e) {
    $err = new \stdClass();
    $err->message = $e->getMessage();
    $err->code = $e->getCode();
    return $err;
  }

  private static function _convertFacebookEvents($rawEvents) {
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
          $event = self::_parseMessaging($messaging);
          array_push($events, $event);
        } catch (\InvalidArgumentException $e) {
          array_push($events, null);
        }
      }
    }

    return $events;
  }

  private static function _parseMessaging($messaging) {
    $text = null;
    $postbackData = null;
    $location = null;
    if (isset($messaging->message)) {
      if (isset($messaging->message->attachments)) {
        $attachments = $messaging->message->attachments;
        if (self::_isLocationMessage($attachments)) {
          $type = 'Message.Location';
          $location = self::_buildLocation($attachments);
        } else {
          // attachmentsが含まれていて位置情報が含まれていないものはMessage.Fileとして扱う
          $type = 'Message.File';
        }
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
    return new Event($replyToken, $userId, $type, $rawData, $text, $postbackData, $location);
  }

  private function _sendMessage(String $to) {
    $responses = [];
    foreach ($this->_templates as $template) {
      $body = [
        'recipient' => [
          'id' => $to
        ],
        'message' => $template
      ];
      try {
        $res = $this->_httpClient->post($this->_getMessageEndpoint(), null, $body, true);
      } catch (\RuntimeException $e) {
        $res = self::_buildCurlErrorResponse($e);
      }
      array_push($responses, $res);
    }
    $this->_templates = [];
    return json_encode($responses);
  }

  private static function _isLocationMessage($attachments) {
    foreach ($attachments as $attachment) {
      if ($attachment->type === 'location') {
        return true;
      }
    }
    return false;
  }

  private static function _buildLocation($attachments) {
    foreach ($attachments as $attachment) {
      if ($attachment->type !== 'location') {
        continue;
      }
      return [
        'lat' => $attachment->payload->coordinates->lat,
        'long' => $attachment->payload->coordinates->long
      ];
    }
  }

  private function _buildAttachment($type, $payload) {
    return [
      'attachment' => [
        'type' => $type,
        'payload' => $payload
      ]
    ];
  }

  private function _buildCarouselTemplate(Array $columns) {
    $elements = [];
    foreach ($columns as $column) {
      array_push($elements, $this->_buildColumn($column));
    }
    return [
      'template_type' => 'generic',
      'elements' => $elements
    ];
  }

  private function _buildButtonTemplate(String $text, Array $replies) {
    $buttons = [];
    foreach ($replies as $reply) {
      array_push($buttons, $this->_buildButton($reply));
    }
    return [
      'template_type' => 'button',
      'text' => $text,
      'buttons' => $buttons
    ];
  }

  private function _buildColumn($source) {
    $buttons = [];
    foreach ($source[3] as $button) {
      array_push($buttons, $this->_buildButton($button));
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

  private function _buildButton($source) {
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

  private function _getMessageEndpoint() {
    return $this->_endPoint . 'v2.6/me/messages' . '?access_token=' . self::$_FACEBOOK_ACCESS_TOKEN;
  }

  private function _getProfileEndpoint($userId) {
    return $this->_endPoint .'v2.6/' . $userId . '?access_token=' . self::$_FACEBOOK_ACCESS_TOKEN;
  }

  private function _getKey($url) {
    preg_match('/(.*\/)+([^¥?]+)\?*/', $url, $result);
    return $result[2];
  }

}
