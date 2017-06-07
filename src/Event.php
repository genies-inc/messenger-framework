<?php

namespace  MessengerFramework;

class Event {

  public $replyToken;

  public $userId;

  public $type;

  public $rawData;

  public $text;

  public $postbackData;

  public function __construct(String $replyToken, String $userId, String $type, $rawData, String $text = null, String $postbackData = null) {
    $this->replyToken = $replyToken;
    $this->userId = $userId;
    $this->type = $type;
    $this->rawData = $rawData;
    $this->text = $text;
    $this->postbackData = $postbackData;
  }

}

