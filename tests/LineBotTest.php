<?php

namespace MessengerFramework\Test;

use MessengerFramework\LineBot;
use MessengerFramework\Curl;
use MessengerFramework\Event;
use PHPUnit\Framework\TestCase;

class LineTest extends TestCase {

  private $_curlMock;

  public function setUp() {
    $this->_curlMock = $this->getMockBuilder(Curl::class)
      ->setMethods([ 'post', 'get' ])
      ->getMock();
    /*
      postのモックについて
      with(
        $url,
        $headers,
        $bodyArray,
        $isJSON
      )
    */
  }

  public function testReplyTextMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [
            [
              'type' => 'text',
              'text' => 'テスト'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addText('テスト');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushTextMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [
            [
              'type' => 'text',
              'text' => 'テスト'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addText('テスト');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function carouselMessageProvider() {
    return [
      'valid carousel message' => [
        [
          'type' => 'template',
          'altText' => 'alt text for carousel',
          'template' => [
            'type' => 'carousel',
            'columns' => [
              [
                'thumbnailImageUrl' => 'https://www.sampleimage.com/thumbnail.jpg',
                'title' => 'タイトル1',
                'text' => 'サブタイトル1',
                'actions' => [
                  [
                    'type' => 'uri',
                    'label' => 'URLボタン',
                    'uri' => 'https://www.sampleimage.com/sample.jpg'
                  ],
                  [
                    'type' => 'postback',
                    'label' => 'Postbackボタン',
                    'data' => 'key1=value1&key2=value2'
                  ]
                ]
              ]
            ]
          ]
        ],
        [
          [
            'タイトル1', 'サブタイトル1', 'https://www.sampleimage.com/thumbnail.jpg', [
              [
                'title' => 'URLボタン',
                'action' => 'url',
                'url' => 'https://www.sampleimage.com/sample.jpg'
              ],
              [
                'title' => 'Postbackボタン',
                'action' => 'postback',
                'data' => 'key1=value1&key2=value2'
              ]
            ],
          ]
        ]
      ]
    ];
  }

  /**
   * @dataProvider carouselMessageProvider
   */
  public function testReplyCarouselMessage($expectedCarouselArray, $carouselSource) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [ $expectedCarouselArray ]
        ]),
        $this->equalTo(true)
      );

    $bot = new LineBot($this->_curlMock);
    $bot->addCarousel($carouselSource);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider carouselMessageProvider
   */
  public function testPushCarouselMessage($expectedCarouselArray, $carouselSource) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [ $expectedCarouselArray ]
        ]),
        $this->equalTo(true)
      );

    $bot = new LineBot($this->_curlMock);
    $bot->addCarousel($carouselSource);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyImageMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [
            [
              'type' => 'image',
              'originalContentUrl' => 'https://www.sampleimage.com/sample.jpg',
              'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushImageMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [
            [
              'type' => 'image',
              'originalContentUrl' => 'https://www.sampleimage.com/sample.jpg',
              'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyVideoMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [
            [
              'type' => 'video',
              'originalContentUrl' => 'https://www.sampleimage.com/sample.mp4',
              'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushVideoMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [
            [
              'type' => 'video',
              'originalContentUrl' => 'https://www.sampleimage.com/sample.mp4',
              'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyAudioMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [
            [
              'type' => 'audio',
              'originalContentUrl' => 'https://www.sampleimage.com/sample.m4a',
              'duration' => 10000
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushAudioMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [
            [
              'type' => 'audio',
              'originalContentUrl' => 'https://www.sampleimage.com/sample.m4a',
              'duration' => 10000
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyMultiMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [
            [
              'type' => 'text',
              'text' => 'テスト1'
            ],
            [
              'type' => 'text',
              'text' => 'テスト2'
            ],
            [
              'type' => 'text',
              'text' => 'テスト3'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushMultiMessage() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [
            [
              'type' => 'text',
              'text' => 'テスト1'
            ],
            [
              'type' => 'text',
              'text' => 'テスト2'
            ],
            [
              'type' => 'text',
              'text' => 'テスト3'
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function confirmMessageProvider() {
    return [
      'valid confirm message' => [
        [
          'type' => 'template',
          'altText' => 'alt text for confirm',
          'template' => [
            'type' => 'confirm',
            'text' => 'タイトル',
            'actions' => [
              [
                'type' => 'uri',
                'label' => 'URLボタン',
                'uri' => 'https://www.sampleimage.com/sample.jpg'
              ],
              [
                'type' => 'postback',
                'label' => 'Postbackボタン',
                'data' => 'key1=value1&key2=value2'
              ]
            ]
          ]
        ],
        'タイトル',
        [
          [
            'title' => 'URLボタン',
            'action' => 'url',
            'url' => 'https://www.sampleimage.com/sample.jpg'
          ],
          [
            'title' => 'Postbackボタン',
            'action' => 'postback',
            'data' => 'key1=value1&key2=value2'
          ]
        ]
      ]
    ];
  }

  /**
   * @dataProvider confirmMessageProvider
   */
  public function testReplyConfirmMessage($expectedConfirmArray, $title, $confirmSource) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [ $expectedConfirmArray ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addConfirm($title, $confirmSource);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider confirmMessageProvider
   */
  public function testPushConfirmMessage($expectedConfirmArray, $title, $confirmSource) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [ $expectedConfirmArray ]
        ]),
        $this->equalTo(true)
      );
    $bot = new LineBot($this->_curlMock);
    $bot->addConfirm($title, $confirmSource);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testExceptionOccurredWhenReply() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
    $bot = new LineBot($this->_curlMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $res = $bot->replyMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $expected = new \stdClass();
    $expected->code = 1;
    $expected->message = 'Curlでエラーが起きました';
    $this->assertEquals($expected, json_decode($res));
  }

  public function testExceptionOccurredWhenPush() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
    $bot = new LineBot($this->_curlMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $res = $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $expected = new \stdClass();
    $expected->code = 1;
    $expected->message = 'Curlでエラーが起きました';
    $this->assertEquals($expected, json_decode($res));
  }

  public function testTestSignature() {
    $bot = new LineBot($this->_curlMock);
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $x_line_signature = base64_encode(hash_hmac('sha256', $requestBody, 'develop', true));
    $this->assertTrue($bot->testSignature($requestBody, $x_line_signature));
  }

  public function testTestSignatureInvalidSignature() {
    $bot = new LineBot($this->_curlMock);
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $x_line_signature = base64_encode(hash_hmac('sha256', 'invalidString', true));
    $this->assertFalse($bot->testSignature($requestBody, $x_line_signature));
  }

  /**
   * @dataProvider requestBodyProvider
   */
  public function testParseEvents($requestBody, $expectedTypes, $expectedData) {
    $bot = new LineBot($this->_curlMock);
    $events = $bot->parseEvents($requestBody);
    $this->assertContainsOnly(Event::class, $events);
    foreach ($events as $index => $event) {
      $this->assertEquals($expectedTypes[$index], $event->type);
      $this->assertEquals($expectedData[$index], $event->data);
    }
  }

  public function requestBodyProvider() {
    /*
      data case => [
        requestBody
        expectedTypes
        expectedData
      ]
    */
    return [
      'line text message' =>
      [
        '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}',
        [ 'Message.Text' ],
        [
          [ 'text' => 'てすと' ]
        ]
      ],
      'line image message' =>
      [
        '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}]}',
        [ 'Message.File' ],
        [
          []
        ]
      ],
      'line postback' =>
      [
        '{"events":[{"type":"postback","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"postback":{"data":"key1=value1&key2=value2&key3=value3"}}]}',
        [ 'Postback' ],
        [
          [ 'postback' => 'key1=value1&key2=value2&key3=value3' ]
        ]
      ],
      'line image message' =>
      [
        '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"location","id":"2222222222222","title":"location title","address":"どこかの住所","latitude":0,"longitude":0}}]}',
        [ 'Message.Location' ],
        [
          [ 'location' => [ 'lat' => 0, 'long' => 0 ] ]
        ]
      ]
    ];
  }

  public function testGetProfile() {
    $this->_curlMock->expects($this->once())
      ->method('get')
      ->with(
        'https://api.line.me/v2/bot/profile/0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
        ['Authorization' => 'Bearer develop']
      )->willReturn('{"displayName":"Taro Test","userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","pictureUrl":"test.jpg","statusMessage":"ステータスメッセージ"}');
    $bot = new LineBot($this->_curlMock);
    $profile = new \stdClass();
    $profile->displayName = 'Taro Test';
    $profile->userId = '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0';
    $profile->pictureUrl = 'test.jpg';
    $profile->statusMessage = 'ステータスメッセージ';
    $wrapper = [
      'name' => $profile->displayName,
      'profilePic' => $profile->pictureUrl,
      'rawProfile' => $profile
    ];
    $this->assertEquals($wrapper, $bot->getProfile('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
  }

  /**
   * @dataProvider eventFileDataProvider
   */
  public function testGetFiles($requestBody, $expectedFile, $expectedUrl, $expectedBinary) {
    $this->_curlMock->expects($this->once())
      ->method('get')
      ->with(
        $this->equalTo($expectedUrl)
      )->willReturn($expectedBinary);
    $bot = new LineBot($this->_curlMock);
    $events = $bot->parseEvents($requestBody);
    foreach ($events as $event) {
      $this->assertEquals($expectedFile, $bot->getFiles($event));
    }
  }

  public function eventFileDataProvider() {
    return [
      'image' => [
        '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}]}',
        [ '2222222222222.jpg' => 'imageBinary'],
        'https://api.line.me/v2/bot/message/2222222222222/content',
        'imageBinary'
      ]
    ];
  }

}
