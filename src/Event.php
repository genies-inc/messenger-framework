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
   * @var String|null プラットフォームで共通して返信に使う文字列
   */
  public $replyToken;

  /**
   * @var String|null プラットフォームで共通して一意にユーザーを識別する文字列
   */
  public $userId;

  /**
   * @var String イベント(メッセージ)の種類
   */
  public $type;

  /**
   * @var stdClass|null 各プラットフォームのイベントをstdClass化したもの
   */
  public $rawData;

  /**
   * @var Array
   * 'text' => イベント(メッセージ)についてきたテキスト
   * 'postback' => Postbackイベントで返ってきた文字列データ
   * 'location' => lat => 緯度, long => 経度
   * 'sticker' => ステッカーId(Lineの場合はPackageIdとカンマ区切り)
   **/
  public $data;

  /**
   * @var Array|null プラットフォーム独自のイベントのデータ
   */
  public $origin;

  /**
   * Event constructor
   *
   * @param String|null $replyToken
   * @param String|null $userId
   * @param String $type
   * @param stdClass $rawData
   * @param Array|null $data
   * @param Array|null $origin
   */
  public function __construct(String $replyToken = null, String $userId = null, String $type, $rawData, Array $data = null, Array $origin = null) {
    $this->replyToken = $replyToken;
    $this->userId = $userId;
    $this->type = $type;
    $this->rawData = $rawData;
    $this->data = $data;
    $this->origin = $origin;
  }

}

