<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　連絡掲示板ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
$partnerUserId = '';
$partnerUserInfo = '';
$myUserInfo = '';
$reportInfo = '';
$viewData = '';
// 画面表示用データ取得
//================================
// GETパラメータを取得
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
// DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($m_id);
debug('getMsgsAndBord 取得したDBデータ：' . print_r($viewData, true));
// パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:mypage.php"); //マイページへ
}
// 点検情報を取得
$reportInfo = getReportOne($viewData[0]['report_id']);
debug('取得した点検データ：' . print_r($reportInfo, true));
// 点検情報が入っているかチェック
if (empty($reportInfo)) {
  error_log('エラー発生:点検情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
}
// viewDataから相手のユーザーIDを取り出す
$dealUserIds[] = $viewData[0]['reporter_id'];
$dealUserIds[] = $viewData[0]['questioner_id'];
debug('$dealUserIds[]：' . print_r($dealUserIds, true));
debug('自分のIDは：' . print_r($_SESSION['user_id'], true));
if (($key = array_search($_SESSION['user_id'], $dealUserIds)) !== false) {
  debug('$key：' . print_r($key, true));

  unset($dealUserIds[$key]);
}
$partnerUserId = array_shift($dealUserIds);
debug('取得した相手のユーザーID：' . $partnerUserId);
// DBから取引相手のユーザー情報を取得
if (isset($partnerUserId)) {
  debug('相手のユーザー情報：');
  $partnerUserInfo = getUser($partnerUserId);
}
// 相手のユーザー情報が取れたかチェック
if (empty($partnerUserInfo)) {
  error_log('エラー発生:相手のユーザー情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
}
// DBから自分のユーザー情報を取得
debug('自分のユーザー情報：');
$myUserInfo = getUser($_SESSION['user_id']);
// 自分のユーザー情報が取れたかチェック
if (empty($myUserInfo)) {
  error_log('エラー発生:自分のユーザー情報が取得できませんでした');
  header("Location:mypage.php"); //マイページへ
}

// post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');

  //ログイン認証
  require('auth.php');

  //バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  //最大文字数チェック
  validMaxLen($msg, 'msg', 200);
  //未入力チェック
  validRequired($msg, 'msg');

  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO message (bord_id, send_date, to_user, from_user, msg, create_date) VALUES (:b_id, :send_date, :to_user, :from_user, :msg, :date)';
      $data = array(':b_id' => $m_id, ':send_date' => date('Y-m-d H:i:s'), ':to_user' => $partnerUserId, ':from_user' => $_SESSION['user_id'], ':msg' => $msg, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        $_POST = array(); //postをクリア
        debug('連絡掲示板へ遷移します。');
        header("Location: " . $_SERVER['PHP_SELF'] . '?m_id=' . $m_id); //自分自身に遷移する
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '連絡掲示板';
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
    <?php echo getSessionFlash('msg_success');
    unset($_SESSION['msg_success']);
    ?>
  </p>

  <!-- メインコンテンツ -->
  <div class="l-contents">
    <?php
    require('sidebar.php');
    ?>
    <!-- Main -->
    <div class="l-main">
      <div class="p-messageBord">
        <h1 class="c-main__title">連絡掲示板</h1>
        <div class="p-message__info">
          <!-- 連絡先の画像 -->
          <div class="p-partner__avatar">
            <img src="<?php echo showImg(sanitize($partnerUserInfo['pic'])); ?>" alt="reporter" class="p-partner__img"><br>
            <!-- 連絡先の情報 -->
            <div class="p-partner__info">
              <p class="p-partner__name"><?php echo sanitize($partnerUserInfo['user_name']) ?></p>
              <ul class="c-ul">
                <li class="c-li"><a class="far fa-envelope" href="mailto:<?php echo sanitize($partnerUserInfo['email']); ?>"></a></li>
                <li class="c-li"><a class="fas fa-phone-square" href="tel:<?php echo sanitize($partnerUserInfo['tel']); ?>"></a></li>
              </ul>
            </div>
          </div>
          <!-- 点検した計器の情報 -->
          <div class="p-report__info">
            <div class="p-info__data">
              <ul class="c-ul">
                <li class="c-li">プラント：<?php echo sanitize($reportInfo['plant']); ?></li>
                <li class="c-li">タグ：<?php echo sanitize($reportInfo['tag']); ?></li>
                <li class="c-li">不具合内容：<?php echo sanitize($reportInfo['symptoms']); ?></li>
                <li class="c-li">点検日：<?php echo date('Y/m/d', strtotime(sanitize($reportInfo['testday']))); ?></li>
                <li class="c-li">担当者：<?php echo sanitize($reportInfo['staff']); ?></li>
                <li class="c-li">所感：<?php echo sanitize($reportInfo['observation']); ?></li><br>
                <li>連絡開始日：<?php echo date('Y/m/d', strtotime(sanitize($viewData[0]['create_date']))); ?></li>
              </ul>
            </div>
            <div class="p-info__img">
              <img src="<?php echo showImg(sanitize($reportInfo['pic1'])); ?>" alt="" height="120px" width="auto">
            </div>
          </div>
        </div>
        <!-- sp専用のアバター画面 -->
        <div class="p-message__infoSp">
          <div class="p-partner__avatar">
            <img src="<?php echo showImg(sanitize($partnerUserInfo['pic'])); ?>" alt="reporter" class="p-partner__img">
            <!-- 連絡先の情報 -->
            <div class="p-partner__info">
              <ul class="c-ul message">
                <li class="c-li message"><?php echo sanitize($partnerUserInfo['user_name']) ?></li>
                <li class="c-li"><a class="far fa-envelope" href="mailto:<?php echo sanitize($partnerUserInfo['email']); ?>"></a></li>
                <li class="c-li"><a class="fas fa-phone-square" href="tel:<?php echo sanitize($partnerUserInfo['tel']); ?>"></a></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- メッセージ -->
        <div class="c-scroll__container p-message__bord js-scroll-bottom">
          <?php
          if (!empty($viewData)) {
            foreach ($viewData as $key => $val) {
              if (!empty($val['from_user']) && $val['from_user'] == $partnerUserId) {
                ?>
                <div class="p-message__cnt p-left__message">
                  <div class="p-message__avatar">
                    <img src="<?php echo sanitize(showImg($partnerUserInfo['pic'])); ?>" alt="reporter_avatarImg" class="p-avatar__img">
                  </div>
                  <!-- message -->
                  <div class="p-msg__inrTxt p-left__inrTxt">
                    <span class="p-left__triangle"></span>
                    <p><?php echo sanitize($val['msg']); ?></p>
                  </div>
                  <!-- send date -->
                  <div class="p-message__date"><?php echo date('Y/m/d', strtotime(sanitize($val['send_date']))); ?></div>
                  <div class="p-message__date"><?php echo date('h:i', strtotime(sanitize($val['send_date']))); ?></div>
                </div>
              <?php
                  } else {
                    ?>
                <div class="p-message__cnt p-right__message">
                  <div class="p-message__avatar p-right__avatar">
                    <img src="<?php echo sanitize(showImg($myUserInfo['pic'])); ?>" alt="questioner_avatarImg" class="p-avatar__img">
                  </div>
                  <p class="p-msg__inrTxt p-right__inrTxt">
                    <span class="p-right__triangle"></span>
                    <?php if (!empty($val["msg"])) {
                            echo sanitize($val['msg']);
                          } else echo "・・・" ?>
                  </p>
                  <div class="p-message__date date-right"><?php if (!empty($val["send_date"])) echo date('Y/m/d', strtotime(sanitize($val['send_date']))); ?></div>
                  <div class="p-message__date date-right"><?php if (!empty($val["send_date"])) echo date('h:i', strtotime(sanitize($val['send_date']))); ?></div>
                </div>
            <?php
                }
              }
            } else {
              ?>
            <p style="text-align:center;line-height:20;">メッセージ投稿はまだありません</p>
          <?php
          }
          ?>
        </div>


        <!-- submit button -->
        <div class="p-newMessage__sendArea">
          <form action="" method="post">
            <div class="js-form__group">
              <label class="c-form__label">
                <textarea class="p-message__textarea js-form-messagebord-validate js-count-text" name="msg" cols="30" rows="3"></textarea>


                <div class="p-msg__container">
                  <div class="p-msg__counter js-text-counter">
                    <span class="js-show-count-text">0</span>/200
                  </div>

                  <!-- error表示部 -->
                  <div class="p-form-errorMsgContainer">
                    <span class="c-form__areaMsg">
                      <?php
                      if (!empty($err_msg['msg'])) echo $err_msg['msg'];
                      ?>
                    </span>
                    <span class="p-msg__err js-help-block"></span>
                  </div>

                </div>
              </label>
            </div>
            <div class="c-button__container">
              <input type="submit" class="far fa-paper-plane c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf1d8;" disabled="disabled">
            </div>
          </form>
        </div>


      </div>
    </div>
  </div>
</div>
<!-- footer -->
<?php
require('footer.php');
?>