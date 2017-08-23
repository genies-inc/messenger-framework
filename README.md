# messenger-framework

各サービスのMessengerBotのラッパー。バージョン1.4.0

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

詳細は`docs/usage-example.php`を参照。

## 読み込みと初期化

```
require_once './vendor/autoload.php';

use MessengerFramework\MessengerBot;

$bot = new MessengerBot('facebook');
```

## イベント(メッセージ)の利用

```
$events = $bot->getEvents();
foreach ($events as $event) {
  switch ($event->type) {
    case 'Message.Text' :
    // テキストメッセージが来た
    error_log($event->text);
    break;
    case 'Postback' :
    // Postbackが来た
    error_log($event->postbackData);
    break;
    default :
    break;
  }
}
```

## メッセージの送信

```
$bot->addText('こんにちは');
$bot->addImage('https://sample.com/sample.jpg', 'https://sample.com/sample_preview.jpg');
$bot->addText('これは私です');
$bot->reply($event->replyToken);
```

# 仕様

概要は`docs/class-spec.puml`内。

![クラスの仕様](docs/class-spec.png "クラスの仕様")

[ドキュメント(PHPDoc)](https://genies-inc.github.io/messenger-framework/)

ドキュメント(PHPDoc)は`composer gendoc`とコマンドで`docs`に作成できる。

フレームワーク全体でプラットフォームの差異を吸収するという考えで作られている。  
Facebook、LineのMessengerBotを扱うためのフレームワークを用意し、それを更にラップするクラスを提供している。  
基本的に少ない方、不自由な方に合わせている。

フレームワーク全体で吸収という考えなのでMessengerBot配下のFacebookBotやLineBotがMessengerBotの層で使われる差異が吸収されたEventクラスを返す。

詳細な取扱がしたいのなら各プラットフォームの公式SDKを使ったほうがいい。  
しかし、このフレームワークでも各プラットフォームのBotはMessengerBotと似た使用感で触ることはできる。

ラッパークラスMessengerBotはプラットフォームを使い分けること、Webhookリクエストボディの取り出しなどグローバルの値に近いものにのみ責任を持つ。

メッセージ送信などリクエストの不備は各APIが教えてくれるので原則このフレームワーク内でそれらのチェックは行わない。  

以下、APIまでいかないと教えてくれないことの例

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

ラッパーのイベントのuserIdはユーザーのプロフィールを取得したりpushしたりする時に使うユーザー毎に一意なID。

ラッパーのイベントのreplyTokeは返信に必要なトークン、Facebookは`sender.id`、Lineは`replyToken`のこと。

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
それ以外のオプションを指定した場合は変換されずにそのまま追加される。

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
そのときCurlはタイムアウトの例外が起きたことを伝える。  
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

基本的に上位のクラスからしていく。  
余裕ができた時に下位のクラスの単体テストを作っていく。  
ラッパーやアダプターのようなクラスで内部状態、グローバルな状態を前提として準備するテストになるのはやむを得ない。

## グローバルの関数や変数を擬似的に上書きしてテストなどでモック動作をさせることができるようにした

名前空間を別々の場所で使用してテストコードから上書き用ファイルを読み込む、上書きした関数や変数が返したい値をグローバル変数で設定する。  
読み込まれたファイルは上書きする関数内でモックの動作をするか`call_user_func()`を使って本来の動作をするかを振り分ける。

## HTTPリクエストをそのままレスポンスボディにして返すサーバーを用意した

APIを正しく使えているか、HTTPリクエストを送り出す時点で期待通りの組み立てが出来ているのかをテストするためのサーバープログラムを用意した。

実行にはNode.jsが必要。(詳しくはdevtool/mirror_server内の説明書を参照)

APIにリクエストをするまではフレームワーク側の責任なのでそれをテストするために使う。  
最後の責任であるリクエストを正しい形で送り出す。ということが期待通りにできているかどうかをオウム返ししてきたレスポンスを見てテストする。

# ライセンス

MITライセンスです。  
LICENSE.txtを見て下さい。

# TODO

## APIのレスポンスを各Messengerで共通して扱えるようなクラスを用意する

各MessengerのAPIからの結果を統一的なインタフェースで扱えるようなクラスを用意する。  
成功したのかどうかや、届いているのかどうかなどがわかるようなクラス。

### 現状

httpのレスポンスボディをそのまま返している。  
Curlがタイムアウトなどの例外を出した時はそのmessageとcodeをプロパティーに持つstdClassのインスタンスをJSON化して返している。

## Composer非対応の環境用のまとめたスクリプトを生成するツールを用意する

## ドキュメントをまともな日本語で書く
