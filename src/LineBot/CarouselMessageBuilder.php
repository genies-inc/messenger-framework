<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder;

class CarouselMessageBuilder implements MessageBuilder {

  private $template = null;

  /*
    columnsの形
    [
      [
        'タイトル1',
        'サブタイトル1',
        'アイキャッチ画像',
        [
          ボタン連想配列の配列
        ]
      ]
      上の配列がCarouselのcolumnの数だけ続く
    ]
  */
  public function __construct(Array $columns) {

    $columnsTemplate = [];
    foreach ($columns as $column) {
      $actions = [];

      foreach (array_slice($column, 3) as $button) {
        switch ($button['action']) {
          case 'postback' :
          array_push($actions, new TemplateActionBuilder\PostbackTemplateActionBuilder($button['title'], $button['data']));
          break;
          case 'url' :
          array_push($actions, new TemplateActionBuilder\UriTemplateActionBuilder($button['title'], $button['url']));
          break;
          default :
          break;
        }
      }
      array_push($columnsTemplate, new CarouselColumnTemplateBuilder($column[0], $column[1], $column[2] ?? '', $actions));
    }
    $this->template = new CarouselTemplateBuilder($columnsTemplate);
  }

  public function buildMessage() {
    $core = new TemplateMessageBuilder('', $this->template);
    return $core->buildMessage();
  }

}
