<?php

namespace Genies\MessengerFramework\Test;

use \Genies\MessengerFramework\FacebookBot;
use \Genies\MessengerFramework\Curl;
use \Genies\MessengerFramework\Event;
use \Genies\MessengerFramework\Config;
use \PHPUnit\Framework\TestCase;

// TODO : 画像や動画、音声を送信するときのattachment_idの再利用をテストする
class FacebookBotTest extends TestCase
{

    private $_curlMock;

    private $_configMock;

    public function setUp()
    {
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
        $this->_configMock = new Config('facebook', 'develop', 'develop');
    }

    public function testReplyTextMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [ 'text' => 'テスト' ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト');
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

    public function testPushTextMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [ 'text' => 'テスト' ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト');
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testReplyTextMessageFail()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [ 'text' => '間違った何か' ]
            ],
            $this->errorResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('間違った何か');
        $this->assertFalse($bot->replyMessage('1000000000000000'));
    }

    public function genericMessageDataProvider()
    {
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
        ]];
    }

  /**
   * @dataProvider genericMessageDataProvider
   */
    public function testReplyGenericMessage($expectedGenericArray, $genericSource)
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => $expectedGenericArray
            ],
            $this->successResponse
        );

        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addGeneric($genericSource);
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

  /**
   * @dataProvider genericMessageDataProvider
   */
    public function testPushGenericMessage($expectedGenericArray, $genericSource)
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => $expectedGenericArray
            ],
            $this->successResponse
        );

        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addGeneric($genericSource);
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testReplyImageMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'image',
                        'payload' => [ 'url' => 'https://www.sampleimage.com/sample.jpg', 'is_reusable' => true ]
                    ]
                ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addImage('https://www.sampleimage.com/sample.jpg');
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

    public function testPushImageMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'image',
                        'payload' => [ 'url' => 'https://www.sampleimage.com/sample.jpg', 'is_reusable' => true ]
                    ]
                ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addImage('https://www.sampleimage.com/sample.jpg');
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testReplyVideoMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'video',
                        'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp4', 'is_reusable' => true ]
                    ]
                ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addVideo('https://www.sampleimage.com/sample.mp4');
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

    public function testPushVideoMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'video',
                        'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp4', 'is_reusable' => true ]
                    ]
                ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addVideo('https://www.sampleimage.com/sample.mp4');
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testReplyAudioMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'audio',
                        'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp3', 'is_reusable' => true ]
                    ]
                ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addAudio('https://www.sampleimage.com/sample.mp3');
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

    public function testPushAudioMessage()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'audio',
                        'payload' => [ 'url' => 'https://www.sampleimage.com/sample.mp3', 'is_reusable' => true ]
                    ]
                ]
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addAudio('https://www.sampleimage.com/sample.mp3');
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testReplyMultiMessage()
    {
        $this->_curlMock->expects($this->exactly(3))
        ->method('post')
        ->withConsecutive(
            [
                $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
                $this->equalTo([]),
                $this->equalTo([
                    'recipient' => [ 'id' => '1000000000000000' ],
                    'message' => [ 'text' => 'テスト1' ]
                ]),
                $this->equalTo(true)
            ],
            [
                $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
                $this->equalTo([]),
                $this->equalTo([
                    'recipient' => [ 'id' => '1000000000000000' ],
                    'message' => [ 'text' => 'テスト2' ]
                ]),
                $this->equalTo(true)
            ],
            [
                $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
                $this->equalTo([]),
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
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

    public function testPushMultiMessage()
    {
        $this->_curlMock->expects($this->exactly(3))
        ->method('post')
        ->withConsecutive(
            [
                $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
                $this->equalTo([]),
                $this->equalTo([
                    'recipient' => [ 'id' => '1000000000000000' ],
                    'message' => [ 'text' => 'テスト1' ]
                ]),
                $this->equalTo(true)
            ],
            [
                $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
                $this->equalTo([]),
                $this->equalTo([
                    'recipient' => [ 'id' => '1000000000000000' ],
                    'message' => [ 'text' => 'テスト2' ]
                ]),
                $this->equalTo(true)
            ],
            [
                $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
                $this->equalTo([]),
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
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function buttonMessageProvider()
    {
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
        ]];
    }

  /**
   * @dataProvider buttonMessageProvider
   */
    public function testReplyButtonMessage($expectedButtonArray, $titleSource, $buttonSource)
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => $expectedButtonArray
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addButton($titleSource, $buttonSource);
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

  /**
   * @dataProvider buttonMessageProvider
   */
    public function testPushButtonMessage($expectedButtonArray, $titleSource, $buttonSource)
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => $expectedButtonArray
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addButton($titleSource, $buttonSource);
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testReplyButtonMessageWithButtonOption()
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => [
                    'attachment' => [
                        'type' => 'template',
                        'payload' => [
                            'template_type' => 'button',
                            'text' => 'タイトル',
                            'buttons' => [
                                [
                                    'type' => 'web_url',
                                    'url' => 'https://www.sampleimage.com/sample.jpg',
                                    'title' => 'URLボタン',
                                    'webview_height_ratio' => 'compact'
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
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addButton('タイトル', [
            [
                'title' => 'URLボタン',
                'action' => 'url',
                'url' => 'https://www.sampleimage.com/sample.jpg',
                'webview_height_ratio' => 'compact'
            ],
            [
                'title' => 'Postbackボタン',
                'action' => 'postback',
                'data' => 'key1=value1&key2=value2'
            ]
        ]);
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

    public function rawMessageDataProvider()
    {
        return [
            'text message' => [
                [
                    'text' => 'テスト1'
                ]
            ],
            'generic message' => [
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
                ]
            ]
        ];
    }

  /**
   * @dataProvider rawMessageDataProvider
   */
    public function testReplyRawMessage($rawSource)
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => $rawSource
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addrawMessage($rawSource);
        $this->assertTrue($bot->replyMessage('1000000000000000'));
    }

  /**
   * @dataProvider rawMessageDataProvider
   */
    public function testPushRawMessage($rawSource)
    {
        $this->_setCurlMockForSingleMessage(
            /* expected payload */
            [
                'recipient' => [ 'id' => '1000000000000000' ],
                'message' => $rawSource
            ],
            $this->successResponse
        );
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addrawMessage($rawSource);
        $this->assertTrue($bot->pushMessage('1000000000000000'));
    }

    public function testExceptionOccurredWhenReply()
    {
        $this->_curlMock->expects($this->exactly(1))
        ->method('post')
        ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト1');
        $bot->addText('テスト2');
        $bot->addText('テスト3');
        try {
            $res = $bot->replyMessage('1000000000000000');
            $this->fail();
        } catch (\RuntimeException $ex) {
            $this->assertEquals('Curlでエラーが起きました', $ex->getMessage());
            $this->addToAssertionCount(1);
        }
    }

    public function testExceptionOccurredWhenPush()
    {
        $this->_curlMock->expects($this->exactly(1))
        ->method('post')
        ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト1');
        $bot->addText('テスト2');
        $bot->addText('テスト3');
        try {
            $res = $bot->pushMessage('1000000000000000');
            $this->fail();
        } catch (\RuntimeException $ex) {
            $this->assertEquals('Curlでエラーが起きました', $ex->getMessage());
            $this->addToAssertionCount(1);
        }
    }

  /**
   * @dataProvider requestBodyProvider
   */
    public function testParseEvents($requestBody, $expectedTypes, $expectedData)
    {
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $events = $bot->parseEvents($requestBody);
        $this->assertContainsOnly(Event::class, $events);
        foreach ($events as $index => $event) {
            $this->assertEquals($expectedTypes[$index], $event->type);
            $this->assertEquals($expectedData[$index], $event->data);
        }
    }

    public function requestBodyProvider()
    {
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

    public function testTestSignature()
    {
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
        $x_hub_signature = 'sha1=' . hash_hmac('sha1', $jsonString, 'develop');
        $this->assertTrue($bot->testSignature($jsonString, $x_hub_signature));
    }

    public function testTestSignatureInvalidAlgorithm()
    {
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
        $x_hub_signature = 'hoge=' . hash_hmac('sha1', $jsonString, 'develop');
        $this->assertFalse($bot->testSignature($jsonString, $x_hub_signature));
    }

    public function testTestSignatureInvalidSignature()
    {
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $jsonString = '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"text":"\u3066\u3059\u3068\u3066\u3059\u3068"}}]}]}';
        $x_hub_signature = 'sha1=' . 'invalidString';
        $this->assertFalse($bot->testSignature($jsonString, $x_hub_signature));
    }

    public function testGetProfile()
    {
        $this->_curlMock->expects($this->once())
        ->method('get')
        ->with(
            'https://graph.facebook.com/v2.10/1000000000000000?access_token=develop'
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
    public function testGetFiles($requestBody, $expectedFiles, $expectedUrl, $expectedBinary)
    {
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

    public function fileDataProvider()
    {
        return [
        'image' => [
                '{"object":"page","entry":[{"id":"000000000000000","time":1495206000000,"messaging":[{"sender":{"id":"1000000000000000"},"recipient":{"id":"200000000000000"},"timestamp":1495207800000,"message":{"mid":"mid.$cAADj4thus55iSabc123DEFghi45j","seq":1000,"attachments":[{"type":"image","payload":{"url":"https://www.sampleimage.com/sample.jpg"}}]}}]}]}',
                [ 'sample.jpg' => 'imageBinary'],
                'https://www.sampleimage.com/sample.jpg',
                'imageBinary'
            ]
        ];
    }

    public function testGetPayloads()
    {
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('message1');
        $bot->addText('message2');
        $expected = [
            [ 'text' => 'message1' ],
            [ 'text' => 'message2' ]
        ];
        $this->assertEquals($expected, $bot->getMessagePayloads());
    }

    public function testClearMessages()
    {
        $bot = new FacebookBot($this->_curlMock, $this->_configMock);
        $bot->addText('message1');
        $bot->addText('message2');
        $bot->clearMessages();
        $this->assertEquals([], $bot->getMessagePayloads());
    }

    private function _setCurlMockForSingleMessage($payload, $retVal)
    {
        $this->_curlMock->expects($this->once())
        ->method('post')
        ->with(
            $this->equalTo('https://graph.facebook.com/v2.10/me/messages?access_token=develop'),
            $this->equalTo([]),
            $this->equalTo($payload),
            $this->equalTo(true)
        )->willReturn($retVal);
        return;
    }

    private $successResponse = '{"recipient_id":"1000000000000000","message_id":"mid.$cAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"}';

    private $errorResponse = '{"error":{"message":"error message"}}';
}
