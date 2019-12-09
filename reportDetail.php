<?php
//共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「点検内容詳細ページ reportDetail');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//================================
// 画面表示用データ取得
//================================
// reportテーブルのidをGETパラメータで取得し格納
$report_id = (!empty($_GET['report_id'])) ? $_GET['report_id'] : '';
// reportテーブルから指定したidの点検データを取得し格納
$viewData = getReportOne($report_id);

//点検報告者
$reporter_id = $viewData['user_id'];
//問合せ者
$questioner_id = $_SESSION['user_id'];

// パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
debug('取得した点検データ：' . print_r($viewData, true));
debug('取得したGETデータ：' . print_r($_GET, true));

// post送信されていた場合
if (!empty($_POST['submit'])) {
  debug('POST送信があります。');

  //ログイン認証
  require('auth.php');

  //連絡掲示板がすでに存在しているかチェック
  $getBord = getBord($viewData['user_id'], $report_id);
  debug('連絡掲示板情報：' . print_r($getBord, true));
  if (!empty($getBordId = $getBord['id'])) {
    $_SESSION['msg_success'] = SUC05;
    debug('連絡掲示板へ遷移します。');
    header("Location:msg.php?m_id=" . $getBordId); //連絡掲示板へ
    exit();
  } else {
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO bord (reporter_id,questioner_id,report_id, create_date) VALUES (:reporter_uid, :questioner_uid, :report_id, :date)';
      $data = array(':reporter_uid' => $viewData['user_id'], ':questioner_uid' => $_SESSION['user_id'], ':report_id' => $report_id, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        $_SESSION['msg_success'] = SUC05;
        debug('連絡掲示板へ遷移します。');
        header("Location:msg.php?m_id=" . $dbh->lastInsertID()); //連絡掲示板へ
        exit();
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
$siteTitle = '点検内容詳細';
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
    <!-- サイドバー -->
    <?php
    require('sidebar.php');
    ?>
    <!-- Main -->
    <div class="l-main">
      <h1 class="c-main__title">点検内容詳細</h1>
      <div class="p-form__container">
        <form action="" method="post" class="p-form p-form__reportDetail">
          <!-- タグ名など記載部 -->
          <div class="p-form__head">
            <i class="far fa-thumbs-up faa-vertical animated-hover icn-like js-click-like <?php if (isLike($_SESSION['user_id'], $viewData['id'])) {
                                                                                            echo 'active';
                                                                                          } ?>" aria-hidden="true" data-reportid="<?php echo sanitize($viewData['id']); ?>"></i>
            <span class="badge c-font__span">ReportID/<?php echo sanitize($viewData['id']); ?></span>
            <p class="c-font__p">
              <?php echo sanitize($viewData['plant']); ?>
              <?php echo sanitize($viewData['tag']); ?>
              <?php echo sanitize($viewData['symptoms']); ?></p>

          </div>
          <!-- 画像格納部 -->
          <div class="p-form__imgContainer">
            <!-- メイン画像 -->
            <div class="p-imgContainer__main">
              <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像：<?php echo sanitize($viewData['tag']); ?>" class="c-img__main js-switch-img-main">
            </div>
            <!-- サブ画像 -->
            <div class="p-imgContainer__sub">
              <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="画像1：<?php echo sanitize($viewData['tag']); ?>" class="c-img__sub js-switch-img-sub">
              <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="画像2：<?php echo sanitize($viewData['tag']); ?>" class="c-img__sub js-switch-img-sub">
            </div>
          </div>
          <!-- 点検内容詳細部 -->
          <div class="p-form__body">
            <ul>
              <li>担当者：<?php echo sanitize($viewData['staff']); ?></li>
              <li>点検日：<?php echo sanitize($viewData['testday']); ?></li>
              <li>報告日：<?php echo date('Y-m-d', strtotime(sanitize($viewData['create_date']))); ?></li>
              <li>点検内容：<?php echo sanitize($viewData['observation']); ?></li>
            </ul>
          </div>
          <!-- リンク先 -->
          <div class="p-form__foot">
            <?php
            if ($reporter_id !== $questioner_id) {
              ?>
              <!-- 点検者に連絡できる。自分で点検したものに連絡はできない -->
              <div class="c-button__container">
                <input name="submit" type="submit" class="c-button black" value="<?php echo sanitize($viewData['staff']); ?>さんに連絡する">
              </div>
            <?php
            } else {
              ?>
              <div class="c-font">
                <a class="c-font__a">自分で点検した計器です</a>
              </div>
            <?php
            }
            ?>
          </div>
          <!-- GETパラメーターを付加する -->
          <div class="c-font">
            <a class="c-font__a" href="history.php<?php echo appendGetParam(array('report_id')); ?>">&lt; 点検履歴一覧に戻る</a>
          </div>
          <div class="c-font">
            <a class="c-font__a" href="mypage.php">&lt; マイページに戻る</a>
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