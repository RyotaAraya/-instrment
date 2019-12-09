<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
// 画面処理
//==============================
// DBからユーザーデータを取得
debug('useridは：' . $_SESSION['user_id']);
$userData = getUser($_SESSION['user_id']);
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：profEdit.php：' . print_r($dbFormData, true));

//POST送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //変数にユーザー情報を代入
  $user_name = $_POST['user_name'];
  $tel = (!empty($_POST['tel'])) ? $_POST['tel'] : 0;
  $email = $_POST['email'];
  //画像をアップロードし、パスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  //画像をPOSTしていない
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

  //DBの情報と入力情報が異なる場合にバリデーションを行う
  if ($dbFormData['user_name'] !== $user_name) {
    //名前の最大文字数チェック
    validMaxLen($user_name, 'user_name');
  }
  if ((int) $dbFormData['tel'] !== $tel) { //DBから取得するデータをstringからint型にキャスト(型変換)して比較する。
    //TEL形式チェック
    validTel($tel, 'tel');
  }
  if ($dbFormData['email'] !== $email) {
    //emailの最大文字数チェック
    validMaxLen($email, 'email');
    if (empty($err_msg['email'])) {
      //emailの重複チェック
      validEmailDup($email);
    }
    //emailの形式チェック
    validEmail($email, 'email');
    //emailの未入力チェック
    validRequired($email, 'email');
  }
  //ゲストクラスは変更不可
  if (empty($err_msg) && $userData['class'] != 10) {
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users  SET user_name = :u_name, tel = :tel, email = :email, pic = :pic WHERE id = :u_id';
      $data = array(':u_name' => $user_name, ':tel' => $tel, ':email' => $email, ':u_id' => $dbFormData['id'], ':pic' => $pic);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        //メッセージ表示用
        //$_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移します。');
        $_SESSION['msg_success'] = $user_name . "さんの " . SUC02;
        header("Location:mypage.php"); //マイページへ
        exit();
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  } else if ($userData['class'] == 10) {
    //ゲストはパスワード変更できない
    debug('ゲスト機能制限');
    $err_msg['common'] = MSG19;
    $_SESSION['msg_success'] = MSG19 . ' ユーザー登録してみましょう!!';
  }
}


debug('profEdit.php 画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'Profile編集';
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
    <!-- サイドバー -->
    <?php
    require('sidebar.php');
    ?>


    <!-- Main -->
    <div class="l-main l-main__form">
      <h1 class="c-main__title">Profile編集</h1>
      <div class="p-form__container">
        <form action="" method="post" class="c-form p-form p-form__profEdit" enctype="multipart/form-data" style="width:;">


          <!-- Common Error 表示部 -->
          <div class="c-form-errorMsgContainer">
            <span class="c-form__areaMsg">
              <?php
              if (!empty($err_msg['common'])) echo $err_msg['common'];
              ?>
            </span>
          </div>


          <!--ID表示-->
          ID：<?php echo getFormData('id'); ?>


          <!--名前表示-->
          <div class="js-form__group">
            <label class="c-form__label <?php if (!empty($err_msg['user_name'])) echo 'err'; ?>">
              氏名
              <input class="c-label__input js-form-name-validate" type="text" name="user_name" placeholder="安室 透" value="<?php echo getFormData('user_name'); ?>">
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


          <!--電話番号表示-->
          <div class="js-form__group">
            <label class="c-form__label <?php if (!empty($err_msg['tel'])) echo 'err'; ?>">
              TEL
              <input class="c-label__input js-tel-validate" type="text" name="tel" placeholder="090-1111-1111" value="<?php if (!empty(getFormData('tel'))) {
                                                                                                                        echo getFormData('tel');
                                                                                                                      } ?>">
            </label>
            <!-- エラー表示部 -->
            <div class="c-form-errorMsgContainer">
              <span class="c-form__areaMsg">
                <?php
                if (!empty($err_msg['tel'])) echo $err_msg['tel'];
                ?>
              </span>
              <span class="js-help-block"></span>
            </div>
          </div>


          <!--Email表示-->
          <div class="js-form__group">
            <label class="c-form__label <?php if (!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input class="c-label__input js-form-email-validate" type="text" name="email" placeholder="google@gmail.com" value="<?php echo getFormData('email'); ?>">
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

          <!-- 画像 -->
          <div class="js-form__group">
            プロフィール画像
            <div class="c-form__imgDropContainer">
              <label class="c-form__label c-form__drop js-drop-area <?php if (!empty($err_msg['pic'])) echo 'err'; ?>" style="height:300px;line-height:370px;margin-top:10px;">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic" class="c-label__file js-file-input" style="height:300px;">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="c-label__img js-prev-img" style="<?php if (empty(getFormData('pic'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['pic'])) echo $err_msg['pic'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>
          </div>

          <!-- Submit -->
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