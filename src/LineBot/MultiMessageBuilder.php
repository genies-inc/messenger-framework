<?php

namespace Framework\LineBot;

use Framework\MessageBuilder;

class MultiMessageBuilder implements MessageBuilder {

  private $builders = [];

  public function add(MessageBuilder $builder) {
    array_push($this->builders, $builder);
  }

  public function buildMessage() {
    $messages = [];
    foreach ($this->builders as $builder) {
      foreach ($builder->buildMessage() as $message) {
        array_push($messages, $message);
      }
    }
    return $messages;
  }

}
