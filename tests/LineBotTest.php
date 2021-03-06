<?php

namespace Genies\MessengerFramework\Test;

use \Genies\MessengerFramework\LineBot;
use \Genies\MessengerFramework\Curl;
use \Genies\MessengerFramework\Event;
use \Genies\MessengerFramework\Config;
use \PHPUnit\Framework\TestCase;

// TODO : テンプレート系メッセージ(CarouselやConfirm)で代替テキストを設定した場合もテストする
class LineTest extends TestCase
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
        $this->_configMock = new Config('line', 'develop', 'develop');
    }

    public function testReplyTextMessage()
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [
                [
                    'type' => 'text',
                    'text' => 'テスト'
                ]
            ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト');
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function testPushTextMessage()
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [
                [
                    'type' => 'text',
                    'text' => 'テスト'
                ]
            ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト');
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testReplyTextMessageFail()
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [
                [
                    'type' => 'text',
                    'text' => '間違った何か'
                ]
            ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->errorResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('間違った何か');
        $this->assertFalse($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function carouselMessageProvider()
    {
        return [
            'valid carousel message' => [
                [
                    'type' => 'template',
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
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
                        'タイトル1', 'サブタイトル1', 'https://www.sampleimage.com/thumbnail.jpg',
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
                        ],
                    ]
                ]
            ]
        ];
    }

  /**
   * @dataProvider carouselMessageProvider
   */
    public function testReplyCarouselMessage($expectedCarouselArray, $carouselSource)
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [ $expectedCarouselArray ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );

        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addCarousel($carouselSource);
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

  /**
   * @dataProvider carouselMessageProvider
   */
    public function testPushCarouselMessage($expectedCarouselArray, $carouselSource)
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [ $expectedCarouselArray ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );

        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addCarousel($carouselSource);
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testReplyImageMessage()
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [
                [
                    'type' => 'image',
                    'originalContentUrl' => 'https://www.sampleimage.com/sample.jpg',
                    'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
                ]
            ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function testPushImageMessage()
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [
                [
                    'type' => 'image',
                    'originalContentUrl' => 'https://www.sampleimage.com/sample.jpg',
                    'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
                ]
            ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addImage('https://www.sampleimage.com/sample.jpg', 'https://www.sampleimage.com/sample-preview.jpg');
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testReplyVideoMessage()
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [
                [
                    'type' => 'video',
                    'originalContentUrl' => 'https://www.sampleimage.com/sample.mp4',
                    'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
                ]
            ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function testPushVideoMessage()
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [
                [
                    'type' => 'video',
                    'originalContentUrl' => 'https://www.sampleimage.com/sample.mp4',
                    'previewImageUrl' => 'https://www.sampleimage.com/sample-preview.jpg'
                ]
            ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addVideo('https://www.sampleimage.com/sample.mp4', 'https://www.sampleimage.com/sample-preview.jpg');
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testReplyAudioMessage()
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [
                [
                    'type' => 'audio',
                    'originalContentUrl' => 'https://www.sampleimage.com/sample.m4a',
                    'duration' => 10000
                ]
            ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function testPushAudioMessage()
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [
                [
                    'type' => 'audio',
                    'originalContentUrl' => 'https://www.sampleimage.com/sample.m4a',
                    'duration' => 10000
                ]
            ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addAudio('https://www.sampleimage.com/sample.m4a', 10000);
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testReplyMultiMessage()
    {
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
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト1');
        $bot->addText('テスト2');
        $bot->addText('テスト3');
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function testPushMultiMessage()
    {
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
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト1');
        $bot->addText('テスト2');
        $bot->addText('テスト3');
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function confirmMessageProvider()
    {
        return [
            'valid confirm message' => [
                [
                    'type' => 'template',
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
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
    public function testReplyConfirmMessage($expectedConfirmArray, $title, $confirmSource)
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [ $expectedConfirmArray ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addConfirm($title, $confirmSource);
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

  /**
   * @dataProvider confirmMessageProvider
   */
    public function testPushConfirmMessage($expectedConfirmArray, $title, $confirmSource)
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [ $expectedConfirmArray ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addConfirm($title, $confirmSource);
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testReplyConfirmMessageWithButtonOptionPass()
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [
                [
                    'type' => 'template',
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
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
                                'data' => 'key1=value1&key2=value2',
                                'text' => 'Postbackボタン'
                            ]
                        ]
                    ]
                ]
            ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addConfirm('タイトル', [
            [
                'title' => 'URLボタン',
                'action' => 'url',
                'url' => 'https://www.sampleimage.com/sample.jpg'
            ],
            [
                'title' => 'Postbackボタン',
                'action' => 'postback',
                'data' => 'key1=value1&key2=value2',
                'text' => 'Postbackボタン'
            ]
        ]);
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function buttonsMessageDataProvider()
    {
        return [
            'thumbnail and title not empty' => [
                [
                    'type' => 'template',
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
                    'template' => [
                        'type' => 'buttons',
                        'title' => 'タイトル',
                        'text' => '説明',
                        'thumbnailImageUrl' => 'https://www.sampleimage.com/thumbnail.jpg',
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
                '説明',
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
                ],
                'タイトル',
                'https://www.sampleimage.com/thumbnail.jpg'
            ],
            'title not empty' => [
                [
                    'type' => 'template',
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
                    'template' => [
                        'type' => 'buttons',
                        'title' => 'タイトル',
                        'text' => '説明',
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
                '説明',
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
                ],
                'タイトル',
                null
            ],
            'title empty' => [
                [
                    'type' => 'template',
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
                    'template' => [
                        'type' => 'buttons',
                        'text' => '説明',
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
                '説明',
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
                ],
                null,
                null
            ]
        ];
    }

  /**
   * @dataProvider buttonsMessageDataProvider
   */
    public function testReplyButtonsMessage($expectedButtonsArray, $description, $buttons, $title, $thumbnailUrl)
    {
        $this->_setCurlMockForReply(
            /* expected message */
            [ $expectedButtonsArray ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addButtons($description, $buttons, $title, $thumbnailUrl);
        $bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f');
        $this->addToAssertionCount(1);
    }

  /**
   * @dataProvider buttonsMessageDataProvider
   */
    public function testPushButtonsMessage($expectedButtonsArray, $description, $buttons, $title, $thumbnailUrl)
    {
        $this->_setCurlMockForPush(
            /* expected message */
            [ $expectedButtonsArray ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addButtons($description, $buttons, $title, $thumbnailUrl);
        $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0');
        $this->addToAssertionCount(1);
    }

    public function rawMessageDataProvider()
    {
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
                    'altText' => 'メッセージが届いています        (閲覧可能端末から見て下さい)',
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
    public function testReplyRawMessage($rawSource)
    {
        $this->_setCurlMockForReply(
            /* expected messages */
            [ $rawSource ],
            '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addRawMessage($rawSource);
        $this->assertTrue($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

  /**
   * @dataProvider rawMessageDataProvider
   */
    public function testPushRawMessage($rawSource)
    {
        $this->_setCurlMockForPush(
            /* expected messages */
            [ $rawSource ],
            '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            $this->successResponse
        );
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addRawMessage($rawSource);
        $this->assertTrue($bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testExceptionOccurredAtCurlReply()
    {
        $this->_curlMock->expects($this->once())
        ->method('post')
        ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト1');
        $bot->addText('テスト2');
        $bot->addText('テスト3');
        $this->assertFalse($bot->replyMessage('1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'));
    }

    public function testExceptionOccurredAtCurlPush()
    {
        $this->_curlMock->expects($this->once())
        ->method('post')
        ->will($this->throwException(new \RuntimeException('Curlでエラーが起きました', 1)));
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('テスト1');
        $bot->addText('テスト2');
        $bot->addText('テスト3');
        $this->assertFalse($res = $bot->pushMessage('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

    public function testTestSignature()
    {
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
        $x_line_signature = base64_encode(hash_hmac('sha256', $requestBody, 'develop', true));
        $this->assertTrue($bot->testSignature($requestBody, $x_line_signature));
    }

    public function testTestSignatureInvalidSignature()
    {
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $requestBody = '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"text","id":"2222222222222","text":"てすと"}}]}';
        $x_line_signature = base64_encode(hash_hmac('sha256', 'invalidString', true));
        $this->assertFalse($bot->testSignature($requestBody, $x_line_signature));
    }

  /**
   * @dataProvider requestBodyProvider
   */
    public function testParseEvents($requestBody, $expectedTypes, $expectedData)
    {
        $bot = new LineBot($this->_curlMock, $this->_configMock);
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
                ],
            'line follow event' =>
                [
                    '{"events":[{"type":"follow","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000}]}',
                    [ 'Unsupported' ],
                    [
                        null
                    ]
                ],
            'line unfollow event' =>
                [
                    '{"events":[{"type":"unfollow","source":{"userId":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","type":"user"},"timestamp":1495206000000}]}',
                    [ 'Unsupported' ],
                    [
                        null
                    ]
                ],
        ];
    }

    public function testGetProfile()
    {
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

    public function testGetProfileNoPictureUrl()
    {
        $this->_curlMock->expects($this->once())
        ->method('get')
        ->with(
            'https://api.line.me/v2/bot/profile/0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0',
            ['Authorization' => 'Bearer develop']
        )->willReturn('{"displayName":"Taro Test","userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","statusMessage":"ステータスメッセージ"}');
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $profile = new \stdClass();
        $profile->displayName = 'Taro Test';
        $profile->userId = '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0';
        $profile->statusMessage = 'ステータスメッセージ';

        $wrapper = new \stdClass();
        $wrapper->name = $profile->displayName;
        $wrapper->profilePic = null;
        $wrapper->rawProfile = $profile;
        $this->assertEquals($wrapper, $bot->getProfile('0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'));
    }

  /**
   * @dataProvider eventFileDataProvider
   */
    public function testGetFiles($requestBody, $expectedFile, $expectedUrl, $expectedBinary)
    {
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

    public function eventFileDataProvider()
    {
        return [
            'image' => [
                '{"events":[{"type":"message","replyToken":"1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f","source":{"userId":"0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0","type":"user"},"timestamp":1495206000000,"message":{"type":"image","id":"2222222222222"}}]}',
                [ '2222222222222.jpg' => 'imageBinary'],
                'https://api.line.me/v2/bot/message/2222222222222/content',
                'imageBinary'
            ]
        ];
    }

    private function _setCurlMockForReply($messages, $replyToken, $retVal)
    {
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
        )->willReturn($retVal);
    }

    public function testGetMessagePayload()
    {
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('message1');
        $bot->addText('message2');
        $expected = [
            [
                'type' => 'text',
                'text' => 'message1'
            ],
            [
                'type' => 'text',
                'text' => 'message2'
            ]
        ];
        $this->assertEquals($expected, $bot->getMessagePayload());
    }

    public function testClearMessage()
    {
        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $bot->addText('message1');
        $bot->addText('message2');
        $bot->clearMessages();
        $this->assertEquals([], $bot->getMessagePayload());
    }

    public function testSendRawDataReply()
    {
        $data = [
            'messages' => [
                [
                    'type' => 'text',
                    'text' => 'テスト'
                ]
            ],
            'replyToken' => '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f'
        ];
        $this->_setCurlMockForReply([
            [
                'type' => 'text',
                'text' => 'テスト'
            ]
        ], '1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f1f', $this->successResponse);

        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $this->assertEquals($this->successResponse, $bot->sendRawData($data));
    }

    public function testSendRawDataPush()
    {
        $data = [
            'messages' => [
                [
                    'type' => 'text',
                    'text' => 'テスト'
                ]
            ],
            'to' => '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0'
        ];
        $this->_setCurlMockForPush([
            [
                'type' => 'text',
                'text' => 'テスト'
            ]
        ], '0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0', $this->successResponse);

        $bot = new LineBot($this->_curlMock, $this->_configMock);
        $this->assertEquals($this->successResponse, $bot->sendRawData($data));
    }

    private function _setCurlMockForPush($messages, $recipientId, $retVal)
    {
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
        )->willReturn($retVal);
    }

    private $successResponse = '{}';

    private $errorResponse = '{"message": "error message"}';
}
