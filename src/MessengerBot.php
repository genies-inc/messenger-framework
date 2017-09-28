<?php
/**
 * MessengerBotを定義
 *
 * @copyright Genies, Inc. All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Rintaro Ishikawa
 * @version 1.5.1
 */

namespace  Genies\MessengerFramework;

/**
 * [API] 各プラットフォームのMessengerBotのAPIを統一的なインタフェースで扱うラッパー
 *
 * MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
 *
 * @access public
 * @package MessengerFramework
 */
class MessengerBot
{

    /**
     * MessengerBot constructor
     *
     * @param Config $config
     * @package MessengerFramework
     */
    public function __construct(Config $config)
    {
        switch (strtolower($config->getPlatform())) {
            case 'facebook':
                $this->core = new FacebookBot(new Curl(), $config);
                break;
            case 'line':
                $this->core = new LineBot(new Curl(), $config);
                break;
            default:
                throw new \InvalidArgumentException("指定されたプラットフォームはサポートされていません。", 1);
        }
    }

    // MARK : Public MessengerBotのメソッド

    /**
     * @var FacebookBot|LineBot 各プラットフォームのBotインタフェース
     */
    public $core;

    /**
     * Webhookリクエストをもとにどのプラットフォームの差異を吸収したEventの配列を返す
     *
     * @return Event|null[] プラットフォームの差異を吸収したEventの配列
     */
    public function getEvents()
    {
        $requestBody = file_get_contents("php://input");
        if (!$this->_validateSignature($requestBody)) {
            throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
        }
        return $this->core->parseEvents($requestBody);
    }

    /**
     * replyTokenを使って追加してきたメッセージを返信する
     *
     * @param String $replyToken
     * @return String APIからのレスポンスやCurlのエラーをまとめた配列のJSON
     * @throws RuntimeException curlの実行時に起きるエラー
     */
    public function reply(String $replyToken)
    {
        return $this->core->replyMessage($replyToken);
    }

    /**
     * recipientIdに向けて追加してきたメッセージを送信する
     *
     * @param String $recipientId
     * @return String APIからのレスポンスやCurlのエラーをまとめた配列のJSON
     * @throws RuntimeException curlの実行時に起きるエラー
     */
    public function push(String $recipientId)
    {
        return $this->core->pushMessage($recipientId);
    }

    /**
     * 現在送信予定としてスタックされているメッセージを取得する
     *
     * Facebookならmessage、Lineならmessagesの中身にあたるもの
     *
     * @return Array メッセージを送信するAPIに渡すPayloadのメッセージ部分
     */
    public function getMessagePayload()
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
                return $this->core->getMessagePayloads();
            case $this->core instanceof LineBot:
                return $this->core->getMessagePayload();
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * 現在送信予定としてスタックされているメッセージをリセットする
     *
     * @return Void
     */
    public function clearMessages()
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
            case $this->core instanceof LineBot:
                $this->core->clearMessages();
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * テキストメッセージを送信予定に追加する
     *
     * @param String $message
     */
    public function addText(String $message)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
            case $this->core instanceof LineBot:
                $this->core->addText($message);
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * 画像を送信予定に追加する
     *
     * @param String $fileUrl
     * @param String $previewUrl
     */
    public function addImage(String $fileUrl, String $previewUrl)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
            case $this->core instanceof LineBot:
                $this->core->addImage($fileUrl, $previewUrl);
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * 動画を送信予定に追加する
     *
     * @param String $fileUrl
     * @param String $previewUrl
     */
    public function addVideo(String $fileUrl, String $previewUrl)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
            case $this->core instanceof LineBot:
                $this->core->addVideo($fileUrl, $previewUrl);
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * 音声を送信予定に追加する
     *
     * @param String $fileUrl
     * @param Int $duration
     */
    public function addAudio(String $fileUrl, Int $duration)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
            case $this->core instanceof LineBot:
                $this->core->addAudio($fileUrl, $duration);
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * Confirmメッセージを送信予定に追加する
     *
     * buttonsにMessageボタンを含められないので
     * Lineではボタンを押してもユーザーの発言として表示されない
     *
     * @param String $text
     * @param Array $buttons
     */
    public function addConfirm(String $text, array $buttons)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
                return $this->core->addButton($text, $buttons);
            case $this->core instanceof LineBot:
                return $this->core->addConfirm($text, $buttons);
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * テンプレートメッセージを送信予定に追加する
     * LineBotを操作している時、1カラムであったら内部でCarouselではなくButtonsが使われる
     *
     * @param Array $columns
     */
    public function addTemplate(array $columns)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
                $this->core->addGeneric($columns);
                break;
            case $this->core instanceof LineBot:
                if (count($columns) === 1) {
                    $this->core->addButtons(
                        $columns[0][1],
                        $columns[0][3],
                        $columns[0][0],
                        $columns[0][2]
                    );
                    return;
                }
                $this->core->addCarousel($columns);
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * ボタンメッセージを送信予定に追加する
     *
     * @param String $description ボタンメッセージの説明
     * @param Array $buttons ボタンメッセージについてくるボタン
     */
    public function addButtons(String $description, array $buttons)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
                return $this->core->addButton($description, $buttons);
            case $this->core instanceof LineBot:
                return $this->core->addButtons($description, $buttons);
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * 各プラットフォームの書式通りのメッセージを連想配列で送信予定に追加する
     *
     * @param Array $message
     */
    public function addRawMessage(array $message)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
            case $this->core instanceof LineBot:
                $this->core->addRawMessage($message);
                break;
            default:
                throw new \LogicException('仕様からここが実行されることはありえません。');
        }
    }

    /**
     * 発生したEvent(メッセージ)中のファイルを取得する
     *
     * どのプラットフォームのEventとして解釈するかはこのMessengerBotクラスの状態に依存
     *
     * @param Event $message
     * @return Array ファイル名 => バイナリ文字列 な連想配列
     * @throws RuntimeException curlの実行時に起きるエラー
     */
    public function getFilesIn(Event $message)
    {
        return $this->core->getFiles($message);
    }

    /**
     * userIdをもとにプロフィールを取得する
     *
     * userIdをどのプラットフォームのものとして扱うのかはMessengerBotの状態に依存
     *
     * @param String $userId
     * @return stdClass name -> ユーザー名, profilePic -> プロフィール画像のURL, rawProfile -> 元データ
     * @throws RuntimeException curlの実行時に起きるエラー
     */
    public function getProfile($userId)
    {
        return $this->core->getProfile($userId);
    }

    /**
     * MessengerBotがどのプラットフォームのラッパーとして動作しているかを取得
     *
     * @return String プラットフォーム名(小文字)
     */
    public function getPlatform()
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
                return 'facebook';
            case $this->core instanceof LineBot:
                return 'line';
        }
        throw new \LogicException('仕様からここが実行されることはありえません。');
    }

    // MARK : Private

    private function _validateSignature($requestBody)
    {
        switch (true) {
            case $this->core instanceof FacebookBot:
                $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? 'invalid';
                break;
            case $this->core instanceof LineBot:
                $signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? 'invalid';
                break;
            default:
                break;
        }

        return $this->core->testSignature($requestBody, $signature);
    }
}
