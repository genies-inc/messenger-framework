<?php

namespace Framework;

// MessengerBotクラスを使う側が何のBotかは最初に指定して使うのでリクエストから判別するような機能はいらない
class MessengerBot {

  private $requestBody = '';

  private $type = '';

  public function __construct($botType) {
    $this->requestBody = file_get_contents("php://input");
    $this->type = strtolower($botType);

    switch ($this->type) {
      case 'facebook' :
      require_once './src/config/facebook.config.php';
      break;
      case 'line' :
      require_once './src/config/line.config.php';
      break;
      default :
      throw new \InvalidArgumentException("指定されたプラットフォームはサポートされていません。", 1);
    }

    if (!self::validateSignature($this->type, $this->requestBody)) {
      throw new \UnexpectedValueException("正しい送信元からのリクエストではありません。");
    }
  }

  private static function validateSignature($type, $body) {

    switch ($type) {
      case 'facebook' :
      if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
        return false;
      }
      $array = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2);
      $algo = $array[0] ?? 'sha1';
      $signature = $array[1] ?? 'invalidString';
      if (!in_array($algo, hash_algos())) {
        return false;
      }
      $sample = hash_hmac($algo, $body, FACEBOOK_APP_SECRET);
      return $signature === $sample;
      break;

      case 'line' :
      if (!isset($_SERVER['HTTP_X_LINE_SIGNATURE'])) {
        return false;
      }
      $sample = hash_hmac('sha256', $body, LINE_CHANNEL_SECRET, true);
      return hash_equals(base64_encode($sample), $_SERVER['HTTP_X_LINE_SIGNATURE']);
      break;

      default :
      break;
    }

    return false;

  }

}
