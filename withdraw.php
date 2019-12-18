<?php

//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アカウント削除ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//ユーザーIDを取得
debug('useridは：' . $_SESSION['user_id']);
//ユーザーIDをもとに重要なユーザー情報を取得
$userData = getUser($_SESSION['user_id']);
//========================
// 画面処理
//========================
// post送信されていた場合
if (!empty($_POST) && $userData['class'] != 10) {
  debug('POST送信があります。');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
    $data = array(':us_id' => $_SESSION['user_id']);
    $stmt = queryPost($dbh, $sql, $data);

    //クエリ成功の場合
    if ($stmt) {
      /*LogOut処理 POSTも初期化*/
      isLogout();
      debug('セッション変数の中身：' . print_r($_SESSION, true));
      debug('トップページへ遷移します。');
      header("Location:index.php");
      exit();
    } else {
      debug('クエリが失敗しました。');
      $err_msg['common'] = MSG07;
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

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'アカウント削除';
require('head.php');
?>
<p class="js-show-msg p-msg__slide" style="display:none;">
  <?php echo getSessionFlash('msg_success'); ?>
</p>
<body>

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>
  <?php
  require('body.php');
  ?>
  <div class="l-body__main">
    <!-- メインコンテンツ -->
    <div class="l-contents">
      <!-- サイドバー -->
      <?php
      require('sidebar.php');
      ?>
      <!-- Main -->
      <div class="l-main">
        <h1 class="c-main__title">退会</h1>
        <div class="p-form__container">
          <form name="withdrawform" action="" method="post" class="p-form p-form__withdraw js-withdraw__confirm">
            <ul class="p-use__menu">
              <li class="c-li p-use__list">退会すると再ログインできません。</li>
              <li class="c-li p-use__list">emailを再使用して新規アカウントで登録することは可能です。</li>
              <li class="c-li p-use__list">同じアカウントで再登録したい場合は管理者に連絡をお願いします。</li>
            </ul>
            <div class="c-button__container">
              <input type="submit" class="fas fa-user-minus c-button l-form__btn btn-gradient" value="&#xf503;" name="submit">
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