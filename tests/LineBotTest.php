<?php

namespace MessengerFramework\Test;

use MessengerFramework\LineBot;
use MessengerFramework\Curl;
use MessengerFramework\Event;
use MessengerFramework\Config;
use PHPUnit\Framework\TestCase;

class LineTest extends TestCase {

  private $_curlMock;

  private $_configMock;

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
    $this->_configMock = new Config('line', 'develop', 'develop');
  }

  public function testReplyTextMessage() {
    $this->_setCurlMockForReply(
      /* expected messages */
      [
        [
          'type' => 'text',
          'text' => 'テスト'
        ]
      ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushTextMessage() {
    $this->_setCurlMockForPush(
      /* expected messages */
      [
        [
          'type' => 'text',
          'text' => 'テスト'
        ]
      ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function carouselMessageProvider() {
    return [
      'valid carousel message' => [
        [
          'type' => 'template',
          'altText' => 'メニューが届いています(閲覧可能端末から見て下さい)',
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
    $this->_setCurlMockForReply(
      /* expected messages */
      [ $expectedCarouselArray ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );

    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addCarousel($carouselSource);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider carouselMessageProvider
   */
  public function testPushCarouselMessage($expectedCarouselArray, $carouselSource) {
    $this->_setCurlMockForPush(
      /* expected messages */
      [ $expectedCarouselArray ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );

    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addCarousel($carouselSource);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyImageMessage() {
    $this->_setCurlMockForReply(
      /* expected messages */
      [
        [
          'type' => 'image',
          'originalContentUrl' => 'https://www.sampleimage.com/sample.jpg',
          'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
        ]
      ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushImageMessage() {
    $this->_setCurlMockForPush(
      /* expected messages */
      [
        [
          'type' => 'image',
          'originalContentUrl' => 'https://www.sampleimage.com/sample.jpg',
          'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
        ]
      ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyVideoMessage() {
    $this->_setCurlMockForReply(
      /* expected messages */
      [
        [
          'type' => 'video',
          'originalContentUrl' => 'https://www.sampleimage.com/sample.mp4',
          'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
        ]
      ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushVideoMessage() {
    $this->_setCurlMockForPush(
      /* expected messages */
      [
        [
          'type' => 'video',
          'originalContentUrl' => 'https://www.sampleimage.com/sample.mp4',
          'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
        ]
      ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyAudioMessage() {
    $this->_setCurlMockForReply(
      /* expected messages */
      [
        [
          'type' => 'audio',
          'originalContentUrl' => 'https://www.sampleimage.com/sample.m4a',
          'duration' => 10000
        ]
      ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushAudioMessage() {
    $this->_setCurlMockForPush(
      /* expected messages */
      [
        [
          'type' => 'audio',
          'originalContentUrl' => 'https://www.sampleimage.com/sample.m4a',
          'duration' => 10000
        ]
      ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testReplyMultiMessage() {
    $this->_setCurlMockForReply(
      /* expected messages */
      [
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
      ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  public function testPushMultiMessage() {
    $this->_setCurlMockForPush(
      /* expected messages */
      [
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
      ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
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
          'altText' => '確認メッセージが届いています(閲覧可能端末から見て下さい)',
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
    $this->_setCurlMockForReply(
      /* expected messages */
      [ $expectedConfirmArray ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addConfirm($title, $confirmSource);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider confirmMessageProvider
   */
  public function testPushConfirmMessage($expectedConfirmArray, $title, $confirmSource) {
    $this->_setCurlMockForPush(
      /* expected messages */
      [ $expectedConfirmArray ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addConfirm($title, $confirmSource);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function rawMessageDataProvider() {
    return [
      'text message' => [
        [
          'type' => 'text',
          'text' => 'テスト1'
        ]
      ],
      'carousel message' => [
        [
          'type' => 'template',
          'altText' => 'メニューが届いています(閲覧可能端末から見て下さい)',
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
        ]
      ]
    ];
  }

  /**
   * @dataProvider rawMessageDataProvider
   */
  public function testReplyRawMessage($rawSource) {
    $this->_setCurlMockForReply(
      /* expected messages */
      [ $rawSource ],
      '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addRawMessage($rawSource);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider rawMessageDataProvider
   */
  public function testPushRawMessage($rawSource) {
    $this->_setCurlMockForPush(
      /* expected messages */
      [ $rawSource ],
      '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
    );
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $bot->addRawMessage($rawSource);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
    $this->addToAssertionCount(1);
  }

  public function testExceptionOccurredWhenReply() {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
    $bot = new LineBot($this->_curlMock, $this->_configMock);
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
    $bot = new LineBot($this->_curlMock, $this->_configMock);
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
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $x_line_signature = base64_encode(hash_hmac('sha256', $requestBody, 'develop', true));
    $this->assertTrue($bot->testSignature($requestBody, $x_line_signature));
  }

  public function testTestSignatureInvalidSignature() {
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $x_line_signature = base64_encode(hash_hmac('sha256', 'invalidString', true));
    $this->assertFalse($bot->testSignature($requestBody, $x_line_signature));
  }

  /**
   * @dataProvider requestBodyProvider
   */
  public function testParseEvents($requestBody, $expectedTypes, $expectedData) {
    $bot = new LineBot($this->_curlMock, $this->_configMock);
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
          null
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
    $bot = new LineBot($this->_curlMock, $this->_configMock);
    $profile = new \stdClass();
    $profile->displayName = 'Taro Test';
    $profile->userId = '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0';
    $profile->pictureUrl = 'test.jpg';
    $profile->statusMessage = 'ステータスメッセージ';

    $wrapper = new \stdClass();
    $wrapper->name = $profile->displayName;
    $wrapper->profilePic = $profile->pictureUrl;
    $wrapper->rawProfile = $profile;
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
    $bot = new LineBot($this->_curlMock, $this->_configMock);
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

  private function _setCurlMockForReply($messages, $replyToken) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'replyToken' => $replyToken,
          'messages' => $messages
        ]),
        $this->equalTo(true)
      );
  }

  private function _setCurlMockForPush($messages, $recipientId) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([ 'Authorization' => 'Bearer develop' ]),
        $this->equalTo([
          'to' => $recipientId,
          'messages' => $messages
        ]),
        $this->equalTo(true)
      );
  }

}
