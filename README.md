# messenger-framework

各サービスのMessengerBotのラッパー。

# 利用時の注意

Facebookの初回認証は対応していない。

# インストール

## Composer

```
composer require genies/messengerframework
```

# 使い方

詳細は`doc/usage-example.php`を参照。

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

概要は`doc/class-spec.puml`内。  
ドキュメント(PHPDoc)は`composer gendoc`とコマンドで`doc/phpdoc/`に作成できる。

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

##### 使えるボタンの種類

+ Postbackボタン
+ Urlボタン(https強制、url強制(telやftpやなんかのidとかみたいなURIはだめ))

#### ファイル系(Image、Video、Audio)

ファイルのURLは必要(https強制)

重い動画などを送るとFacebookのメッセージ送信APIのレスポンスに待たされてしまうため、Webhookリクエストに対するレスポンスを制限時間以内に返せない。  
FacebookがリトライしたWebhookリクエストを続けざまに受けるということが起きていた。
これによって同じ動画を次々と送り続けるという不具合があった。

Curlにタイムアウトを付けた

一定時間経ったらメッセージ送信の結果を待たずにFacebookのWebhookリクエストのレスポンスを返してしまう。  
そのときCurlはタイムアウトの例外が起きたことを伝える。  
メッセージは送信される。

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

テストをするために毎回Herokuに上げたりしないでもリクエストボディを作ることができる。

## HTTPリクエストをそのままレスポンスボディにして返すサーバーを用意した

APIを正しく使えているか、HTTPリクエストを送り出す時点で期待通りの組み立てが出来ているのかをテストするためのサーバープログラムを用意した。

実行にはNode.jsが必要。(詳しくはdevtool/mirror_server内の説明書を参照)

APIにリクエストをするまではフレームワーク側の責任なのでそれをテストするために使う。  
最後の責任であるリクエストを正しい形で送り出す。ということが期待通りにできているかどうかをオウム返ししてきたレスポンスを見てテストする。

# TODO

## 未対応のイベントが混じっていたときのMessengerBot#getEvents()の挙動を決める

`MessengerBot#getEvents()`をした時イベントオブジェクトが配列で返ってくるが、途中に未対応の種類のイベントが混じっていた場合に  
エラーを投げるのか、空のイベントオブジェクトを入れておくのかnullなのかなどを決める。  

### 現状

リクエストボディを展開した時、最下位のイベント(メッセージ)が未対応だった場合はnullとし、  
それより上位が未対応の形式であった場合、例外を投げる。  
(上位の形式を処理できなかったら、下位を展開して配列にしようがないから。)

## APIのレスポンスを各Messengerで共通して扱えるようなクラスを用意する

各MessengerのAPIからの結果を統一的なインタフェースで扱えるようなクラスを用意する。  
成功したのかどうかや、届いているのかどうかなどがわかるようなクラス。

### 現状

httpのレスポンスボディをそのまま返している。  
Curlがタイムアウトなどの例外を出した時はそのmessageとcodeをプロパティーに持つstdClassのインスタンスをJSON化して返している。

## Herokuなどで動いているコードからこのフレームワークを呼び出すときの様々な問題

まずベタ書きをしているとHerokuはcomposerでフレームワークをインストールするところから動き出すので、環境ごとに設定を書き換えるタイミングがない。  
これを回避するためにはライブラリまるごとgit管理下に置くという方法があるが管理されるファイル数が増えるので避けた方がいい。  
チャンネルシークレットなどの設定はリクエストの検証処理で使われるが、その処理に少しでも関わるテストをするたびにフレームワークにべた書きされた設定と  
テストコード中に散らばっている設定を同じものに書き換え直すのは困難。  
グローバル定数として定義するのも定義がかぶるときが出てきてしまったり、テストをする際もphp.iniを書き換えrunkitを使ってといった手順を踏んで定数の削除をしたりしないといけない。  
テスト側と実際のコード側で設定の読み込み先（定義場所）が違うものであることを保証したい  
(テスト実行時にはテスト用の値を読み込ませ、本番時には本番用の値を読み込ませるようにしたい)  
それは本番かどうかの分岐コードなどではなく  
一番最初の設定時点（環境変数など）に依存していると置き換えが用意  
クラスを入れ子にしてnewしていくようなイメージ（テストしやすい）

### 現状

Facebook、Lineの各ボットクラス内でのみ使えるベタ書きの定数で設定をしている。  
フレームワーク利用側からvendorフォルダ以下のフレームワーク本体のベタ書きの部分を書き換えることが出来ない環境(Heroku)への対策として、  
もし設定してあるようであれば環境変数から読み込むようにして両立をするようにした。  
テストをする際は設定ファイルを読み込んだりするのではなく、各テストクラス内で使える定数を定義するようにした。
