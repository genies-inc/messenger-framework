<?php

namespace  MessengerFramework;

// 正直サブクラス作るより外で組み立てるクラスを用意したほうが良さそうな予感もする
// その場合コンストラクタの引数がすごいことになるのが問題
abstract class Event {

  public $replyToken = null;

  public $userId = null;

  public $type = null;

  public $rawData = null;

  public $text = null;

  public $postbackData = null;

  abstract public function getFiles();

}

