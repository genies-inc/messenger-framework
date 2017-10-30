# messenger-framework

各サービスのMessengerBotのラッパー。バージョン1.5.2

# 利用時の注意

Facebookの初回認証は対応していない。

# インストール

## Composer

以下の記述を`composer.json`に付け加えて下さい

```
"require": {
    "genies/messengerframework": "*"
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/genies-inc/messenger-framework.git"
    }
]
```

```
composer install
```

# 使い方

## 読み込みと初期化

```
require_once './vendor/autoload.php';

$config = new \MessengerFramework\Config('facebook', 'Appシークレット', 'アクセストークン');
// $config = new \MessengerFramework\Config('line', 'チャンネルシークレット', 'アクセストークン');
$bot = new \MessengerFramework\MessengerBot($config);
```

## イベント(メッセージ)の利用

```
$events = $bot->getEvents();
foreach ($events as $event) {
  switch ($event->type) {
    case 'Message.Text':
      error_log($event->data['text']);
      break;
    case 'Postback':
      error_log($event->data['postback']);
      break;
    case 'Message.File':
      // [ ファイル名かID => バイナリ ]の連想配列
      $files = $bot->getFilesIn($event);
      break;
    case 'Message.Sticker':
      error_log(json_encode($event->data['sticker'], JSON_PRETTY_PRINT));
      break;
    case 'Message.Location':
      error_log(json_encode($event->data['location'], JSON_PRETTY_PRINT));
      break;
    case 'Unsupported':
      error_log(json_encode($event, JSON_PRETTY_PRINT));
      break;
  }
}
```

## メッセージの送信

主なメッセージの送信の仕方を紹介します。  
メッセージは5件まで追加して送信可能です。  
(Facebookのボット使用中はリクエストをメッセージ件数回送るので原子性はないです)

```
// テキストを送信
$bot->addText('こんにちは');

// ボタンを送信
$bot->addButtons('ボタンを押して下さい', [
  [
    'title' => 'Postbackボタン1',
    'action' => 'postback',
    'data' => 'postbackのデータ1'
  ],
  [
    'title' => 'Postbackボタン2',
    'action' => 'postback',
    'data' => 'postbackのデータ2'
  ],
  [
    'title' => 'URLボタン',
    'action' => 'url',
    'url' => 'https://github.com/genies-inc/messenger-framework'
  ]
]);

// 画像を送信
$bot->addImage('https://sample.com/sample.jpg', 'https://sample.com/sample_preview.jpg');

// テンプレートメッセージを送信（FacebookではGeneric、LineではCarousel）
$bot->addTemplate([
  [
    'カラムのタイトル',
    'カラムの説明',
    'https://sample.com/sample1.jpg',
    [
      [
        'title' => 'Postbackボタン1',
        'action' => 'postback',
        'data' => 'postbackのデータ1'
      ],
      [
        'title' => 'Postbackボタン2',
        'action' => 'postback',
        'data' => 'postbackのデータ2'
      ]
    ]
  ],
  [
    'カラムのタイトル2',
    'カラムの説明2',
    'https://sample.com/sample2.jpg',
    [
      [
        'title' => 'Postbackボタン3',
        'action' => 'postback',
        'data' => 'postbackのデータ1'
      ],
      [
        'title' => 'Postbackボタン4',
        'action' => 'postback',
        'data' => 'postbackのデータ2'
      ]
    ]
  ]
]);

// 確認用のメッセージを送信
$bot->addConfirm('確認用のボタンです', [
  [
    'title' => 'はい',
    'action' => 'postback',
    'data' => 'はい'
  ],
  [
    'title' => 'いいえ',
    'action' => 'postback',
    'data' => 'いいえ'
  ],
]);

// 返信をする場合
$isError = $bot->reply($event->replyToken);
// プッシュをする場合
// $isError = $bot->push($event->userId);

// これらの戻り値はAPIからのレスポンスがエラーであるかどうか
```

# 仕様

概要は`docs/class-spec.puml`内。

![クラスの仕様](docs/class-spec.png "クラスの仕様")

[ドキュメント(PHPDoc)](https://genies-inc.github.io/messenger-framework/)

フレームワーク全体でプラットフォームの差異を吸収するという考えで作られている。  
Facebook、LineのBotを扱うためのクラスを用意し、それを更にラップするクラスを提供している。  
基本的に少ない方、不自由な方に合わせている。

フレームワーク全体で吸収という考えなのでMessengerBot配下のFacebookBotやLineBotがMessengerBotの層で使われる差異が吸収されたEventクラスを返す。

詳細な取扱がしたいのなら各プラットフォームの公式SDKを使ったほうがいい。  
しかし、このフレームワークでも各プラットフォームのBotはMessengerBotと似た使用感で触ることはできる。

ラッパークラスMessengerBotはプラットフォームを使い分けること、Webhookリクエストボディの取り出しなどグローバルの値に近いものにのみ責任を持つ。

メッセージ送信などリクエストの不備は各APIが教えてくれるので原則このフレームワーク内でそれらのチェックは行わない。  

MessengerBot#reply()やMessengerBot#push()などでAPIのレスポンスを受け取らないとわからないことの例

+ Lineでのカルーセルの高さの不揃いはLineの公式SDKは教えてくれない
+ Lineでの同時送信の上限
+ 存在しないファイルの送信(リンク切れのアイコンがMessenger上に表示される)

## Eventクラス

各プラットフォームからのイベントを表すクラス。
イベントとはFacebookなら`リクエスト#Entry[*]#Messaging[*]`を指し、Lineなら`リクエスト#Events[*]`を指す。

Event#rawData そのままのイベント
Event#その他 プロパティー名通りのプラットフォーム間の差異を吸収したもの。  
ない場合はnullになる。

例

+ Event#userIdはユーザーのプロフィールを取得したりpushしたりする時に使うユーザー毎に一意なID。
+ Event#replyTokeは返信に必要なトークン、Facebookは`sender.id`、Lineは`replyToken`のこと。

## MessengerBotクラス

`MessengerBot#getFilesIn(Event message)`はEventに含まれるファイルを取得するメソッド。  
つまりFacebookならMessaging単位、LineならEvent単位。

ファイルはバイナリ文字列を1ファイルとしたものの配列で手に入るようになっている。  
(Lineは1メッセージにつき1ファイルと決まっているが配列に揃える)
`[ ファイル名 => バイナリ文字列 ]`

`MessengerBot#getEvents()`

未対応のEventはnullとなるのでnullが混じったEventの配列が返ってくる。  
FacebookはMessaging、LineはEventより上の階層で未対応のフォーマットだったら例外が投げられる。

`MessengerBot#getProfile()`で返ってくる連想配列の値は未設定などの理由でnullを取り得る。

Profileの連想配列

```
[
  'name' => ユーザー名,
  'profilePic' => プロフィール画像のURL,
  'rawProfile' => プラットフォームのプロフィールデータをstdClass化したもの
]
```

### MessengerBot#add種類(TextやTemplate)

#### TemplateMessage

FacebookだとGeneric、LineだとCarouselに当たるもの。  
MessengerBot#addTemplateを使っていてカラムが1つのときはLineはCarouselではなくButtonsを使うようにしてある。

書式

```
[
  [
    タイトル,
    説明,
    アイキャッチ画像のURL,
    [
      [
        'title' => ボタンのタイトル,
        'action' => ボタンの種類,
        'data|url' => Postbackのデータ、またはURL
      ]
    ]
  ]
]
```

このボタンの書式はMessengerBotだけではなくFacebookBotやLineBotなどプラットフォームのボットの層でもGenericやCarouselを生成するのに使う。  
ボタンのオプションは上記の通りtitile、action、data、urlを指定するとFacebook、Lineにあったものに変換する。  
**それ以外のオプションを指定した場合は変換されずにそのまま追加される。**

##### 使えるボタンの種類

+ Postbackボタン
+ Urlボタン(https強制、url強制(telやftpやなんかのidとかみたいなURIはだめ))

#### ファイル系(Image、Video、Audio)

ファイルのURLが必要(https強制)

重い動画などを送るとFacebookのメッセージ送信APIのレスポンスに待たされてしまうため、Webhookリクエストに対するレスポンスを制限時間以内に返せない。  
FacebookがリトライしたWebhookリクエストを続けざまに受けるということが起きていた。
これによって同じ動画を次々と送り続けるという不具合があった。

とりあえずの策としてCurlにタイムアウトを付けた

一定時間経ったらメッセージ送信の結果を待たずにFacebookのWebhookリクエストのレスポンスを返してしまう。  
そのときCurlはタイムアウトの例外が起きたと例外を投げる。  
FacebookBotやLineBotのクラス内でその送信は失敗として扱われFalseを返す。  
メッセージは送信される。

## Curlクラス

環境変数`PROXY_URL`がセットされていたらそれをプロキシとして利用する。

# 開発手順

セットアップ

```
composer install
```

ユニットテスト

```
# 全部走る
composer test
# 特定のTestSuiteのみ走る
vendor/bin/phpunit --testsuite "TestSuite名"
```

デバッグ

VSCodeを使う場合。

PHPDebugを導入しXDebugをPHPに導入しておく。  
これでブレークポイントで変数を見ながら実行・テストできる。  
デバッグの設定は.vscode/launch.jsonから行う。

# テスト

テストに関するメモ書き

## グローバルの関数や変数を擬似的に上書きしてテストなどでモック動作をさせることができるようにした

名前空間を別々の場所で使用してテストコードから上書き用ファイルを読み込む、上書きした関数や変数が返したい値をグローバル変数で設定する。  
読み込まれたファイルは上書きする関数内でモックの動作をするか`call_user_func()`を使って本来の動作をするかを振り分ける。

## HTTPリクエストをそのままレスポンスボディにして返すサーバーを用意した

APIを正しく使えているか、HTTPリクエストを送り出す時点で期待通りの組み立てが出来ているのかをテストするためのサーバーを用意した。

実行にはNode.jsが必要。(詳しくはdevtool/mirror_server内の説明書を参照)

リクエストを正しい形で送り出すということが期待通りにできているかどうかをオウム返ししてきたレスポンスを見てテストする。

# ライセンス

MITライセンスです。  
LICENSE.txtを見て下さい。

# TODO

## Composer非対応の環境用のまとめたスクリプトを生成するツールを用意する

## ドキュメントをまともな日本語で書く
