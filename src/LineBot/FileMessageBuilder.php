<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\MessageBuilder;

class FileMessageBuilder implements MessageBuilder {

  private $type;

  private $filePath;

  private $previewData;

  private $previewKeyName;

  public function __construct(String $type, String $filePath, $previewData) {
    $this->type = $type;
    $this->filePath = $filePath;
    $this->previewData = $previewData;
    switch ($type) {
      case 'image' :
      $this->previewKeyName = 'previewImageUrl';
      break;
      case 'video' :
      $this->previewKeyName = 'previewImageUrl';
      break;
      case 'audio' :
      $this->previewKeyName = 'duration';
      break;
      default :
      $this->previewKeyName = 'previewImageUrl';
    }
  }

  public function buildMessage() {
    return [
      [
        'type' => $this->type,
        'originalContentUrl' => $this->filePath,
        $this->previewKeyName => $this->previewData,
      ]
    ];
  }

}
