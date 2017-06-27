<?php
/**
 * Configを定義、設定ファイルです
 *
 * @copyright Genies, Inc. All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Rintaro Ishikawa
 * @version 1.2.1
 */

namespace MessengerFramework;

/**
 * [Config] 各プラットフォームのMessengerのAPIを使うための設定を置いておくクラス
 *
 * 必要な設定項目のプロパティを追加してクラスを作って下さい。
 *
 * @access public
 * @package MessengerFramework
 */
class Config {

  /**
   * @var String FacebookのAppシークレット、リクエストの検証などに使われる
   */
  public $FACEBOOK_APP_SECRET = '';

  /**
   * @var String Faebookのアクセストークン、メッセージの送信などに使われる
   */
  public $FACEBOOK_ACCESS_TOKEN = '';

  /**
   * @var String Lineのチャンネルシークレット、リクエストの検証などに使われる
   */
  public $LINE_CHANNEL_SECRET = '';

  /**
   * @var String Lineのアクセストークン、メッセージの送信などに使われる
   */
  public $LINE_ACCESS_TOKEN = '';

}