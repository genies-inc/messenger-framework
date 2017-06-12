<?php

namespace  MessengerFramework;

// MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
class MessengerBot {

  private $requestBody = '';

  private $type = '';

  public $core;

  public function __construct($botType) {
    $this->requestBody = file_get_contents("php://input");
    $this->type = strtolower($botType);

    switch ($this->type) {
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

  public function getEvents() {
    if (!$this->validateSignature()) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }
    switch ($this->type) {
      case 'facebook':
      case 'line':
      return $this->core->parseEvents($this->requestBody);
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function reply(String $replyToken) {
    switch ($this->type) {
      case 'facebook' :
      case 'line' :
      return $this->core->replyMessage($replyToken);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function push(String $recipientId) {
    switch ($this->type) {
      case 'facebook' :
      case 'line' :
      return $this->core->pushMessage($recipientId);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addText(String $message) {
    switch ($this->type) {
      case 'facebook' :
      case 'line' :
      $this->core->addText($message);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addTemplate(Array $columns) {
    switch ($this->type) {
      case 'facebook' :
      $this->core->addGeneric($columns);
      break;
      case 'line' :
      $this->core->addCarousel($columns);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addImage(String $fileUrl, String $previewUrl) {
    switch ($this->type) {
      case 'facebook' :
      case 'line' :
      $this->core->addImage($fileUrl, $previewUrl);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addVideo(String $fileUrl, String $previewUrl) {
    switch ($this->type) {
      case 'facebook' :
      case 'line' :
      $this->core->addVideo($fileUrl, $previewUrl);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function addAudio(String $fileUrl, Int $duration) {
    switch ($this->type) {
      case 'facebook' :
      case 'line' :
      $this->core->addAudio($fileUrl, $duration);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  // どのプラットフォームのイベントかはMessengerBotの状態に依存する
  // 渡されたメッセージがFacebookのものであってもnew MessengerBot('line')だったらLineとして解釈
  public function getFilesIn(Event $message) {
    switch ($this->type) {
      case 'facebook' :
      return $this->core->getFiles($message);
      break;
      case 'line' :
      return $this->core->getFile($message);
      break;
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

  public function getProfile($userId) {
    $profile = $this->core->getProfile($userId);
    switch ($this->type) {
      case 'facebook' :
      if (!isset($profile->first_name)) {
        throw new \UnexpectedValueException('プロフィールが取得できませんでした。');
      }
      return [
        'name' => $profile->first_name . ' ' . $profile->last_name,
        'profilePic' => $profile->profile_pic
      ];
      case 'line' :
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

  private function validateSignature() {
    switch ($this->type) {
      case 'facebook' :
      $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? 'invalid';
      break;
      case 'line' :
      $signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? 'invalid';
      break;
      default :
      break;
    }

    return $this->core->testSignature($this->requestBody, $signature);
  }

}
