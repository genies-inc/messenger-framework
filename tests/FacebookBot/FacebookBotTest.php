<?php

namespace MessengerFramework\Test;

use MessengerFramework\FacebookBot\FacebookBot;
use MessengerFramework\FacebookBot\TextMessageBuilder;
use MessengerFramework\FacebookBot\GenericMessageBuilder;
use MessengerFramework\FacebookBot\AttachmentMessageBuilder;
use MessengerFramework\HttpClient\Curl;
use PHPUnit\Framework\TestCase;

class FacebookBotTest extends TestCase {

  private $curlMock;

  public function setUp() {
    $this->curlMock = null;
    $this->curlMock = $this->getMockBuilder(Curl::class)
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
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo([
          'recipient' => [
            'id' => '1000000000000000'
          ],
          'message' => [
            'text' => 'テスト'
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $builder = new TextMessageBuilder('テスト');
    $bot->replyMessage('1000000000000000', $builder);
    $this->addToAssertionCount(1);
  }

  public function testReplyGenericMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo([
          'recipient' => [
            'id' => '1000000000000000'
          ],
          'message' => [
            'attachment' => [
              'type' => 'template',
              'payload' => [
                'template_type' => 'generic',
                'elements' => [
                    [
                    'title' => 'タイトル1',
                    'subtitle' => 'サブタイトル1',
                    'buttons' => [
                      [
                        'type' => 'web_url',
                        'url' => 'https://www.sampleimage.com/sample.jpg',
                        'title' => 'URLボタン'
                      ],
                      [
                        'type' => 'postback',
                        'title' => 'Postbackボタン',
                        'payload' => 'key1=value1&key2=value2'
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

    $bot = new FacebookBot($this->curlMock);
    $builder = new GenericMessageBuilder([
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
    $bot->replyMessage('1000000000000000', $builder);
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider attachmentDataProvider
   */
  public function testReplyAttachmentMessage($type, $source, $expectedMessage) {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo([
          'recipient' => [
            'id' => '1000000000000000'
          ],
          'message' => $expectedMessage
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $builder = new AttachmentMessageBuilder($type, $source);
    $bot->replyMessage('1000000000000000', $builder);
    $this->addToAssertionCount(1);
  }

  public function testPushTextMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo([
          'recipient' => [
            'id' => '1000000000000000'
          ],
          'message' => [
            'text' => 'テスト'
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $builder = new TextMessageBuilder('テスト');
    $bot->pushMessage('1000000000000000', $builder);
    $this->addToAssertionCount(1);
  }

  public function testPushGenericMessage() {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo([
          'recipient' => [
            'id' => '1000000000000000'
          ],
          'message' => [
            'attachment' => [
              'type' => 'template',
              'payload' => [
                'template_type' => 'generic',
                'elements' => [
                    [
                    'title' => 'タイトル1',
                    'subtitle' => 'サブタイトル1',
                    'buttons' => [
                      [
                        'type' => 'web_url',
                        'url' => 'https://www.sampleimage.com/sample.jpg',
                        'title' => 'URLボタン'
                      ],
                      [
                        'type' => 'postback',
                        'title' => 'Postbackボタン',
                        'payload' => 'key1=value1&key2=value2'
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

    $bot = new FacebookBot($this->curlMock);
    $builder = new GenericMessageBuilder([
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
    $bot->pushMessage('1000000000000000', $builder);
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider attachmentDataProvider
   */
  public function testPushAttachmentMessage($type, $source, $expectedMessage) {
    $this->curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo([
          'recipient' => [
            'id' => '1000000000000000'
          ],
          'message' => $expectedMessage
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $builder = new AttachmentMessageBuilder($type, $source);
    $bot->pushMessage('1000000000000000', $builder);
    $this->addToAssertionCount(1);
  }

  public function testParseEvents() {
    $bot = new FacebookBot($this->curlMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $this->assertEquals(\json_decode($jsonString), $bot->parseEvents($jsonString));
  }

  public function testTestSignature() {
    $bot = new FacebookBot($this->curlMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $x_hub_signature = 'sha1=' . hash_hmac('sha1', $jsonString, 'develop');
    $this->assertTrue($bot->testSignature($jsonString, $x_hub_signature));
  }

  public function testGetProfile() {
    $this->curlMock->expects($this->once())
      ->method('get')
      ->with(
        'https://graph.facebook.com/v2.6/1000000000000000?access_token=develop'
      )->willReturn('{"first_name": "Taro","last_name": "Test","profile_pic": "test.jpg","locale": "ja_JP","timezone": 9,"gender": "male"}');
    $bot = new FacebookBot($this->curlMock);
    $profile = new \StdClass();
    $profile->first_name = 'Taro';
    $profile->last_name = 'Test';
    $profile->profile_pic = 'test.jpg';
    $profile->locale = 'ja_JP';
    $profile->timezone = 9;
    $profile->gender = 'male';
    $this->assertEquals($profile, $bot->getProfile('1000000000000000'));
  }

  public function attachmentDataProvider() {
    return [
      'image' => [
        'image', 'https://www.sampleimage.com/sample.jpg',
        [
          'attachment' => [
            'type' => 'image',
            'payload' => [
              'url' => 'https://www.sampleimage.com/sample.jpg'
            ]
          ]
        ]
      ],
      'video' => [
        'video', 'https://www.sampleimage.com/sample.mp4',
        [
          'attachment' => [
            'type' => 'video',
            'payload' => [
              'url' => 'https://www.sampleimage.com/sample.mp4'
            ]
          ]
        ]
      ],
      'audio' => [
        'audio', 'https://www.sampleimage.com/sample.mp3',
        [
          'attachment' => [
            'type' => 'audio',
            'payload' => [
              'url' => 'https://www.sampleimage.com/sample.mp3'
            ]
          ]
        ]
      ]
    ];
  }

}
