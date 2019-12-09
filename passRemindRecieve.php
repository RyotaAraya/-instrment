<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Password再発行　認証キー照合し新Passwordをメール送信　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人が使う画面なので）

//SESSIONに認証キーがあるか確認、なければリダイレクト
if (empty($_SESSION['auth_key'])) {
  header("Location:passRemindSend.php"); //認証キー送信ページへ
  exit();
}

//================================
// 画面処理
//================================
//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //変数に認証キーを代入
  $auth_key = $_POST['token'];

  //未入力チェック
  validRequired($auth_key, 'token');

  if (empty($err_msg)) {
    debug('未入力チェックOK。');

    //固定長チェック
    validLength($auth_key, 'token');
    //半角チェック
    validHalf($auth_key, 'token');

    if (empty($err_msg)) {
      debug('バリデーションOK。');

      if ($auth_key !== $_SESSION['auth_key']) {
        $err_msg['common'] = MSG15;
      }
      if (time() > $_SESSION['auth_key_limit']) {
        $err_msg['common'] = MSG16;
      }

      if (empty($err_msg)) {
        debug('認証OK。');

        $pass = makeRandKey(); //パスワード生成

        //例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if ($stmt) {
            debug('クエリ成功。');

            //メールを送信
            $from = 'info@Instrment.com';
            $to = $_SESSION['auth_email'];
            $subject = '【Paddword再発行完了】｜Instrment';
            //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
            //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
            $comment = <<<EOT
本メールアドレス宛にPasswordの再発行を致しました。
下記のURLにて再発行Passwordをご入力頂き、Loginください。


再発行Password：{$pass}


※Login後、Passwordを変更してください。

////////////////////////////////////////
Instrment

////////////////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);

            //セッション削除
            session_unset();
            $_SESSION['msg_success'] = SUC08;

            debug('セッション変数の中身：' . print_r($_SESSION, true));

            header("Location:login.php"); //ログインページへ
            exit();
          } else {
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG07;
          }
        } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
      $err_msg['common'] = MSG18; //認証キーが違います
    }
  }
}
debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>
<?php
//タイトル表示関数
$siteTitle = 'Password再発行認証';
require('head.php');
?>

<?php
require('body.php');
?>
<div class="l-body__main">
  <!-- メニュー -->
  <?php
  require('header.php');
  ?>
  <p class="js-show-msg p-msg__slide" style="display:none;">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <!-- メインコンテンツ -->
  <div class="l-contents">

    <!-- main -->
    <div class="p-form__container">
      <h2 class="c-main__title">Password再発行 2/2</h2>
      <form action="" method="post" class="c-form p-form p-form__passRemindRecieve">
        <div class="">
          <p class="c-font__p">
            1.「認証キー」を入力してボタンを押してください。</p>
          <p class="c-font__p">2.【Password再発行完了】メールを送信します。</p>
          <p class="c-font__p">3.記載されている「再発行Password」を使用してLoginしてください。</p>
        </div>

        <!-- Common Error -->
        <div class="c-form-errorMsgContainer">
          <span class="c-form__areaMsg">
            <?php if (!empty($err_msg['common'])) {
              echo $err_msg['common'];
            } ?>
          </span>
        </div>

        <!-- key -->
        <div class="js-form__group">
          <label class="c-form__label <?php if (!empty($err_msg['token'])) {
                                        echo 'err';
                                      } ?>">
            <input class="c-label__input
              js-form-authentication-key-validate" placeholder="認証キー" type="text" name="token" value="<?php echo getFormData('token'); ?>">
          </label>
          <!-- エラー表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <?php if (!empty($err_msg['token'])) {
                echo $err_msg['token'];
              } ?>
            </span>
            <span class="js-help-block"></span>
          </div>
        </div>

        <!-- submit -->
        <div class="c-button__container">
          <input type="submit" class="far fa-paper-plane c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf1d8" disabled="disabled">
        </div>

        <!-- link -->
        <div class="">
          <a class="c-font__a" href="passRemindSend.php">&lt; 認証キーを再送する</a>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- footer -->
<?php
require('footer.php');
?>