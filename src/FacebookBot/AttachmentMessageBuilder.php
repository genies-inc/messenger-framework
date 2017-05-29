<?php

namespace Framework\FacebookBot;

use Framework\MessageBuilder;

class AttachMentMessageBuilder implements MessageBuilder {

  private $type;

  private $fileUrl;

  public function __construct(String $type, String $fileUrl) {
    $this->type = $type;
    $this->fileUrl = $fileUrl;
  }

  public function buildMessage() {
    return [
      'attachment' => [
        'type' => $this->type,
        'payload' => [
          'url' => $this->fileUrl
        ]
      ]
    ];
  }

}
