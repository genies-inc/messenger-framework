<?php

namespace  MessengerFramework;

interface Bot {

  public function replyMessage(String $to);

  public function pushMessage(String $to);

  public function parseEvents(String $requestBody);

  public function testSignature(String $requestBody, String $signature);

  public function getProfile(String $userId);

  public function getFiles(Event $event);

}
