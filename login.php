<?php

//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ログイン');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=======================
//ログイン画面処理
//=======================
//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');

  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false; //ショートハンド(略記法)という書き方

  //emailの形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validMaxLen($email, 'email');

  //パスワードチェック 半角英数字、最大文字数、最小文字数チェック
  validPass($pass, 'pass');

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    //例外処理
    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql1 = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $sql2 = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 1';

      $data = array(':email' => $email);
      //クエリ実行
      $stmt1 = queryPost($dbh, $sql1, $data);
      $stmt2 = queryPost($dbh, $sql2, $data);
      //クエリ結果の値を取得
      $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
      $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);


      debug('クエリ結果1の中身 $result1：login.php：' . print_r($result1, true));
      debug('クエリ結果2の中身 $result2：login.php：' . print_r($result2, true));


      //パスワード照合
      //DBに保存されているハッシュ化されたパスワードと入力したパスワードを比較する
      if (!empty($result1) && password_verify($pass, $result1['password'])) {
        debug('パスワードがマッチしました。');

        //ログイン有効期限(デフォルトを1時間とする)
        $sesLimit = 60 * 60;
        //最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time(); //time関数は1970年1月1日 00:00:00を0として、1秒経過するごとに1ずつ増加させた値

        //ログイン保持にチェックがある場合
        if ($pass_save) {
          debug('ログイン保持にチェックがあります。');
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          debug('ログイン保持にチェックなし。');
          //次回からログイン保持しないため、ログイン有効期限を1時間にセット
          $_SESSION['login_limit'] = $sesLimit;
        }
        //ユーザーIDを格納
        $_SESSION['user_id'] = $result1['id'];

        debug('セッション変数の中身：' . print_r($_SESSION, true));
        debug('マイページへ遷移します。');
        header("Location:mypage.php"); //マイページへ
        exit();
      } else if (!empty($result2) && password_verify($pass, array_shift($result2))) {
        debug('パスワードがアンマッチです。');
        $err_msg['email'] = '退会済みです再登録してください。';
      } else {
        debug('パスワードがアンマッチです。');
        $err_msg['common'] = MSG09;
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG09;
    }
  }
}
debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>
<?php
//タイトル表示関数
$siteTitle = 'Login';
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
  <p class="js-show-msg p-msg__slide" style="display:none;">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <!-- メインコンテンツ -->
  <div class="l-contents">

    <!-- Main -->
    <div class="p-form__container">
      <h1 class="c-main__title">Login</h1>
      <form action="" method="post" class="c-form p-form p-form__login js-formdata-localStorage">
        <div class="c-form-errorMsgContainer">
          <span class="c-form__areaMsg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </span>
        </div>

        <!-- Email -->
        <div class="js-form__group">
          <label class="c-form__label<?php if (!empty($err_msg['email'])) echo 'err'; ?>">
            <p>Email</p>
            <input class="c-label__input js-form-email-validate" autocomplete="off" type="text" name="email" placeholder="google@gmail.com">
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
            <!-- password表示/非表示切替 -->
            <label class="c-mode__toggle">
              <input type="checkbox" class="c-blind__checkbox js-pass-blind-checkbox">
              <span class="c-blind__icn"></span>
            </label>
          </label>
          <!-- error表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <?php
              if (!empty($err_msg['pass'])) echo $err_msg['pass'];
              ?>
            </span>
            <span class="js-help-block"></span>
          </div>
        </div>

        <!-- Submitボタン -->
        <div class="c-button__container">
          <input type="submit" class="fas fa-sign-in-alt c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf2f6;" disabled="disabled">
        </div>

        <label class="c-form__label">
          <input type="checkbox" name="pass_save">
          <span>次回ログインを省略する</span>
        </label>

        <div class="">
          パスワードを忘れた方は
          <a class="c-font__a" href="passRemindSend.php">コチラ</a>
        </div>

      </form>
    </div>
  </div>
</div>
<!-- footer -->
<?php
require('footer.php');
?>