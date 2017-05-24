<?php

namespace Framework\Test;

use PHPUnit\Framework\TestCase;
use Framework\FacebookEvent;

class FacebookEventTest extends TestCase {

  /**
   * @dataProvider messagingProvider
   */
  public function testConstructor($messaging, $expectedType) {
    $event = new FacebookEvent($messaging);
    $this->assertEquals($messaging, $event->rawData);
    $this->assertEquals($messaging->sender->id, $event->userId);
    $this->assertEquals($messaging->sender->id, $event->replyToken);
    $this->assertEquals($messaging->postback->payload ?? null, $event->postbackData);
    $this->assertEquals($messaging->message->text ?? null, $event->text);
    $this->assertEquals($expectedType, $event->type);
  }

  public function messagingProvider() {
    return [
      // request body
      // '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}'
      'text message' => [
        json_decode('{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}'),
        'Message.Text'
      ],
      // request body
      // '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"./tests/resources/message_image.jpg"}}]}}]}]}'
      'file(image) message' => [
        json_decode('{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"./tests/resources/message_image.jpg"}}]}}'),
        'Message.File'
      ],
      // request body
      // '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"sender":{"id":"1000000000000000"},"postback":{"payload":"text=Postback1\u0025E3\u002582\u002592\u0025E6\u00258A\u0025BC\u0025E3\u002581\u002597\u0025E3\u002581\u0025BE\u0025E3\u002581\u002597\u0025E3\u002581\u00259F"}}]}]}'
      'postback' => [
        json_decode('{"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"sender":{"id":"1000000000000000"},"postback":{"payload":"text=Postback1\u0025E3\u002582\u002592\u0025E6\u00258A\u0025BC\u0025E3\u002581\u002597\u0025E3\u002581\u0025BE\u0025E3\u002581\u002597\u0025E3\u002581\u00259F"}}'),
        'Postback'
      ]
    ];
  }

  /**
   * @dataProvider fileEventProvider
   */
  public function testGetFiles($event, $expected, $files) {
    $event = new FacebookEvent($event);

    // TODO: ファイルが一つしかない前提で書いている
    // リクエストを投げてくれるクラスを作ったらそれをスタブで置き換えて試すこと!
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $files[0];

    $this->assertEquals($expected, $event->getFiles());
  }

  public function fileEventProvider() {
    return [
      'file(image) message' => [
        json_decode('{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"./tests/resources/message_image.jpg"}}]}}'),
        ['message_image.jpg' => file_get_contents('./tests/resources/message_image.jpg')],
        [file_get_contents('./tests/resources/message_image.jpg')]
      ]
    ];
  }

}
