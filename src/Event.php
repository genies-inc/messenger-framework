<?php
/**
 * Eventを定義
 *
 * @copyright Genies, Inc. All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Rintaro Ishikawa
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
   * @var Array|null
   * 'text' => イベント(メッセージ)についてきたテキスト
   * 'postback' => Postbackイベントで返ってきた文字列データ
   * 'location' => lat => 緯度, long => 経度
   **/
  public $data;

  /**
   * Event constructor
   *
   * @param String $replyToken
   * @param String $userId
   * @param String $type
   * @param stdClass $rawData
   * @param Array|null $data
   */
  public function __construct(String $replyToken, String $userId, String $type, $rawData, Array $data = null) {
    $this->replyToken = $replyToken;
    $this->userId = $userId;
    $this->type = $type;
    $this->rawData = $rawData;
    $this->data = $data;
  }

}

