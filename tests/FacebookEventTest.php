<?php

namespace MessengerFramework\Test;

use PHPUnit\Framework\TestCase;
use MessengerFramework\FacebookEvent;
use MessengerFramework\HttpClient\Curl;

// FIXME: Curlを持たせているのがおかしい、では生成時にクロージャを渡してあげる？それもややこしい
// そもそもここでトークンを見ているのもおかしい
// MessengerBot#getFilesOf($event)とするのはどうだろうか?
class FacebookEventTest extends TestCase {

  private $curlMock;

  public function setUp() {
    $this->curlMock = $this->getMockBuilder(Curl::class)
      ->setMethods(['get'])
      ->getMock();
  }

  /**
   * @dataProvider messagingProvider
   */
  public function testConstructor($messaging, $expectedType) {
    $event = new FacebookEvent($messaging, $this->curlMock);
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
  public function testGetFiles($event, $getUrl, $filename) {
    $this->curlMock->expects($this->once())
      ->method('get')
      ->with(
        $this->equalTo($getUrl)
      )->willReturn('test');
    $event = new FacebookEvent($event, $this->curlMock);
    $this->assertEquals([ $filename => 'test' ], $event->getFiles());
  }

  public function fileEventProvider() {
    return [
      'file(image) message' => [
        json_decode('{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"./tests/resources/message_image.jpg"}}]}}'),
        './tests/resources/message_image.jpg',
        'message_image.jpg'
      ]
    ];
  }

}
