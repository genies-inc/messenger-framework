<?php

namespace Framework\Test;

use PHPUnit\Framework\TestCase;
use Framework\LineEvent;

require_once './tests/utils/GLOBAL_file_get_contents-mock.php';

class LineEventTest extends TestCase {

  /**
   * @dataProvider eventProvider
   */
  public function testConstructor($rawEvent, $expectedType) {
    $event = new LineEvent($rawEvent);
    $this->assertEquals($rawEvent, $event->rawData);
    $this->assertEquals($rawEvent->source->userId, $event->userId);
    $this->assertEquals($rawEvent->replyToken, $event->replyToken);
    $this->assertEquals($rawEvent->postback->data ?? null, $event->postbackData);
    $this->assertEquals($rawEvent->message->text ?? null, $event->text);
    $this->assertEquals($expectedType, $event->type);
  }

  public function eventProvider() {
    return [
      // request body
      // '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}'
      'text message' => [
        json_decode('{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}'),
        'Message.Text'
      ],
      // request body
      // '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}]}'
      'file(image) message' => [
        json_decode('{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}'),
        'Message.File'
      ],
      // request body
      // '{"events":[{"type":"postback","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"postback":{"data":"key1=value1&key2=value2&key3=value3"}}]}'
      'postback' => [
        json_decode('{"type":"postback","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"postback":{"data":"key1=value1&key2=value2&key3=value3"}}'),
        'Postback'
      ]
    ];
  }

  /**
   * @dataProvider fileEventProvider
   */
  public function testGetFiles($event, $expected, $files) {
    $event = new LineEvent($event);

    // TODO: ファイルが一つしかない前提で書いている
    // リクエストを投げてくれるクラスを作ったらそれをスタブで置き換えて試すこと!
    global $file_get_contents_rtv;
    $file_get_contents_rtv = $files[0];

    $this->assertEquals($expected, $event->getFiles());
  }

  public function fileEventProvider() {
    return [
      'file(image) message' => [
        json_decode('{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}'),
        ['2222222222222.jpg' => file_get_contents('./tests/resources/message_image.jpg')],
        [file_get_contents('./tests/resources/message_image.jpg')]
      ]
    ];
  }

}
