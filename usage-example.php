<?php

/*
  FacebookとLineで共通の(似ている)テンプレートを使うとき
*/

require_once __DIR__ . '/vendor/autoload.php';

use MessengerFramework\MessengerBot;

$bots = [ new MessengerBot('facebook'), new MessengerBot('line') ];
foreach ($bots as $bot) {
  foreach ($bot->events as $event) {
    $bot->addText("テキストメッセージボディ");
    $bot->addTemplate([
      [
        'テンプレートのタイトル1',
        'テンプレートの説明1',
        [
          'title' => 'Postbackボタン1',
          'action' => 'postback',
          'data' => '何かデータ1'
        ], [
          'title' => 'Urlボタン1',
          'action' => 'url',
          'url' => 'アドレス1'
        ]
      ], [
        'テンプレートのタイトル2',
        'テンプレートの説明2',
        [
          'title' => 'Postbackボタン2',
          'action' => 'postback',
          'data' => '何かデータ2'
        ], [
          'title' => 'Urlボタン2',
          'action' => 'url',
          'url' => 'アドレス2'
        ]
      ]
    ]);
    $bot->addImage("ファイルのURL", "プレビューのURL(Line用)");
    $bot->reply($event->replyToken);
  }
}

/*
  Pushをする時
*/

// pushはLineの場合ReplyTokenではなくユーザーIDだがイベントを元にpushすることは無いと見て
// pushはサービス側で保存したユーザーIDを入れてもらうものとする
$bot->push($recipient);

/*
  イベントの種類を判別
*/

// 文字列で判別
if ($event->type === "Message.Text") {
  // テキストメッセージが来た
} elseif ($event->type === "Message.File") {
  // ファイルが来た
}

/*
  プロフィールを取りたい時
*/

foreach ($bot->events as $event) {
  $profile = $bot->getProfile($event->userId);
}

/*
  ユーザーから送られてきたファイルの取得について
*/

// イベントの種類によってプロパティーの有る無しが変わるのか?
// 何も返さなかったり、エラーを出すなど
if ($event->type === "Message.Text") {
  $event->text;
  $event->files; // null
} elseif ($event->type === "Message.File") {
  $event->text; // null
  $event->files;
}

// Facebook Message 1 - * File
// Line Message 1 - 1 File
// これを揃えるために送られてきたファイル一つでも[BinaryString]
$event->getFiles();

/*
  Facebookのボットを詳細に使う時
*/

use MessengerFramework;

$curl = new MessengerFramework\HttpClient\Curl();
$bot = new MessengerFramework\FacebookBot\FacebookBot($curl);
$builder = new MessengerFramework\FacebookBot\TextMessageBuilder('test message');
$bot->replyMessage('user id', $builder);
