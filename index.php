<?php

//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('HOMEページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
debug('login'.print_r($_SESSION, true));

if (!empty($_SESSION['login_date'])) {
  debug('ログイン済みユーザーです。');
  //現在日時が最終ログイン日時＋有効期限を超えていた場合
  if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
    debug('ログイン有効期限オーバーです。');

    /*LogOut処理 POSTも初期化*/
    isLogout();

    //リロードする
    header('Location:index.php');
    exit();
  } else {
    debug('ログイン有効期限内です。');
    //最終ログイン日時を現在日時に更新する
    $_SESSION['login_date'] = time();
  }
} else {
  debug('未ログインユーザーです。');
}

?>
<?php
//タイトル表示関数
$siteTitle = 'HOME';
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
  <!-- トップバナー -->
  <div class="l-topbaner">
      <img id="js-topbaner-slideshow" class="l-topbaner__img" src="images/topbaner01.jpg" alt="topbaner"/>
  </div>

</div>
<!-- footer -->
<?php
require('footer.php');
?>