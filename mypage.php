<?php
//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==================================
//画面表示用データ取得
//==================================
$u_id = $_SESSION['user_id'];
//DBから点検データを取得
$reportData = getMyReports($u_id);
//debug('取得した点検データ：' . print_r($reportData, true));
//DBから連絡掲示板データを取得
$bordData = getMyMsgsAndBord($u_id);
//debug('取得した掲示板データ：' . print_r($bordData, true));
//DBからお気に入りデータを取得
$likeData = getMyLike($u_id);
debug('取得したお気に入りデータ：' . print_r($likeData, true));

debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>

<?php
//タイトル表示関数
$siteTitle = 'Mypage';
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

  <!--  パスワード変更サクセス時に表示-->
  <p class="js-show-msg p-msg__slide" style="display:none">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>


  <!-- メインコンテンツ -->
  <div class="l-contents">
    <!-- サイドバー -->
    <?php
    require('sidebar.php');
    ?>
    <!-- Main -->
    <div class="l-main">
      <h1 class="c-main__title"><?php if (!empty($userData)) echo $userData['user_name']; ?>のMyPage</h1>
      <h2 class="c-sub__title p-subtitle__report">点検した計器一覧 </h2>
      <section class="c-panel c-scroll__container p-scroll__panel">
        <?php
                                if (!empty($reportData)) :
                                  foreach ($reportData as $key => $val) :
        ?>
            <a href="report.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&report_id=' . $val['id'] : '?report_id=' . $val['id']; ?>" class="c-panel__link p-scroll__link">
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
                                                                                                      // endif;
                                                                                                      else :
          ?>
          <span class="">点検データはまだありません。点検後、<a href="report.php">Report報告</a>してください。</span>

        <?php
                                                                                                      endif;
        ?>
      </section>


      <!-- 連絡掲示板 -->
      <h2 class="c-sub__title p-subtitle__message">連絡掲示板一覧 </h2>
      <section class="list c-table__container c-scroll__container p-scroll__panel">
        <?php
                                                                                                      if (!empty($bordData)) { ?>
          <table class="c-table__main">
            <thead class="c-table__head">
              <tr class="c-table__menu">
                <th class="c-table__list">プラント</th>
                <th class="c-table__list">タグナンバー</th>
                <th class="c-table__list">最新送信日時</th>
                <th class="c-table__list">送信元</th>
                <th class="c-table__list">メッセージ</th>
              </tr>
            </thead>
            <tbody class="c-table__body">
              <?php
                                                                                                        //掲示板に保存されている一番新しいメッセージを表示する
                                                                                                        //send_dateが新しい順に$bordDataのキーに保存し、foreachで展開し$bordData['msg']を取得する
                                                                                                        //array_shiftで最も新しいメッセージが格納されているキーを$msgに挿入する
                                                                                                        foreach ($bordData as $key => $val) {
                                                                                                          if (!empty($val['msg'])) {
                                                                                                            //debug('$val' . print_r($val['msg'], true));
                                                                                                            $msg = array_shift($val['msg']);
                                                                                                            //debug('$msg' . print_r($msg, true));
                                                                                                            //debug('$val' . print_r($val['msg'], true));
                                                                                                            //report_id からプラント名とタグナンバーを取得
                                                                                                            $reportmess = getReportOne($val['report_id']);
                                                                                                            //debug('$reportmess'.print_r($reportmess,true));

              ?>
                  <tr class="c-table__menu">
                    <td class="c-table__list"><?php echo $reportmess['plant']; ?></td>
                    <td class="c-table__list"><?php echo $reportmess['tag']; ?></td>
                    <!-- strtotime は英文形式の日付をUnixタイムスタンプに変換する関数。多くの文字列をサポートしている。 -->
                    <td class="c-table__list"><?php echo sanitize(date('Y.m.d H:i', strtotime($msg['send_date']))); ?></td>
                    <!-- 連絡相手が誰か判別している、取得した最新メッセージから送信者と受信者のIDをそれぞれ取得する。自分のIDと相手のIDを比較 -->
                    <td class="c-table__list"><?php echo sanitize(getOpponentName($msg['to_user'], $msg['from_user'], $u_id)); ?></td>
                    <!-- msgボードへのリンク getパラメータとしてmsgボードのidを渡している。表示する文字数をmb_substrで30文字以内に制限。 -->
                    <td class="c-table__list"><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']), 0, 30); ?>....</a></td>
                  </tr>
                <?php
                                                                                                          } else {
                ?>
                  <?php
                                                                                                            // メッセージがない掲示板は削除フラグを立てる
                                                                                                            // 次回更新時に連絡しない場合削除する
                                                                                                            nonMsgdeleteMsgBord($val['id']);
                                                                                                            //report_id からプラント名とタグナンバーを取得
                                                                                                            $reportmess = getReportOne($val['report_id']);
                  ?>
                  <tr class="c-table__menu">
                    <td class="c-table__list"><?php echo $reportmess['plant']; ?></td>
                    <td class="c-table__list"><?php echo $reportmess['tag']; ?></td>
                    <!-- strtotime は英文形式の日付をUnixタイムスタンプに変換する関数。多くの文字列をサポートしている。 -->
                    <td class="c-table__list">なし</td>
                    <td class="c-table__list">なし</td>
                    <td class="c-table__list"><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">メッセージなし</a></td>
                  </tr>
              <?php
                                                                                                          }
                                                                                                        }
                                                                                                      } else { ?>
              <span class="c-table__non"><a href="history.php">こちらから</a>計器を選択し</span>疑問点などを質問しましょう。
            <?php }
            ?>
            </tbody>
          </table>
      </section>


      <!-- お気に入り -->
      <h2 class="c-sub__title p-subtitle__like">お気に入り一覧 </h2>
      <section class="c-panel c-scroll__container p-scroll__panel">
        <?php
                                                                                                      if (!empty($likeData)) :
                                                                                                        foreach ($likeData as $key => $val) :
        ?>
            <a href="reportDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . $val['report_id'] : '?report_id=' . $val['report_id']; ?>" class="c-panel__link p-scroll__link">
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
                                                                                                      // endif;
                                                                                                      else :
          ?>
          <span class="">お気に入り未登録です。どんどん<a href="history.php">登録</a>しましょう！</span>

        <?php
                                                                                                      endif;
        ?>
      </section>
    </div>
  </div>
</div>
<!-- footer -->
<?php
                                                                                                      require('footer.php');
?>