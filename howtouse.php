<?php

//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　使い方　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン時にサイドバーを表示し、未ログイン時は非表示にする
$login_flg = 0;

if (!empty($_SESSION['login_date'])) {
  debug('ログイン済みユーザーです。');

  //現在日時が最終ログイン日時＋有効期限を超えていた場合
  if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
    debug('ログイン有効期限オーバーです。');

    /*LogOut処理 POSTも初期化*/
    isLogout();

    //リロード
    header('Location:howtouse.php');
    exit();
  } else {
    debug('ログイン有効期限内です。');
    $login_flg = 1;
    //最終ログイン日時を現在日時に更新する
    $_SESSION['login_date'] = time();
  }
} else {
  debug('未ログインユーザーです。');
  $login_flg = 0;
}


debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
// head.php
$siteTitle = '使い方';
require('head.php');
?>
<?php
// body.php
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
      <div class="l-main__use">
        <h1 class="c-main__title">使い方と仕様について</h1>

        <div class="p-use__container">
          <h2 class="c-sub__title">プロフィール</h2>
          <ul class="p-use__menu">
            <li class="c-li p-use__list">アカウントを新規作成しログインした後、プロフィール編集ページから追加でプロフィールデータを入力をしてください。</li>
            <li class="c-li p-use__list">写真を登録すると連絡掲示板のやり取りページなどに反映されます。</li>
            <li class="c-li p-use__list">パスワード変更は同じパスワードに変更できません。</li>
            <li class="c-li p-use__list">パスワード変更後、登録メールアドレス宛に通知メールが届きます。</li>
          </ul>
        </div>

        <div class="p-use__container">
          <h2 class="c-sub__title">パスワードを忘れた場合</h2>
          <ul class="p-use__menu">
            <li class="c-li p-use__list">ログインフォームの「パスワードを忘れた場合は<a class="c-li__a" href="passRemindSend.php">コチラ</a>から」からパスワードの再発行が可能です。</li>
            <li class="c-li p-use__list">パスワード変更処理は終始同じブラウザからでないと実行できません。</li>
            <li class="c-li p-use__list">メールアドレスとパスワード両方忘れた場合は<a class="c-li__a" href="contact.php">管理者</a>に連絡をお願いします。</li>
          </ul>
        </div>

        <div class="p-use__container">
          <h2 class="c-sub__title">レポート作成・編集</h2>
          <ul class="p-use__menu">
            <li class="c-li p-use__list">作成後、上司に作成内容が書かれたメールが届きます。</li>
            <li class="c-li p-use__list">編集後、上司に編集内容が書かれたメールが届きます。</li>
            <li class="c-li p-use__list">レポートは一時保存できませんが、作成後は都度編集可能です。</li>
            <li class="c-li p-use__list">他の方が作成したレポートは編集できません。レポート専用の連絡掲示板で連絡可能です。</li>
          </ul>
        </div>

        <div class="p-use__container">
          <h2 class="c-sub__title">退会</h2>
          <ul class="p-use__menu">
            <li class="c-li p-use__list">同じアカウントで再度ログインできません。emailは同じ物を使用可能です。</li>
            <li class="c-li p-use__list">同じアカウントで再登録したい場合は<a class="c-li__a" href="contact.php">管理者</a>に連絡をお願いします。</li>
          </ul>
        </div>

        <!-- TODO:管理者へのCONTACTページ作成 -->
        <!-- TODO:上司が固定メールアドレスのため、作成者から自動的に判別できるようにする。 -->
      </div>
    </div>
  </div>
  <!-- footer -->
  <?php
  require('footer.php');
  ?>