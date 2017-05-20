# messenger-framework

各サービスのMessengerBotのラッパー。

# 開発手順

セットアップ

```
composer install
```

ユニットテスト

```
# 全部走る
vendor/bin/phpunit
# 特定のTestSuiteのみ走る
vendor/bin/phpunit --testsuite "TestSuite名"
```

# 仕様

概要は`class-spec.puml`、`usage-example.php`内。

## 一部詳細

Facebookの生のイベント(`ラッパーのイベント#rawData`)は`リクエスト#Entry`を展開し、`それぞれ#Messaging`を展開したものとする。
Lineの生のイベントは`リクエスト#events`を展開したものとする。

ラッパーのイベントについてくるファイルはバイナリ文字列を1ファイルとしたものの配列で手に入るようになっている。(Lineは1メッセージにつき1ファイルと決まっているが配列に揃える)

ラッパーのイベントのuserIdはユーザーのプロフィールを取得したりpushしたりする時に使うユーザー毎に一意なID。

ラッパーのイベントのreplyTokeは返信に必要なトークン、Facebookは`sender.id`、Lineは`replyToken`のこと。

# テスト

グローバルの関数や変数を擬似的に上書きしてテストなどでモック動作をさせることができるようになった。
名前空間を別々の場所で使用してテストコードから上書き用ファイルを読み込む、上書きした関数や変数が返したい値をグローバル変数で設定する。
読み込まれたファイルは上書きする関数内でモックの動作をするか`call_user_func()`を使って本来の動作をするかを振り分ける。

テストをするために毎回Herokuに上げたりしないでもリクエストボディを作ることができる。

# 検討

## イベント(メッセージ)をstdClassから独自クラスへ変更

Webhookで受け取ったイベント(メッセージ)を`stdClass`からEventなどの独自クラスへ変更を検討している。

### 理由

+ MessengerBotのメソッド(そこからしか呼ばれないprivateも含めたもの)が長くなりすぎる
+ 上の理由によりテストが非常に困難、メソッドの仕様がわかりにくい
+ filesを`MessengerBot#getEvents()`時に取得しているため、必要以上に待ち時間が発生する
  (getFiles()などとしたクロージャをつけて実現は可能だがわかりにくい)

### Eventクラス

Facebook、Lineの判別はMessengerBotが責任を持ち、個々のイベントを共通な名前に落とし込む実際の処理はこのEventのサブクラスに持たせる。

### 改善できそうなこと

+ `MessengerBot#getEvents()`がわかりやすい処理になる
+ リクエストボディを取るのに使っていた`file_get_contents()`を抽象化出来てテストや取り回しが良くなる
+ filesを必要なタイミングで取り出せるようになる
