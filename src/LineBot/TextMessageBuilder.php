<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\MessageBuilder;

class TextMessageBuilder implements MessageBuilder {

  private $text;

  public function __construct(String $message) {
    $this->text = $message;
  }

  public function buildMessage() {
    return [
      [
        'type' => 'text',
        'text' => $this->text
      ]
    ];
  }

}
