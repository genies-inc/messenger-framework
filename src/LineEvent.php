<?php

namespace  MessengerFramework;

use  MessengerFramework\Event;

class LineEvent extends Event {

  // [ Event#Message#Type => ファイルのID ]
  private $fileIds = null;

  public function __construct($event) {
    $this->userId = $event->source->userId ?? null;
    $this->replyToken = $event->replyToken ?? null;
    $this->rawData = $event;

    if (!isset($event->type)) {
      throw new \InvalidArgumentException('このタイプのイベントには対応していません。');
    }

    switch ($event->type) {
      case 'message' :
      if ($event->message->type === 'text') {
        $this->type = 'Message.Text';
        $this->text = $event->message->text;
        break;
      }
      $this->type = 'Message.File';
      $this->fileIds = [$event->message->type => $event->message->id];
      break;
      case 'postback' :
      $this->type = 'Postback';
      $this->postbackData = $event->postback->data;
      break;
      default :
      throw new \InvalidArgumentException('このタイプのイベントには対応していません。');
    }
  }

  public function getFiles() {
    if (is_null($this->fileIds)) {
      return null;
    }
    $files = [];
    foreach ($this->fileIds as $type => $id) {
      $filename = $this->keyBuilder($type, $id);
      $files[$filename] = file_get_contents();
    }
    return $files;
  }

  private function keyBuilder($type, $id) {
    switch ($type) {
      case 'image' :
      return $id . '.jpg';
      default :
      throw new \LogicException('仕様からここが実行されることはありえません。');
    }
  }

}
