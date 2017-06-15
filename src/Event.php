<?php
/**
 * Eventを定義
 */

namespace  MessengerFramework;

/**
 * 各プラットフォームの差異を吸収したイベント(メッセージ)を表すクラス
 *
 * @access public
 * @package MessengerFramework
 */
class Event {

  // MARK : Public Eventのメソッド

  /**
   * @var String プラットフォームで共通して返信に使う文字列
   */
  public $replyToken;

  /**
   * @var String プラットフォームで共通して一意にユーザーを識別する文字列
   */
  public $userId;

  /**
   * @var String イベント(メッセージ)の種類
   */
  public $type;

  /**
   * @var stdClass 各プラットフォームのイベントをstdClass化したもの
   */
  public $rawData;

  /**
   * @var String|null イベント(メッセージ)についてきたテキスト
   */
  public $text;

  /**
   * @var String|null Postbackイベントで返ってきた文字列データ
   */
  public $postbackData;

  /**
   * @var Array|null lat => 緯度, long => 経度
  */
  public $location;

  /**
   * Event constructor
   *
   * @param String $replyToken
   * @param String $userId
   * @param String $type
   * @param stdClass $rawData
   * @param String|null $text
   * @param String|null $postbackData
   * @param Array|null $location lat => 緯度, long => 経度
   */
  public function __construct(String $replyToken, String $userId, String $type, $rawData, String $text = null, String $postbackData = null, $location = null) {
    $this->replyToken = $replyToken;
    $this->userId = $userId;
    $this->type = $type;
    $this->rawData = $rawData;
    $this->text = $text;
    $this->postbackData = $postbackData;
    $this->location = $location;
  }

}

