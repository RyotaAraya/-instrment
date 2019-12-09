<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「CONTACT');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
//ログイン時にサイドバーを表示し、未ログイン時は非表示にする
$login_flg = false;
$_POST = array();

if (!empty($_SESSION['login_date'])) {
  debug('ログイン状態を確認します');

  //現在日時が最終ログイン日時＋有効期限を超えていた場合
  if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
    debug('ログイン有効期限オーバーです。');

    /*LogOut処理 POSTも初期化*/
    isLogout();

    //リロード
    header('Location:contact.php');
    exit();
  } else {
    debug('ログイン有効期限内です。');
    $login_flg = true;
    //最終ログイン日時を現在日時に更新する
    $_SESSION['login_date'] = time();
  }
} else {
  debug('未ログインユーザーです。');
  $login_flg = false;
}

//==============================
// 画面処理
//==============================
// DBからユーザーデータを取得
if ($login_flg) {
  debug('useridは：' . $_SESSION['user_id']);
  $dbFormData = getUser($_SESSION['user_id']);

  debug('取得したユーザー情報：contact.php：' . print_r($dbFormData, true));
}
//POST送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //ログイン済みユーザーのバリデーション
  if (!empty($dbFormData)) {
    //変数にユーザー情報を代入
    $user_name = $dbFormData['user_name'];
    $email = $dbFormData['email'];
    $message = $_POST['message'];

    //DBの情報と入力情報が異なる場合にバリデーションを行う
    if ($dbFormData['user_name'] !== $user_name) {
      //名前の最大文字数チェック
      validMaxLen($user_name, 'user_name');
    }

    if ($dbFormData['email'] !== $email) {
      //emailの最大文字数チェック
      validMaxLen($email, 'email');
      //emailの形式チェック
      validEmail($email, 'email');
      //emailの未入力チェック
      validRequired($email, 'email');
    }
    
    //messageの文字数チェック
    validMinLen($message, 'message', 10);
    validMaxLen($message, 'message', 200);
  } else {
    //未ログインユーザーのバリデーション

    //変数にユーザー情報を代入
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    //name
    //文字数チェック
    validMaxLen($user_name, 'user_name');
    validRequired($user_name, 'user_name');

    //mail
    //文字数チェック
    validMaxLen($email, 'email');
    //形式チェック
    validEmail($email, 'email');
    //未入力チェック
    validRequired($email, 'email');

    //message
    //文字数チェック
    validMaxLen($message, 'message', 100);
    //未入力チェック
    validRequired($message, 'message');
  }
  debug('err：' . print_r($err_msg, true));


  if (empty($err_msg)) {
    debug('バリデーションOKです。');


    //変数にユーザー情報を代入
    $contact_name = $user_name;
    $contact_from = $email;
    $contact_message = $message;

    //contact mail 送信
    //引数 name,email,message
    if (contactMail($contact_name, $contact_from, $contact_message, 'message')) {
      debug('mail送信完了');
      //問合せメール送信しました。
      $_SESSION['msg_success'] = SUC06;

      $_POST = array();
    } else {
      debug('mail送信失敗');
      $err_msg['common'] = MSG07;
    }
  }
}


debug('contact.php 画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'CONTACT';
require('head.php');
?>

<!--  パスワード変更サクセス時に表示-->
<p class="js-show-msg p-msg__slide" style="display:none">
  <?php echo getSessionFlash('msg_success');
  unset($_SESSION['msg_success']);
  ?>
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
      <!-- サイドバー -->
      <?php
      if ($login_flg) {
        require('sidebar.php');
      }
      ?>
      <!-- Main -->
      <div class="l-main">
        <h1 class="c-main__title">CONTACT</h1>
        <h2 class="c-sub__title p-subtitle__contact">管理者への問合せ </h2>
        <div class="p-form__container">
          <form action="" method="post" class="c-form p-form p-form__profEdit">

            <div class="c-form-errorMsgContainer">
              <span class="c-form__areaMsg">
                <?php
                if (!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
              </span>
            </div>

            <?php
            if ($login_flg) {
              ?>
              <!-- ログイン済みユーザー -->
              <div class="">
                <p class="c-font__p">ID：<span class="js-contact-logincheck"><?php echo getFormData('id'); ?></span></p>
                <p class="c-font__p">氏名：<?php echo getFormData('user_name'); ?></p>
                <p class="c-font__p">Email：<?php echo getFormData('email'); ?></p>
              </div>
            <?php
            } else { ?>
              <!-- 未ログインユーザー -->
              <!--名前表示-->
              <div class="js-form__group">
                <label class="c-form__label <?php if (!empty($err_msg['user_name'])) echo 'err'; ?>">
                  氏名
                  <input class="c-label__input input js-form-name-validate" type="text" name="user_name" value="<?php echo getFormData('user_name'); ?>">
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

              <!--Email表示-->
              <div class="js-form__group">
                <label class="c-form__label <?php if (!empty($err_msg['email'])) echo 'err'; ?>">
                  Email
                  <input class="c-label__input input js-form-email-validate" type="text" name="email" value="<?php echo getFormData('email'); ?>">
                </label>
                <!-- エラー表示部 -->
                <div class="c-form-errorMsgContainer">
                  <span class="c-form__areaMsg">
                    <?php
                      if (!empty($err_msg['email'])) echo $err_msg['email'];
                      ?>
                  </span>
                  <span class="js-help-block"></span>
                </div>
              </div>

            <?php } ?>


            <!-- Message表示 -->
            <div class="js-form__group">
              <label class="c-form__label <?php if (!empty($err_msg['message'])) echo 'err'; ?>">
                <p class="c-font__p">お問い合わせ内容</p>
                <textarea class="c-label__textarea contact-form js-form-message-validate js-count-text" name="message" cols="50" rows="2"></textarea>
              </label>
              <!-- エラー表示部 -->
              <div class="p-message-counter js-text-counter">
                <span class="js-show-count-text">0</span>/200
              </div>
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['message'])) echo $err_msg['message'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>

            <div class="c-button__container">
              <input type="submit" class="far fa-paper-plane c-button l-form__btn btn-gradient js-form-submit" disabled="disabled" value="&#xf1d8">
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