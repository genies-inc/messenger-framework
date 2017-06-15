<?php
/**
 * Botを定義
 */

namespace  MessengerFramework;

/**
 * [API] 各プラットフォームのMessengerBotのAPIを扱うインタフェース
 *
 * 引数として受けるEventや結果として返すファイルの配列は各プラットフォームのものが統一されている
 *
 * @access public
 * @package MessengerFramework
 */
interface Bot {

  // MARK : Public

  /**
   * toに向けてメッセージを返信する
   *
   * @param String $to
   * @return String APIからのレスポンスやCurlのエラーをまとめた配列のJSON
   */
  public function replyMessage(String $to);

  /**
   * toに向けてメッセージを送信する
   *
   * @param String $to
   * @return String APIからのレスポンスやCurlのエラーをまとめた配列のJSON
   */
  public function pushMessage(String $to);

  /**
   * requestBodyから各プラットフォームの差異を吸収したEventを取り出す
   *
   * @param String $requestBody
   * @return Event|null[] Eventクラスの配列
   */
  public function parseEvents(String $requestBody);

  /**
   * signatureから正しい送信元からのリクエストかを確認する
   *
   * @param String $requestBody
   * @param String $signature
   * @return Bool 正しい送信元からのリクエストかどうか
   */
  public function testSignature(String $requestBody, String $signature);

  /**
   * userIdからプラットフォームの差異を吸収したプロフィールを取得する
   *
   * @param String $userId
   * @return Array プラットフォームの差異が吸収されたプロフィールを表す連想配列
   */
  public function getProfile(String $userId);

  /**
   * event(メッセージ)に含まれるファイルを取得する
   *
   * @param Event $event
   * @return Array ファイル名 => バイナリ文字列 の連想配列
   */
  public function getFiles(Event $event);

}
