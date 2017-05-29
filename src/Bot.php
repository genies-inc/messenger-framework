<?php

namespace Framework;

use Framework\MessageBuilder;

interface Bot {

  public function replyMessage(String $to, MessageBuilder $builder);

  public function pushMessage(String $to, MessageBuilder $builder);

}
