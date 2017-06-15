<?php

namespace  MessengerFramework;

class Event {

  // MARK : Public Eventのメソッド

  public $replyToken;

  public $userId;

  public $type;

  public $rawData;

  public $text;

  public $postbackData;

  // ファイルの種類 => Id
  public $fileIds;

  public function __construct(String $replyToken, String $userId, String $type, $rawData, String $text = null, String $postbackData = null, Array $fileIds = null) {
    $this->replyToken = $replyToken;
    $this->userId = $userId;
    $this->type = $type;
    $this->rawData = $rawData;
    $this->text = $text;
    $this->postbackData = $postbackData;
    $this->fileIds = $fileIds;
  }

}

