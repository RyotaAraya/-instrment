<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行　認証キーを発行しメール送信　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人が使う画面なので）

//================================
// 画面処理
//================================
//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //変数にPOST情報代入
  $email = $_POST['email'];

  //未入力チェック
  validRequired($email, 'email');

  if (empty($err_msg)) {
    debug('未入力チェックOK');

    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxLen($email, 'email');

    if (empty($err_msg) && $email !== 'gest@gmail.com') {
      debug('バリデーションOK');

      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        debug('result：' . print_r($result, true));

        // EmailがDBに登録されている場合
        if ($stmt && array_shift($result)) {
          debug('クエリ成功。DB登録あり。');
          $_SESSION['msg_success'] = SUC03;

          $auth_key = makeRandKey(); //認証キー生成

          //メールを送信
          $from = 'info@Instrment.com';
          $to = $email;
          $subject = '【Password再発行認証キー】｜Instrment';
          //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。


Password再発行認証キー入力ページ
h


認証キー：{$auth_key}



※認証キーの有効期限は30分となります


認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。

////////////////////////////////////////
Instrment

////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);

          //認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time() + (60 * 30); //現在時刻より30分後のUNIXタイムスタンプを入れる
          debug('セッション変数の中身：' . print_r($_SESSION, true));

          header("Location:passRemindRecieve.php"); //認証キー入力ページへ
          exit();
        } else {
          debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
          $err_msg['common'] = MSG07;
        }
      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }else if ($email === 'gest@gmail.com') {
      //ゲストはパスワード変更できない
      debug('ゲスト機能制限');
      $err_msg['common'] = MSG19;
      $_SESSION['msg_success'] = MSG19.' ユーザー登録してみましょう!!';
    }
  }
}
debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>
<?php
$siteTitle = 'Password再発行認証キー送信';
require('head.php');
?>
<p class="js-show-msg p-msg__slide" style="display:none;">
  <?php echo getSessionFlash('msg_success'); ?>
</p>
<?php
require('body.php');
?>
<div class="l-body__main">
  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div class="l-contents">

    <!-- Main -->
    <div class="p-form__container">
      <h2 class="c-main__title">Password再発行 1/2</h2>
      <form action="" method="post" class="c-form p-form p-form__passRemindSend">
        <p class="c-font__p">1.ユーザー登録しているメールアドレスを入力してください。</p>
        <p class="c-font__p">2.メールアドレス宛に「Password再発行認証キー」を送信します。</p>

        <!-- Common Error -->
        <div class="c-form-errorMsgContainer">
          <span class="c-form__areaMsg">
            <?php
            echo $err_msg['common'];
            ?>
          </span>
        </div>

        <!-- Email -->
        <div class="js-form__group">
          <label class="c-form__label <?php if (!empty($err_msg['email'])) echo 'err'; ?>">
            <p>Email</p>
            <input class="c-label__input js-form-email-validate" type="text" name="email" placeholder="google@gmail.com">
          </label>
          <!-- エラー表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <span class="js-set-msg-email"></span>
              <?php
              echo $err_msg['email'];
              ?>
            </span>
            <span class="js-help-block">
          </div>
        </div>

        <!-- submit -->
        <div class="c-button__container">
          <input type="submit" class="far fa-paper-plane c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf1d8" disabled="disabled">
        </div>
        <div class="">
          <a class="c-font__a" href="login.php">&lt; ログインページに戻る</a>
        </div>
      </form>
    </div>

  </div>
</div>
<!-- footer -->
<?php
require('footer.php');
?>