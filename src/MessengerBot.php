<?php

namespace  MessengerFramework;

// MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
class MessengerBot {

  public function __construct($botType) {
    switch (strtolower($botType)) {
      case 'facebook' :
      $this->core = new FacebookBot(new Curl());
      break;
      case 'line' :
      $this->core = new LineBot(new Curl());
      break;
      default :
      throw new \InvalidArgumentException("指定されたプラットフォームはサポートされていません。", 1);
    }
  }

  // MARK : Public MessengerBotのメソッド

  public $core;

  public function getEvents() {
    $requestBody = file_get_contents("php://input");
    if (!$this->validateSignature($requestBody)) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }
    return $this->core->parseEvents($requestBody);
  }

  public function reply(String $replyToken) {
    return $this->core->replyMessage($replyToken);
  }

  public function push(String $recipientId) {
    return $this->core->pushMessage($recipientId);
  }

  public function addText(String $message) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      case $this->core instanceof LineBot :
      $this->core->addText($message);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addImage(String $fileUrl, String $previewUrl) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      case $this->core instanceof LineBot :
      $this->core->addImage($fileUrl, $previewUrl);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addVideo(String $fileUrl, String $previewUrl) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      case $this->core instanceof LineBot :
      $this->core->addVideo($fileUrl, $previewUrl);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addAudio(String $fileUrl, Int $duration) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      case $this->core instanceof LineBot :
      $this->core->addAudio($fileUrl, $duration);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  // はい、いいえにボタンを設定できる
  // つまりURLやPostbackを設定できるのだがLineではMessageにしないと発言内容が出ない
  public function addConfirm(String $text, Array $buttons) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      return $this->core->addButton($text, $buttons);
      case $this->core instanceof LineBot :
      return $this->core->addConfirm($text, $buttons);
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addTemplate(Array $columns) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      $this->core->addGeneric($columns);
      break;
      case $this->core instanceof LineBot :
      $this->core->addCarousel($columns);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  // どのプラットフォームのイベントかはMessengerBotの状態に依存する
  // 渡されたメッセージがFacebookのものであってもnew MessengerBot('line')だったらLineとして解釈
  public function getFilesIn(Event $message) {
    return $this->core->getFiles($message);
  }

  // TODO : 分ける処理を下に押し込む
  public function getProfile($userId) {
    $profile = $this->core->getProfile($userId);
    switch (true) {
      case $this->core instanceof FacebookBot :
      if (!isset($profile->first_name)) {
        throw new \UnexpectedValueException('プロフィールが取得できませんでした。');
      }
      return [
        'name' => $profile->first_name . ' ' . $profile->last_name,
        'profilePic' => $profile->profile_pic
      ];
      case $this->core instanceof LineBot :
      if (!isset($profile->displayName)) {
        throw new \UnexpectedValueException('プロフィールが取得できませんでした。');
      }
      return [
        'name' => $profile->displayName,
        'profilePic' => $profile->pictureUrl
      ];
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  // MARK : Private

  private function validateSignature($requestBody) {
    switch (true) {
      case $this->core instanceof FacebookBot :
      $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? 'invalid';
      break;
      case $this->core instanceof LineBot :
      $signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? 'invalid';
      break;
      default :
      break;
    }

    return $this->core->testSignature($requestBody, $signature);
  }

}
