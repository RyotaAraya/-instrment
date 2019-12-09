<?php

//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ゲストログイン');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=======================
//ログイン画面処理
//=======================
//post送信されていた場合
debug('POST送信があります。');

//変数にユーザー情報を代入
$email = 'gest@gmail.com';
$pass = 111111;

debug('バリデーションOKです。');

//例外処理
try {
  //DBへ接続
  $dbh = dbConnect();
  //SQL文作成
  $sql1 = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';

  $data = array(':email' => $email);
  //クエリ実行
  $stmt1 = queryPost($dbh, $sql1, $data);
  //クエリ結果の値を取得
  $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

  debug('クエリ結果1の中身 $result1：login.php：' . print_r($result1, true));


  //パスワード照合
  //DBに保存されているハッシュ化されたパスワードと入力したパスワードを比較する
  if (!empty($result1) && password_verify($pass, $result1['password'])) {
    debug('パスワードがマッチしました。');

    //ログイン有効期限(デフォルトを1時間とする)
    $sesLimit = 60 * 10;
    //最終ログイン日時を現在日時に
    $_SESSION['login_date'] = time(); //time関数は1970年1月1日 00:00:00を0として、1秒経過するごとに1ずつ増加させた値
    //次回からログイン保持しないため、ログイン有効期限を10分にセット
    $_SESSION['login_limit'] = $sesLimit;
    //ユーザーIDを格納
    $_SESSION['user_id'] = $result1['id'];

    debug('セッション変数の中身：' . print_r($_SESSION, true));
    debug('マイページへ遷移します。');
    header("Location:mypage.php"); //マイページへ

    debug('ゲスト機能制限');
    $err_msg['common'] = MSG19;
    $_SESSION['msg_success'] = SUC10;

    exit();
  } else {
    debug('パスワードがアンマッチです。');
    $err_msg['common'] = MSG09;
  }
} catch (Exception $e) {
  error_log('エラー発生：' . $e->getMessage());
  $err_msg['common'] = MSG09;
}
debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
