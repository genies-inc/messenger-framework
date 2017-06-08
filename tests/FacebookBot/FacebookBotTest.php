<?php

namespace MessengerFramework\Test;

use MessengerFramework\FacebookBot\FacebookBot;
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
    $bot->setText('テスト');
    $bot->replyMessage('1000000000000000');
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
    $bot->setGeneric([
      [
        'タイトル1', 'サブタイトル1', null, [
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
    ]);
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyImageMessage() {
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
              'type' => 'image',
              'payload' => [
                'url' => 'https://www.sampleimage.com/sample.jpg'
              ]
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $bot->setImage('https://www.sampleimage.com/sample.jpg');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyVideoMessage() {
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
              'type' => 'video',
              'payload' => [
                'url' => 'https://www.sampleimage.com/sample.mp4'
              ]
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $bot->setVideo('https://www.sampleimage.com/sample.mp4');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyAudioMessage() {
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
              'type' => 'audio',
              'payload' => [
                'url' => 'https://www.sampleimage.com/sample.mp3'
              ]
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $bot->setAudio('https://www.sampleimage.com/sample.mp3');
    $bot->replyMessage('1000000000000000');
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
    $bot->setText('テスト');
    $bot->pushMessage('1000000000000000');
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
    $bot->setGeneric([
      [
        'タイトル1', 'サブタイトル1', null, [
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
    ]);
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushImageMessage() {
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
              'type' => 'image',
              'payload' => [
                'url' => 'https://www.sampleimage.com/sample.jpg'
              ]
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $bot->setImage('https://www.sampleimage.com/sample.jpg');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushVideoMessage() {
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
              'type' => 'video',
              'payload' => [
                'url' => 'https://www.sampleimage.com/sample.mp4'
              ]
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $bot->setVideo('https://www.sampleimage.com/sample.mp4');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushAudioMessage() {
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
              'type' => 'audio',
              'payload' => [
                'url' => 'https://www.sampleimage.com/sample.mp3'
              ]
            ]
          ]
        ]),
        $this->equalTo(true)
      );
    $bot = new FacebookBot($this->curlMock);
    $bot->setAudio('https://www.sampleimage.com/sample.mp3');
    $bot->pushMessage('1000000000000000');
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

  public function testTestSignatureInvalidAlgorithm() {
    $bot = new FacebookBot($this->curlMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $x_hub_signature = 'hoge=' . hash_hmac('sha1', $jsonString, 'develop');
    $this->assertFalse($bot->testSignature($jsonString, $x_hub_signature));
  }

  public function testTestSignatureInvalidSignature() {
    $bot = new FacebookBot($this->curlMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $x_hub_signature = 'sha1=' . 'invalidString';
    $this->assertFalse($bot->testSignature($jsonString, $x_hub_signature));
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

  /**
   * @dataProvider fileDataProvider
   */
  public function testGetFiles($requestBody, $expectedFiles, $expectedUrl, $expectedBinary) {
    $this->curlMock->expects($this->once())
      ->method('get')
      ->with(
        $this->equalTo($expectedUrl)
      )->willReturn($expectedBinary);
    $bot = new FacebookBot($this->curlMock);
    $events = $bot->parseEvents($requestBody);
    foreach ($events->entry as $entry) {
      foreach ($entry->messaging as $messaging) {
        $this->assertEquals($expectedFiles, $bot->getFiles($messaging));
      }
    }
  }

  public function fileDataProvider() {
    return [
      'image' => [
        '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"https://www.sampleimage.com/sample.jpg"}}]}}]}]}',
        [ 'sample.jpg' => 'imageBinary'],
        'https://www.sampleimage.com/sample.jpg',
        'imageBinary'
      ]
    ];
  }

}
