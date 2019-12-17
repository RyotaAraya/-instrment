<?php

//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('計器一覧ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//画面処理
//画面表示用データ取得
//カレントページのGETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは1ページ目
//debug('currentPageNum：' . $currentPageNum);
//プラント
$plantSort = (!empty($_GET['plant_sort'])) ? $_GET['plant_sort'] : '';
//ソート順
$dateSort = (!empty($_GET['date_sort'])) ? $_GET['date_sort'] : '';

//パラメータに不正な値が入っていないかチェック
if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
  exit();
}
//表示件数
$listSpan = 10;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan); //1ページ目なら(1-1)*10=0 , 2ページ目なら(2-1)*10=10
debug('currentMinNum：' . $currentMinNum);

//DBから点検データを取得
$dbReportData = getReportList($currentMinNum, $plantSort, $dateSort);
debug('点検データ一覧：' . print_r($dbReportData, true));

//DBからプラントデータを取得
$dbPlantData = getplant();
//debug('プラントデータ：'.print_r($dbPlantData,true));



debug('画面表示終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>
<?php
$siteTitle = '計器一覧';
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
      <h1 class="c-main__title">計器一覧</h1>
      <!-- serch -->
      <section class="p-search__container">
        <div class="p-search__title">
          <div class="p-search__left">
            <span class="p-serch__totalNum"><?php echo sanitize($dbReportData['total']); ?></span><span class="p-serch__announcement">件 Hit</span>
          </div>
          <div class="p-search__right">
            <span class="p-serch__num"><?php echo (!empty($dbReportData['data'])) ? $currentMinNum + 1 : 0; ?>〜</span><span class="p-serch__num"><?php echo $currentMinNum + count($dbReportData['data']); ?></span>
            <span class="p-serch__announcement">件 ／</span>
            <span class="p-serch__num"><?php echo sanitize($dbReportData['total']); ?></span>
            <span class="p-serch__announcement">件</span>
          </div>
        </div>
        <!-- セレクトボックスー -->
        <form class="p-search__select" name="" method="get">
          <div class="c-label__selectbox p-selectbox__1 p-search__selectbox">
            <select class="" name="plant_sort">
              <option value="0" <?php if (getFormData('plant_sort', true) == 0) {
                                                                                                                                                    echo 'selected';
                                                                                                                                                  } ?>>プラント</option>
              <?php
                                                                                                                                                  foreach ($dbPlantData as $key => $val) {
              ?>
                <option value="<?php echo $val['id'] ?>" <?php if (getFormData('plant_sort', true) == $val['id']) {
                                                                                                                                                      echo 'selected';
                                                                                                                                                    } ?>>
                  <?php echo $val['plant']; ?>
                </option>
              <?php
                                                                                                                                                  }
              ?>
            </select>
          </div>

          <div class="c-label__selectbox p-selectbox__1 p-search__selectbox">
            <select class="" name="date_sort">
              <option value="0" <?php if (getFormData('date_sort', true) == 0) {
                                                                                                                                                    echo 'selected';
                                                                                                                                                  } ?>>点検日</option>
              <option value="1" <?php if (getFormData('date_sort', true) == 1) {
                                                                                                                                                    echo 'selected';
                                                                                                                                                  } ?>>古い順</option>
              <option value="2" <?php if (getFormData('date_sort', true) == 2) {
                                                                                                                                                    echo 'selected';
                                                                                                                                                  } ?>>新しい順</option>
            </select>
          </div>
          <div class="c-button__container p-search__button">
            <input name="search-button" type="submit" value="&#xf002;" class="fas fa-search c-button black">
          </div>
        </form>
      </section>

      <!-- SpとTab画面で表示する -->
      <!-- ページネーション 引数として現在のページ、トータルページ、プラント順、点検順 -->
      <div class="c-sp__pagenation">
        <?php pagenation($currentPageNum, $dbReportData['total_page'], '&plant_sort=' . $plantSort . '&date_sort=' . $dateSort); ?>
      </div>

      <!-- Panal -->
      <div class="c-panel">
        <?php
                                                                                                                                                  foreach ((array) $dbReportData['data'] as $key => $val) :
        ?>
          <a href="reportDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&report_id=' . $val['id'] : '?report_id=' . $val['id']; ?>" class="c-panel__link">
            <!-- 通常時の表示 -->
            <div class="c-panel__head">
              <img class="c-panel__img" src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['tag']); ?>">
            </div>
            <!-- hover時の表示 -->
            <div class="c-panel__head hover">
              <img class="c-panel__img pic1" src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['tag']); ?>">
              <img class="c-panel__img pic2" src="<?php echo showImg(sanitize($val['pic2'])); ?>" alt="<?php echo sanitize($val['tag']); ?>">
            </div>
            <!-- 通常時の表示 -->
            <div class="c-panel__body">
              <ul>
                <li class="c-panel__list"><?php echo sanitize($val['plant']); ?></li>
                <li class="c-panel__list"><?php echo sanitize($val['tag']); ?></li>
                <li class="c-panel__list"><?php echo sanitize($val['testday']); ?></li>
                <li class="c-panel__list">担当：<?php echo sanitize($val['staff']); ?></li>
                <li class="c-panel__list">状態：<?php echo sanitize($val['status_data']); ?></li>
              </ul>
            </div>
            <!-- ホバーしたら表示させる -->
            <div class="c-panel__body hover">
              <ul>
                <li class="c-panel__list"><?php echo sanitize($val['plant']); ?>　<?php echo sanitize($val['tag']); ?></li><br>
                <li class="c-panel__list">点検日：<?php echo sanitize($val['testday']); ?></li>
                <li class="c-panel__list">担当：<?php echo sanitize($val['staff']); ?></li>
                <li class="c-panel__list">状態：<?php echo sanitize($val['status_data']); ?></li><br>
                <li class="c-panel__list">不具合</li>
                <li class="c-panel__list"><?php echo sanitize($val['symptoms']); ?></li><br>
                <li class="c-panel__list">処置</li>
                <li class="c-panel__list"><?php echo sanitize($val['observation']); ?></li><br>
              </ul>
            </div>
          </a>
        <?php
                                                                                                                                                  endforeach;
        ?>
      </div>
      <!-- ページネーション 引数として現在のページ、トータルページ、プラント順、点検順 -->
      <?php pagenation($currentPageNum, $dbReportData['total_page'], '&plant_sort=' . $plantSort . '&date_sort=' . $dateSort); ?>

    </div>
  </div>
</div>
<!-- footer -->
<?php
                                                                                                                                                  require('footer.php');
?>