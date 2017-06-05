<?php

namespace MessengerFramework\Test;

use PHPUnit\Framework\TestCase;
use MessengerFramework\LineEvent;
use MessengerFramework\HttpClient\Curl;

class LineEventTest extends TestCase {

  private $curlMock;

  public function setUp() {
    $this->curlMock = $this->getMockBuilder(Curl::class)
      ->setMethods(['get'])
      ->getMock();
  }

  /**
   * @dataProvider eventProvider
   */
  public function testConstructor($rawEvent, $expectedType) {
    $event = new LineEvent($rawEvent, $this->curlMock);
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
  public function testGetFiles($event, $fileId) {
    $this->curlMock->expects($this->once())
      ->method('get')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/' . $fileId . '/content'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ])
      )->willReturn('test');
    $event = new LineEvent($event, $this->curlMock);
    $this->assertEquals([$fileId . '.jpg' => 'test'], $event->getFiles());
  }

  public function fileEventProvider() {
    return [
      'file(image) message' => [
        json_decode('{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}'),
        '2222222222222'
      ]
    ];
  }

}
