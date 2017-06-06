<?php

namespace MessengerFramework\LineBot;

use MessengerFramework\MessageBuilder;

class CarouselMessageBuilder implements MessageBuilder {

  private $columns;

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
    $this->columns = $columns;
  }

  public function buildMessage() {
    return [
      [
        'type' => 'template',
        'altText' => 'alt text for carousel',
        'template' => $this->buildCarousel($this->columns)
      ]
    ];
  }

  private function buildAction($button) {
    $action = [
      'type' => $button['action'],
      'label' => $button['title']
    ];
    switch ($button['action']) {
      case 'postback' :
      $action['data'] = $button['data'];
      break;
      case 'url' :
      $action['uri'] = $button['url'];
      $action['type'] = 'uri';
      break;
      default :
      break;
    }
    return $action;
  }

  private function buildColumn($column) {
    $actions = [];
    foreach (array_slice($column, 3) as $button) {
      array_push($actions, $this->buildAction($button));
    }
    $column = [
      'thumbnailImageUrl' => $column[2],
      'title' => $column[0],
      'text' => $column[1],
      'actions' => $actions,
    ];
    return $column;
  }

  private function buildCarousel($columns) {
    $columnTempaltes = [];
    foreach ($columns as $column) {
      array_push($columnTempaltes, $this->buildColumn($column));
    }
    return [
      'type' => 'carousel',
      'columns' => $columnTempaltes,
    ];
  }

}
