<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
// 画面処理
//==============================
//ユーザーIDを取得
debug('useridは：' . $_SESSION['user_id']);
//ユーザーIDをもとに重要なユーザー情報を取得
$userData = getUserInportantData($_SESSION['user_id']);

//debug('取得したユーザー情報' . print_r($userData, true));

//POST送信されたていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  //未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if (empty($err_msg)) {
    debug('未入力チェックOK');

    validPass($pass_old, 'pass_old');
    validPass($pass_new, 'pass_new');
    validPass($pass_new_re, 'pass_new_re');

    //古いパスワードが合っているか確認 DBに保存されているパスワードと比較する
    if (!password_verify($pass_old, $userData['password'])) {
      $err_msg['pass_old'] = MSG12;
    }
    //新しいパスワードと古いパスワードが同じかチェック
    if ($pass_old === $pass_new) {
      $err_msg['common'] = MSG13;
    }
    //新しいパスワードと新しいパスワードの再入力が合っているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    //ゲストクラスは変更不可
    if (empty($err_msg) && $userData['class'] != 10) {
      debug('バリデーションOK');

      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if ($stmt) {
          //メッセージ表示用
          $_SESSION['msg_success'] = $user_name . 'さんの' . SUC01;
          //メールを送信
          $user_name = ($userData['user_name']) ? $userData['user_name'] : '名無し';
          $from = 'camerondiaztest@gmail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知 INSTRMENT';
          //EOT(end of textの略)何でもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など入れてはダメ。
          //EOT内の半角空白もそのまま半角空白として扱われるためインデントしないこと。
          $comment = <<<EOT
{$user_name} さん

パスワードが変更されました。
EOT;
          sendMail($from, $to, $subject, $comment);
          header("Location:mypage.php");
          exit();
        }
      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    } else if ($userData['class'] == 10) {
      //ゲストはパスワード変更できない
      debug('ゲスト機能制限');
      $err_msg['common'] = MSG19;
      $_SESSION['msg_success'] = MSG19 . ' ユーザー登録してみましょう!!';
    }
  }
}


?>

<?php
//タイトル表示関数
$siteTitle = 'Password変更';
require('head.php');
?>
<?php
require('body.php');
?>
<p class="js-show-msg p-msg__slide" style="display:none;">
  <?php echo getSessionFlash('msg_success'); ?>
</p>
<div class="l-body__main">
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div class="l-contents">
    <!-- サイドバー -->
    <?php
    require('sidebar.php');
    ?>

    <!-- Main -->
    <div class="l-main">
      <h2 class="c-main__title">Password変更</h2>
      <div class="p-form__container">
        <form action="" method="post" class="c-form p-form p-form__passEdit js-formdata-localStorage">
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg js-oldnew-passwordMatchFlg">
              <?php
              echo getErrMsg('common');
              ?>
            </span>
          </div>

          <!-- 古いパスワード -->
          <div class="js-form__group">
            <label class="c-form__label <?php if (!empty($err_msg['pass_old'])) echo 'err'; ?>">
              <p>Current Password</p>
              <input class="c-label__input js-form-oldpassword-validate js-oldpassword-blind" type="password" name="pass_old">
              <!-- password表示/非表示切替 -->
              <label class="c-mode__toggle">
                <input type="checkbox" class="c-blind__checkbox js-oldpass-blind-checkbox">
                <span class="c-blind__icn"></span>
              </label>
            </label>
            <!-- エラー表示部 -->
            <div class="c-form-errorMsgContainer">
              <span class="c-form__areaMsg js-php-error-msg">
                <?php
                echo getErrMsg('pass_old');
                ?>
              </span>
              <span class="js-help-block"></span>
            </div>
          </div>

          <!-- 新しいパスワード -->
          <div class="js-form__group">
            <label class="c-form__label <?php if (!empty($err_msg['pass_new'])) echo 'err'; ?>">
              <p>New Password</p>
              <input class="c-label__input js-form-password-validate js-password-blind" type="password" name="pass_new">
              <!-- password表示/非表示切替 -->
              <label class="c-mode__toggle">
                <input type="checkbox" class="c-blind__checkbox js-pass-blind-checkbox">
                <span class="c-blind__icn"></span>
              </label>
            </label>
            <div class="c-form-errorMsgContainer">
              <span class="c-form__areaMsg js-php-error-msg">
                <?php
                echo getErrMsg('pass_new');
                ?>
              </span>
              <span class="js-help-block"></span>
            </div>
          </div>

          <!-- 新しいパスワード確認 -->
          <div class="js-form__group js-passwordMatchFlg">
            <label class="c-form__label <?php if (!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
              <p>Confirm New Password</p>
              <input class="c-label__input js-form-repassword-validate js-repassword-blind" type="password" name="pass_new_re">
              <!-- password表示/非表示切替 -->
              <label class="c-mode__toggle">
                <input type="checkbox" class="c-blind__checkbox js-repass-blind-checkbox">
                <span class="c-blind__icn"></span>
              </label>
            </label>
            <div class="c-form-errorMsgContainer">
              <span class="c-form__areaMsg js-php-error-msg">
                <?php
                echo getErrMsg('pass_new_re');
                ?>
              </span>
              <span class="js-help-block"></span>
            </div>
          </div>


          <div class="c-button__container">
            <input type="submit" class="far fa-edit c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf044">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- footer -->
<?php
require('footer.php');
?>