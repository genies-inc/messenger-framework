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
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $replyToken;

  /**
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $userId;

  /**
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $type;

  /**
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $rawData;

  /**
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $text;

  /**
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $postbackData;

  // ファイルの種類 => Id
  /**
   * @var Bot 各プラットフォームのBotインタフェース
   */
  public $fileIds;

  /**
   * @var Array lat => 緯度, long => 経度
  */
  public $location;

  /**
   * Event constructor
   *
   * @param String $replyToken
   * @param String $userId
   * @param String $type
   * @param mix $rawData
   * @param String $text
   * @param String $postbackData
   * @param Array $fileIds
   * @param Array $location lat => 緯度, long => 経度
   */
  public function __construct(String $replyToken, String $userId, String $type, $rawData, String $text = null, String $postbackData = null, Array $fileIds = null, $location = null) {
    $this->replyToken = $replyToken;
    $this->userId = $userId;
    $this->type = $type;
    $this->rawData = $rawData;
    $this->text = $text;
    $this->postbackData = $postbackData;
    $this->fileIds = $fileIds;
    $this->location = $location;
  }

}

