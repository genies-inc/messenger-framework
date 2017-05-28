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

ラッパーのイベントについてくるファイルはバイナリ文字列を1ファイルとしたものの配列で手に入るようになっている。  
(Lineは1メッセージにつき1ファイルと決まっているが配列に揃える)

ラッパーのイベントのuserIdはユーザーのプロフィールを取得したりpushしたりする時に使うユーザー毎に一意なID。

ラッパーのイベントのreplyTokeは返信に必要なトークン、Facebookは`sender.id`、Lineは`replyToken`のこと。

# テスト

グローバルの関数や変数を擬似的に上書きしてテストなどでモック動作をさせることができるようになった。  
名前空間を別々の場所で使用してテストコードから上書き用ファイルを読み込む、上書きした関数や変数が返したい値をグローバル変数で設定する。  
読み込まれたファイルは上書きする関数内でモックの動作をするか`call_user_func()`を使って本来の動作をするかを振り分ける。

テストをするために毎回Herokuに上げたりしないでもリクエストボディを作ることができる。

## テスト中に正しい値を保持できているか確認するための関数を用意した

`INJECT_ASERTER.php`を読み込んで`global $testerMock`に任意のアサーションを入れた関数を代入する。  
テスト対象の処理中でtester()を呼び出せばそのアサーションが呼ばれるので、ある時点での値をテストする時に使える。  
クラスがあまりわかれていなくて隠蔽されている状況ではこういうものを使うしか無い?

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
