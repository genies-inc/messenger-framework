<?php

namespace MessengerFramework\FacebookBot;

use MessengerFramework\MessageBuilder;

class GenericMessageBuilder implements MessageBuilder {

  private $columns;

  public function __construct($columns) {
    $this->columns = $columns;
  }

  public function buildMessage() {
    $elements = [];
    foreach ($this->columns as $column) {
      array_push($elements, $this->buildColumn($column));
    }

    return [
      'attachment' => [
        'type' => 'template',
        'payload' => [
          'template_type' => 'generic',
          'elements' => $elements
        ]
      ]
    ];
  }

  private function buildColumn($columnArray) {
    $buttons = [];
    foreach (array_slice($columnArray, 3) as $button) {
      array_push($buttons, $this->buildButton($button));
    }

    $column = [
      'title' => $columnArray[0],
      'subtitle' => $columnArray[1],
      'buttons' => $buttons
    ];

    if (!is_null($columnArray[2])) {
      $column['image_url'] = $columnArray[2];
    }

    return $column;
  }

  private function buildButton($buttonArray) {
    switch ($buttonArray['action']) {
      case 'postback' :
      return [
        'type' => 'postback',
        'title' => $buttonArray['title'],
        'payload' => $buttonArray['data']
      ];
      case 'url' :
      return [
        'type' => 'web_url',
        'title' => $buttonArray['title'],
        'url' => $buttonArray['url']
      ];
      default :
      throw new \InvalidArgumentException('その種類のボタンアクションには対応していません。');
    }
  }

}
