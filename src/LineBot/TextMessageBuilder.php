<?php

namespace Framework\LineBot;

use Framework\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder as SDKTextMessageBuilder;

class TextMessageBuilder implements MessageBuilder {

  private $text;

  public function __construct(String $message) {
    $this->text = $message;
  }

  public function buildMessage() {
    return (new SDKTextMessageBuilder($this->text))->buildMessage();
  }

}
