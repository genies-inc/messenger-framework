<?php
/**
 * Configを定義、設定ファイルです
 *
 * @copyright Genies, Inc. All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Rintaro Ishikawa
 * @version 1.3.2
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

  // MARK : Constructor

  /**
   * Config constructor
   * 二番目以降の引数を指定しなかったら環境変数から設定が読み込まれる
   *
   * @param String $platform
   * @param String[] $keys シークレット、トークンの順
   */
  public function __construct(String $platform, ...$keys) {
    $this->_platform = $platform;
    switch ($platform) {
      case 'facebook' :
      $this->_FACEBOOK_APP_SECRET = $keys[0] ?? \getenv('FACEBOOK_APP_SECRET');
      $this->_FACEBOOK_ACCESS_TOKEN = $keys[1] ?? \getenv('FACEBOOK_ACCESS_TOKEN');
      break;
      case 'line' :
      $this->_LINE_CHANNEL_SECRET = $keys[0] ?? \getenv('LINE_CHANNEL_SECRET');
      $this->_LINE_ACCESS_TOKEN = $keys[1] ?? \getenv('LINE_ACCESS_TOKEN');
      break;
    }
  }

  // MARK : Public Configクラスのメソッド

  /**
   * どのプラットフォームのConfigかを取得する
   *
   * @return String プラットフォームの名前
   */
  public function getPlatform() {
    return $this->_platform;
  }

  /**
   * Facebookのアプリシークレットを取得する
   *
   * @return String Facebookのアプリシークレット
   */
  public function getFacebookAppSecret() {
    return $this->_FACEBOOK_APP_SECRET;
  }

  /**
   * Facebookのアクセストークンを取得する
   *
   * @return String Facebookのアクセストークン
   */
  public function getFacebookAccessToken() {
    return $this->_FACEBOOK_ACCESS_TOKEN;
  }

  /**
   * Lineのチャンネルシークレットを取得する
   *
   * @return String Lineのチャンネルシークレット
   */
  public function getLineChannelSecret() {
    return $this->_LINE_CHANNEL_SECRET;
  }

  /**
   * Lineのアクセストークンを取得する
   *
   * @return String Lineのアクセストークン
   */
  public function getLineAccessToken() {
    return $this->_LINE_ACCESS_TOKEN;
  }

  // Private

  private $_platform;

  private $_FACEBOOK_APP_SECRET;

  private $_FACEBOOK_ACCESS_TOKEN;

  private $_LINE_CHANNEL_SECRET;

  private $_LINE_ACCESS_TOKEN;

}
