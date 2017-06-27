<?php

namespace MessengerFramework\Test;

use MessengerFramework\FacebookBot;
use MessengerFramework\Curl;
use MessengerFramework\Event;
use MessengerFramework\Config;
use PHPUnit\Framework\TestCase;

class FacebookBotTest extends TestCase {

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
    $this->_configMock = $this->getMockBuilder(Config::class)->getMock();
    $this->_configMock->FACEBOOK_APP_SECRET = 'develop';
    $this->_configMock->FACEBOOK_ACCESS_TOKEN = 'develop';
  }

  public function testReplyTextMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [ 'text' => 'テスト' ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushTextMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [ 'text' => 'テスト' ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function genericMessageDataProvider() {
    return [
      'valid generic message' => [
        [
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
        ],
        [
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
        ]
      ]
    ];
  }

  /**
   * @dataProvider genericMessageDataProvider
   */
  public function testReplyGenericMessage($expectedGenericArray, $genericSource) {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => $expectedGenericArray
      ]
    );

    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addGeneric($genericSource);
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider genericMessageDataProvider
   */
  public function testPushGenericMessage($expectedGenericArray, $genericSource) {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => $expectedGenericArray
      ]
    );

    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addGeneric($genericSource);
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyImageMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [
          'attachment' => [
            'type' => 'image',
            'payload' => [ 'url' => 'https://www.sampleimage.com/sample.jpg' ]
          ]
        ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addImage('https://www.sampleimage.com/sample.jpg');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushImageMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [
          'attachment' => [
            'type' => 'image',
            'payload' => [ 'url' => 'https://www.sampleimage.com/sample.jpg' ]
          ]
        ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addImage('https://www.sampleimage.com/sample.jpg');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyVideoMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [
          'attachment' => [
            'type' => 'video',
            'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp4' ]
          ]
        ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addVideo('https://www.sampleimage.com/sample.mp4');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushVideoMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [
          'attachment' => [
            'type' => 'video',
            'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp4' ]
          ]
        ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addVideo('https://www.sampleimage.com/sample.mp4');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyAudioMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [
          'attachment' => [
            'type' => 'audio',
            'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp3' ]
          ]
        ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addAudio('https://www.sampleimage.com/sample.mp3');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushAudioMessage() {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => [
          'attachment' => [
            'type' => 'audio',
            'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp3' ]
          ]
        ]
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addAudio('https://www.sampleimage.com/sample.mp3');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testReplyMultiMessage() {
    $this->_curlMock->expects($this->exactly(3))
      ->method('post')
      ->withConsecutive(
        [
          $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
          $this->equalTo(null),
          $this->equalTo([
            'recipient' => [ 'id' => '1000000000000000' ],
            'message' => [ 'text' => 'テスト1' ]
          ]),
          $this->equalTo(true)
        ],
        [
          $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
          $this->equalTo(null),
          $this->equalTo([
            'recipient' => [ 'id' => '1000000000000000' ],
            'message' => [ 'text' => 'テスト2' ]
          ]),
          $this->equalTo(true)
        ], [
          $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
          $this->equalTo(null),
          $this->equalTo([
            'recipient' => [ 'id' => '1000000000000000' ],
            'message' => [ 'text' => 'テスト3' ]
          ]),
          $this->equalTo(true)
        ]
      );

    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testPushMultiMessage() {
    $this->_curlMock->expects($this->exactly(3))
      ->method('post')
      ->withConsecutive(
        [
          $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
          $this->equalTo(null),
          $this->equalTo([
            'recipient' => [ 'id' => '1000000000000000' ],
            'message' => [ 'text' => 'テスト1' ]
          ]),
          $this->equalTo(true)
        ],
        [
          $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
          $this->equalTo(null),
          $this->equalTo([
            'recipient' => [ 'id' => '1000000000000000' ],
            'message' => [ 'text' => 'テスト2' ]
          ]),
          $this->equalTo(true)
        ], [
          $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
          $this->equalTo(null),
          $this->equalTo([
            'recipient' => [ 'id' => '1000000000000000' ],
            'message' => [ 'text' => 'テスト3' ]
          ]),
          $this->equalTo(true)
        ]
      );

    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function buttonMessageProvider() {
    return [
      'valid button message' => [
        [
          'attachment' => [
            'type' => 'template',
            'payload' => [
              'template_type' => 'button',
              'text' => 'タイトル',
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
   * @dataProvider buttonMessageProvider
   */
  public function testReplyButtonMessage($expectedButtonArray, $titleSource, $buttonSource) {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => $expectedButtonArray
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addButton($titleSource, $buttonSource);
    $bot->replyMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider buttonMessageProvider
   */
  public function testPushButtonMessage($expectedButtonArray, $titleSource, $buttonSource) {
    $this->_setCurlMockForSingleMessage(
      /* expected payload */
      [
        'recipient' => [ 'id' => '1000000000000000' ],
        'message' => $expectedButtonArray
      ]
    );
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addButton($titleSource, $buttonSource);
    $bot->pushMessage('1000000000000000');
    $this->addToAssertionCount(1);
  }

  public function testExceptionOccurredWhenReply() {
    $this->_curlMock->expects($this->exactly(3))
      ->method('post')
      ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $res = $bot->replyMessage('1000000000000000');
    $expected = new \stdClass();
    $expected->code = 1;
    $expected->message = 'Curlでエラーが起きました';
    $this->assertEquals([$expected, $expected, $expected], json_decode($res));
  }

  public function testExceptionOccurredWhenPush() {
    $this->_curlMock->expects($this->exactly(3))
      ->method('post')
      ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $bot->addText('テスト1');
    $bot->addText('テスト2');
    $bot->addText('テスト3');
    $res = $bot->pushMessage('1000000000000000');
    $expected = new \stdClass();
    $expected->code = 1;
    $expected->message = 'Curlでエラーが起きました';
    $this->assertEquals([$expected, $expected, $expected], json_decode($res));
  }

  /**
   * @dataProvider requestBodyProvider
   */
  public function testParseEvents($requestBody, $expectedTypes, $expectedData) {
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
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
      'facebook text message' =>
      [
        '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}',
        [ 'Message.Text' ],
        [
          [ 'text' => 'てすとてすと' ]
        ]
      ],
      'facebook image message' =>
      [
        '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"https://www.sampleimage.com/sample.jpg"}}]}}]}]}',
        [ 'Message.File' ],
        [
          null
        ]
      ],
      'facebook postback' =>
      [
        '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"sender":{"id":"1000000000000000"},"postback":{"payload":"text=Postback1\u0025E3\u002582\u002592\u0025E6\u00258A\u0025BC\u0025E3\u002581\u002597\u0025E3\u002581\u0025BE\u0025E3\u002581\u002597\u0025E3\u002581\u00259F"}}]}]}',
        [ 'Postback' ],
        [
          [ 'postback' =>\http_build_query([ 'text' => 'Postback1を押しました' ]) ]
        ]
      ],
      'facebook location message'=>
      [
         '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"location","payload":{"coordinates":{"lat":0,"long":0}}}]}}]}]}',
         [ 'Message.Location' ],
         [
           [ 'location' => [ 'lat' => 0, 'long' => 0] ]
         ]
      ]
    ];
  }

  public function testTestSignature() {
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $x_hub_signature = 'sha1=' . hash_hmac('sha1', $jsonString, 'develop');
    $this->assertTrue($bot->testSignature($jsonString, $x_hub_signature));
  }

  public function testTestSignatureInvalidAlgorithm() {
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $x_hub_signature = 'hoge=' . hash_hmac('sha1', $jsonString, 'develop');
    $this->assertFalse($bot->testSignature($jsonString, $x_hub_signature));
  }

  public function testTestSignatureInvalidSignature() {
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
    $x_hub_signature = 'sha1=' . 'invalidString';
    $this->assertFalse($bot->testSignature($jsonString, $x_hub_signature));
  }

  public function testGetProfile() {
    $this->_curlMock->expects($this->once())
      ->method('get')
      ->with(
        'https://graph.facebook.com/v2.6/1000000000000000?access_token=develop'
      )->willReturn('{"first_name": "Taro","last_name": "Test","profile_pic": "test.jpg","locale": "ja_JP","timezone": 9,"gender": "male"}');
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $profile = new \StdClass();
    $profile->first_name = 'Taro';
    $profile->last_name = 'Test';
    $profile->profile_pic = 'test.jpg';
    $profile->locale = 'ja_JP';
    $profile->timezone = 9;
    $profile->gender = 'male';
    $wrapper = new \stdClass();
    $wrapper->name = $profile->first_name . ' ' . $profile->last_name;
    $wrapper->profilePic = $profile->profile_pic;
    $wrapper->rawProfile = $profile;
    $this->assertEquals($wrapper, $bot->getProfile('1000000000000000'));
  }

  /**
   * @dataProvider fileDataProvider
   */
  public function testGetFiles($requestBody, $expectedFiles, $expectedUrl, $expectedBinary) {
    $this->_curlMock->expects($this->once())
      ->method('get')
      ->with(
        $this->equalTo($expectedUrl)
      )->willReturn($expectedBinary);
    $bot = new FacebookBot($this->_curlMock, $this->_configMock);
    $events = $bot->parseEvents($requestBody);
    foreach ($events as $event) {
      $this->assertEquals($expectedFiles, $bot->getFiles($event));
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

  private function _setCurlMockForSingleMessage($payload) {
    $this->_curlMock->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('https://graph.facebook.com/v2.6/me/messages?access_token=develop'),
        $this->equalTo(null),
        $this->equalTo($payload),
        $this->equalTo(true)
      );
    return;
  }

}
