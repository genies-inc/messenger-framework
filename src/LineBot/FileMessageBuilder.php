<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\MessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;

class FileMessageBuilder implements MessageBuilder {

  private $sdkBuilder;

  private $filePath;

  private $previewData;

  public function __construct(String $type, String $filePath, $previewData) {
    switch ($type) {
      case 'image' :
      $this->sdkBuilder = new ImageMessageBuilder($filePath, $previewData);
      break;
      case 'video' :
      $this->sdkBuilder = new VideoMessageBuilder($filePath, $previewData);
      break;
      case 'audio' :
      $this->sdkBuilder = new AudioMessageBuilder($filePath, $previewData);
      break;
      default :
      // TODO: LineAPIのsend message objectの未対応ファイルのエラーの責任をここじゃなくてAPIに押し付ける
      // 最終的に間違っているファイルはAPIが教えてくれる
      // しかし、現状メッセージビルダーの時点で教えているので管理しづらい
      // LINESDKを切り離したら
      // フレームワーク利用側から伝わってきた$type引数をそのまま使ってAPIへ送って責任をAPIへ押し付ける
      // ちなみに
      // MessageBuilderをImage、Video、Audio用に分ける方法だと
      // 結局その対応付の責任をラッパーなどフレームワークが持つことになってしまう
      throw new \InvalidArgumentException('未対応のファイルメッセージタイプです。');
    }
  }

  public function buildMessage() {
    return $this->sdkBuilder->buildMessage();
  }

}
