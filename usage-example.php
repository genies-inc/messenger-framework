<?php

/*
  FacebookとLineで共通の(似ている)テンプレートを使うとき
*/

require_once __DIR__ . '/vendor/autoload.php';

use MessengerBotFramework\MessengerBot;

$bots = [ new MessengerBot('facebook'), new MessengerBot('line') ];
foreach ($bots as $bot) {
  foreach ($bot->events as $event) {
    $bot->addText("テキストメッセージボディ");
    $bot->addTemplate(
      "テンプレートのタイトル",
      "テンプレートの説明"
      /* , [
        "title" => "ボタン1",
        "action" => "postback",
        "data" => "何かデータ"
      ], [
        "title" => "ボタン2",
        "action" => "message",
        "data" => "名前=値&名前2=値2"
      ] ... */
    );
    $bot->addFile("ファイルのURL");
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
