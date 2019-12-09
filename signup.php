<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
if (!empty($_SESSION['login_date'])) {
  debug('ログイン済みユーザーです。');
  //現在日時が最終ログイン日時＋有効期限を超えていた場合
  if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
    debug('ログイン有効期限オーバーです。');

    /*LogOut処理 POSTも初期化*/
    isLogout();

    //リロードする
    header('Location:index.php');
  } else {
    debug('ログイン有効期限内です。');
    //最終ログイン日時を現在日時に更新する
    $_SESSION['login_date'] = time();
    header('Location:mypage.php');
  }
} else {
  debug('未ログインユーザーです。');
}

//post送信されていた場合
if (!empty($_POST)) {

  //変数にユーザー情報を代入
  $user_name = $_POST['user_name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  //emailの形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validMaxLen($email, 'email');
  //email重複チェック
  validEmailDup($email);

  //パスワードチェック 半角英数字、最大文字数、最小文字数チェック
  validPass($pass, 'pass');
  validPass($pass_re, 'pass_re');

  //未入力チェック
  validRequired($user_name, 'user_name');
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  //パスワードとパスワード再入力が合っているかチェック
  validMatch($pass, $pass_re, 'pass_re');

  if (empty($err_msg)) {

    //例外処理
    try {
      // DBへ接続 function.php
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO users (user_name,email,password,login_time,create_date) VALUES(:user_name,:email,:pass,:login_time,:create_date)';
      $data = array(
        ':user_name' => $user_name, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT),
        ':login_time' => date('Y-m-d H:i:s'),
        ':create_date' => date('Y-m-d H:i:s')
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('クエリ実行後の$stmt:' . print_r($stmt, true));

      // クエリ成功の場合
      if ($stmt) {
        //メッセージを表示する
        $_SESSION['msg_success'] = $user_name . "さん    よ う こ そ！";
        //ログイン有効期限（デフォルトを１時間とする）
        $sesLimit = 60 * 60;
        // 最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time();
        $_SESSION['login_limit'] = $sesLimit;

        // ユーザーIDを格納
        //dbConnect した時に、PDOオブジェクトを取ってきてdbhandlerという変数につめている。その中にはlastInsert ID という関数が入っている。オブジェクトなので、その中のまとまった処理、メソッドが入っている。それを呼び出すだけで、直前で insert したレコードの ID を取得する。セッションに詰める。
        $_SESSION['user_id'] = $dbh->lastInsertId();

        debug('セッション変数の中身：' . print_r($_SESSION, true));

        header("Location:mypage.php"); //マイページへ
        exit();
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>
<?php
//タイトル表示関数
$siteTitle = 'Register';
require('head.php');
?>
<?php
require('body.php');
?>
<div class="l-body__main">
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <!-- メインコンテンツ -->
  <div class="l-contents">

    <!-- Main -->
    <div class="p-form__container">
      <h1 class="c-main__title">Register</h1>
      <form action="" method="post" class="c-form p-form p-form__signup js-formdata-localStorage">
        <div class="c-form-errorMsgContainer">
          <span class="c-form__areaMsg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </span>
        </div>

        <!-- ユーザー名 -->
        <div class="js-form__group">
          <label class="c-form__label <?php if (!empty($err_msg['user_name'])) echo 'err'; ?>">
            <p>Name</p>
            <input class="c-label__input input js-form-name-validate" type="text" name="user_name" placeholder="吉田 松蔭">
          </label>
          <!-- エラー表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <?php
              if (!empty($err_msg['user_name'])) echo $err_msg['user_name'];
              ?>
            </span>
            <span class="js-help-block"></span>
          </div>
        </div>

        <!-- Email -->
        <div class="js-form__group">
          <label class="c-form__label <?php if (!empty($err_msg['email'])) echo 'err'; ?>">
            <p>Email</p>
            <input class="c-label__input input js-form-email-validate" autocomplete="off" type="text" name="email" placeholder="google@gmail.com">
          </label>
          <!-- エラー表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <span class="js-set-msg-email"></span>
              <?php
              if (!empty($err_msg['email'])) echo $err_msg['email'];
              ?>
            </span>
            <span class="js-help-block"></span>
          </div>
        </div>

        <!-- Password -->
        <div class="js-form__group">
          <label class="c-form__label <?php if (!empty($err_msg['pass'])) echo 'err'; ?>">
            <p>Password</p>
            <input class="c-label__input js-form-password-validate js-password-blind" type="password" name="pass" placeholder="6文字以上の英数字">
            <!-- Password表示/非表示切替 -->
            <label class="c-mode__toggle">
              <input type="checkbox" class="c-blind__checkbox js-pass-blind-checkbox">
              <span class="c-blind__icn"></span>
            </label>
          </label>
          <!-- erorr表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <?php
              if (!empty($err_msg['pass'])) echo $err_msg['pass'];
              ?>
            </span>
            <span class="js-help-block"></span>
          </div>
        </div>

        <!-- Password再入力 -->
        <div class="js-form__group js-passwordMatchFlg">
          <label class="c-form__label <?php if (!empty($err_msg['pass_re'])) echo 'err'; ?>">
            <p>Confirm password</p>
            <input class="c-label__input js-form-repassword-validate js-repassword-blind" type="password" name="pass_re" placeholder="6文字以上の英数字">
            <!-- password表示/非表示切替 -->
            <label class="c-mode__toggle">
              <input type="checkbox" class="c-blind__checkbox js-repass-blind-checkbox">
              <span class="c-blind__icn"></span>
            </label>
          </label>
          <!-- error表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <?php
              if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
              ?>
            </span>
            <span class="js-help-block"></span>
          </div>
        </div>

        <!-- Submitボタン -->
        <div class="c-button__container">
          <input type="submit" class="fas fa-user-plus c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf234;" disabled="disabled">
        </div>
      </form>
    </div>
  </div>
</div>
<!-- footer -->
<?php
require('footer.php');
?>