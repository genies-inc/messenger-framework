<?php

namespace Framework\Test;

use Framework\LineBot\LineBot;
use Framework\LineBot\TextMessageBuilder;
use Framework\LineBot\CarouselMessageBuilder;
use Framework\LineBot\FileMessageBuilder;
use Framework\LineBot\MultiMessageBuilder;
use Framework\HttpClient\Curl;
use PHPUnit\Framework\TestCase;

class LineTest extends TestCase {

  private $curlMock;

  public function setUp() {
    $this->curlMock = null;
    $this->curlMock = $this->getMockBuilder(Curl::class)
      ->setMethods(['post'])
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
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
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
    $bot = new LineBot($this->curlMock);
    $builder = new TextMessageBuilder('テスト');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f', $builder);
    $this->addToAssertionCount(1);
  }

  public function testReplyCarouselMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
        $this->equalTo([
          'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
          'messages' => [
            [
              'type' => 'template',
              'altText' => '',
              'template' => [
                'type' => 'carousel',
                'columns' => [
                  [
                    'thumbnailImageUrl' => '',
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
        ]),
        $this->equalTo(true)
      );

    $bot = new LineBot($this->curlMock);
    $builder = new CarouselMessageBuilder([
      [
        'タイトル1', 'サブタイトル1', null, [
          'title' => 'URLボタン',
          'action' => 'url',
          'url' => 'https://www.sampleimage.com/sample.jpg'
        ], [
          'title' => 'Postbackボタン',
          'action' => 'postback',
          'data' => 'key1=value1&key2=value2'
        ]
      ]
    ]);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f', $builder);
    $this->addToAssertionCount(1);

  }

  public function testReplyFileMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
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
    $bot = new LineBot($this->curlMock);
    $builder = new FileMessageBuilder('image', 'https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f', $builder);
    $this->addToAssertionCount(1);
  }

  public function testReplyMultiMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/reply'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
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
    $bot = new LineBot($this->curlMock);
    $builder1 = new TextMessageBuilder('テスト1');
    $builder2 = new TextMessageBuilder('テスト2');
    $builder3 = new TextMessageBuilder('テスト3');
    $multiBuilder = new MultiMessageBuilder();
    $multiBuilder->add($builder1);
    $multiBuilder->add($builder2);
    $multiBuilder->add($builder3);
    $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f', $multiBuilder);
    $this->addToAssertionCount(1);
  }

  public function testPushTextMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
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
    $bot = new LineBot($this->curlMock);
    $builder = new TextMessageBuilder('テスト');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0', $builder);
    $this->addToAssertionCount(1);
  }

  public function testPushCarouselMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
        $this->equalTo([
          'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
          'messages' => [
            [
              'type' => 'template',
              'altText' => '',
              'template' => [
                'type' => 'carousel',
                'columns' => [
                  [
                    'thumbnailImageUrl' => '',
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
        ]),
        $this->equalTo(true)
      );

    $bot = new LineBot($this->curlMock);
    $builder = new CarouselMessageBuilder([
      [
        'タイトル1', 'サブタイトル1', null, [
          'title' => 'URLボタン',
          'action' => 'url',
          'url' => 'https://www.sampleimage.com/sample.jpg'
        ], [
          'title' => 'Postbackボタン',
          'action' => 'postback',
          'data' => 'key1=value1&key2=value2'
        ]
      ]
    ]);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0', $builder);
    $this->addToAssertionCount(1);
  }

  public function testPushFileMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
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
    $bot = new LineBot($this->curlMock);
    $builder = new FileMessageBuilder('image', 'https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0', $builder);
    $this->addToAssertionCount(1);
  }

  public function testPushMultiMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://api.line.me/v2/bot/message/push'),
        $this->equalTo([
          'Authorization' => 'Bearer develop'
        ]),
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
    $bot = new LineBot($this->curlMock);
    $builder1 = new TextMessageBuilder('テスト1');
    $builder2 = new TextMessageBuilder('テスト2');
    $builder3 = new TextMessageBuilder('テスト3');
    $multiBuilder = new MultiMessageBuilder();
    $multiBuilder->add($builder1);
    $multiBuilder->add($builder2);
    $multiBuilder->add($builder3);
    $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0', $multiBuilder);
    $this->addToAssertionCount(1);
  }

  public function testTestSignature() {
    $bot = new LineBot($this->curlMock);
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $x_line_signature = base64_encode(hash_hmac('sha256', $requestBody, 'develop', true));
    $this->assertTrue($bot->testSignature($requestBody, $x_line_signature));
  }

  public function testParseEvents() {
    $bot = new LineBot($this->curlMock);
    $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
    $this->assertEquals(\json_decode($requestBody), $bot->parseEvents($requestBody));
  }

}
