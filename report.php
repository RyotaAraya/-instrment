<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「reportページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
require('auth.php');

//==============================
// 画面処理
//==============================

//GETデータを格納
$report_id = (!empty($_GET['report_id'])) ? $_GET['report_id'] : '';
//DBから点検データを取得
$dbFormData = (!empty($report_id)) ? getTestReport($_SESSION['user_id'], $report_id) : '';
//新規登録か編集画面か判断するためのフラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//DBからプラントデータを取得
$dbPlantData = getPlant();
$dbStatusData = getStatus();
debug('レポートのID：' . $report_id);
debug('フォーム用DBデータ：' . print_r($dbFormData, true));
//debug('プラントデータ：'.print_r($dbPlantData, true));
//debug('ステータスデータ：'.print_r($dbStatusData, true));


//パラメータ改ざんチェック
//GETパラメータはあるが、改ざんされている(URLをいじっている)場合、正しい点検データを取得できないためマイページへ遷移させる
if (!empty($report_id) && empty($dbFormData)) {
  debug('GETパラメータの点検IDが違います。マイページへ遷移します');
  header("Location:mypage.php");
  exit();
}


//// DBからユーザーデータを取得
//debug('useridは：'.$_SESSION['user_id']);
$userData = getUser($_SESSION['user_id']);
//$dbFormData = getUser($_SESSION['user_id']);
//debug('取得したユーザー情報'.print_r($userData,true));



//1.post送信されていた場合
if (!empty($_POST)) {
  debug('[[[[[[[[[[[[[[[[[[[[[[[]]]]]]]]]]]]]]]]]]]]]]]');
  debug('POST送信された：levelgage.php：');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));


  //変数にユーザー情報を代入
  $plant = $_POST['plant_id'];
  $tag = $_POST['tag'];
  $symptoms = $_POST['symptoms'];
  $testday = $_POST['testday'];
  $staff = $_POST['staff'];
  $observation = $_POST['observation'];

  //画像をアップロードし、パスを格納
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTに反映されないので）
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;

  //pic1が空でpic2に画像があった場合、pic1にpicの画像を移す
  if (empty($pic1) && !empty($pic2)) {
    $pic1 = $pic2;
    $pic2 = '';
  }
  $status_id = $_POST['status_id'];


  //更新の場合はDBの情報と入力情報が異なる場合、バリデーションを行う。
  if (empty($dbFormData)) {

    //所見最大文字数チェック
    validMaxLen($observation, 'observation');

    //入力必須フォームの未入力チェック
    validSelect($plant, 'plant_id');  //プラント名未入力チェック
    validRequired($tag, 'tag');  //タグナンバー未入力チェック
    validRequired($symptoms, 'symptoms');  //不具合内容未入力チェック
    validRequired($testday, 'testday');  //テスト日未入力チェック
    validRequired($staff, 'staff');  //担当者未入力チェック
    validRequired($observation, 'observation');  //タグ名未入力チェック
    validSelect($status_id, 'status_id');  //結果未入力チェック
  } else {
    if ($dbFormData['plant_id'] !== $plant) {
      //バリデーション
      validSelect($plant, 'plant_id');  //プラント名未入力チェック

    }
    if ($dbFormData['tag'] !== $tag) {
      //バリデーション
      validRequired($tag, 'tag');  //タグナンバー未入力チェック

    }
    if ($dbFormData['symptoms'] !== $symptoms) {
      //バリデーション
      validMaxLen($symptoms, 'symptoms', 50); //50文字数制限
      validMinLen($symptoms, 'symptoms', 6); //50文字数制限
      validRequired($symptoms, 'symptoms');  //不具合内容未入力チェック

    }
    if ($dbFormData['testday'] !== $testday) {
      //バリデーション
      validRequired($testday, 'testday');  //テスト日未入力チェック

    }
    if ($dbFormData['staff'] !== $staff) {
      //バリデーション
      validMaxLen($symptoms, 'staff', 20); //文字数制限
      validRequired($staff, 'staff');  //担当者未入力チェック

    }
    if ($dbFormData['observation'] !== $observation) {
      //バリデーション
      validMaxLen($symptoms, 'observation', 50); //50文字数制限
      validRequired($observation, 'observation');  //タグ名未入力チェック

    }
    if ($dbFormData['status_id'] !== $status_id) {
      //バリデーション
      validSelect($status_id, 'status_id');  //結果未入力チェック

    }
  }


  if (empty($err_msg)) {
    debug('バリデーションOK');

    //例外処理
    try {
      //DBへの接続準備
      $dbh = dbConnect();
      //メール文にプラント名を表示するための暫定処置
      //getPlantで取得した連想配列のキーが０から始まるが、$plantで取得するプラントのidは１から始まるため
      //表示する前に1マイナスしている。
      debug('$plant：' . print_r($plant, true));
      $plantdata = $dbPlantData[$plant - 1];
      debug('$dbPlantData：' . print_r($dbPlantData, true));
      debug('$plantdata：' . print_r($plantdata, true));
      $statusdata = $dbStatusData[$status_id - 1];

      //SQL文作成
      //編集と新規作成

      //編集
      if ($edit_flg) {
        $sql = 'UPDATE report SET plant_id = :plant_id,tag = :tag,symptoms = :symptoms,testday = :testday,staff = :staff,observation = :observation,pic1 = :pic1, pic2 = :pic2,status_id = :status_id,update_date = :update_date WHERE user_id = :user_id AND id = :report_id';

        //SQL文（クエリー作成）
        $data = (array(':plant_id' => $plant, ':tag' => $tag, ':symptoms' => $symptoms, ':testday' => $testday, ':staff' => $staff, ':observation' => $observation, ':pic1' => $pic1, ':pic2' => $pic2, ':status_id' => $status_id, ':update_date' => date('Y-m-d H:i:s'), ':user_id' => $_SESSION['user_id'], ':report_id' => $report_id));

        $comment = <<<EOT

送付者：{$userData['user_name']}
点検結果を編集しましたので報告いたします。
ご確認の程、よろしくお願いします。

ID：{$data[':report_id']}
プラント：{$plantdata['plant']}
タグナンバー：{$data[':tag']}
不具合内容：{$data[':symptoms']}
点検日：{$data[':testday']}
担当者：{$data[':staff']}
所感：{$data[':observation']}
結果：{$statusdata['status_data']}
編集日時：{$data[':update_date']}


EOT;
      } else {

        //新規作成
        $sql = 'INSERT INTO report (plant_id,tag,symptoms,testday,staff,observation,pic1,pic2,status_id,user_id,create_date) VALUES (:plant_id,:tag,:symptoms,:testday,:staff,:observation,:pic1,:pic2,:status_id,:user_id,:create_date)';

        //SQL文（クエリー作成）
        $data = (array(':plant_id' => $plant, ':tag' => $tag, ':symptoms' => $symptoms, ':testday' => $testday, ':staff' => $staff, ':observation' => $observation, ':pic1' => $pic1, ':pic2' => $pic2, ':status_id' => $status_id, ':user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s')));

        $comment = <<<EOT

送付者：{$user_name}
点検結果を報告いたします。
ご確認の程、よろしくお願いします。

ID：{$_SESSION['test_id']}
プラント：{$plantdata['plant']}
タグナンバー：{$data[':tag']}
不具合内容：{$data[':symptoms']}
点検日：{$data[':testday']}
担当者：{$data[':staff']}
所感：{$data[':observation']}
結果：{$data[':status_id']}
報告日時：{$data[':create_date']}


EOT;
      }
      debug('SQL文：' . print_r($sql, true));
      debug('data：' . print_r($data, true));
      $stmt = queryPost($dbh, $sql, $data);
      debug('stmt：' . print_r($stmt, true));


      //クエリ成功の場合
      if ($stmt) {
        //メッセージ表示用
        $_SESSION['msg_success'] = SUC09;
        debug('クエリ成功');
        $_SESSION['test_id'] = $dbh->lastInsertId();
        //メールを送信
        $user_name = ($userData['user_name']) ? $userData['user_name'] : '名無し';
        $from = $userData['email'];
        $to = 'camerondiaztest@gmail.com';
        $subject = '点検結果送付';
        sendMail($from, $to, $subject, $comment);
        header("Location:mypage.php"); //マイページへ
        exit();
      } else {
        debug('クエリ失敗');
        $err_msg['common'] = MSG07;
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
//タイトル表示関数
$siteTitle = 'Report';
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
    <div class="l-main">
      <h1 class="c-main__title">Report<?php echo (!$edit_flg) ? '報告' : '編集'; ?></h1>
      <div class="p-form__container">
        <form action="" method="post" enctype="multipart/form-data" class="c-form p-form p-form__report">

          <div class="p-report__container">
            <div class="js-form__group">
              <!-- Plant -->
              <label class="c-form__label <?php if (!empty($err_msg['plant_id'])) {
                                            echo 'err';
                                          } ?>">
                Plant
                <div class="c-label__selectbox p-selectbox__1">
                  <select class="js-form-plantid-validate" name="plant_id">
                    <option value="0" <?php if (getFormData('plant_id') == 0) {
                                        echo 'selected';
                                      } ?>>選択してください</option>
                    <?php
                    foreach ($dbPlantData as $key => $val) {
                      ?>
                      <option value="<?php echo $val['id'] ?>" <?php if (getFormData('plant_id') == $val['id']) {
                                                                    echo 'selected';
                                                                  } ?>>
                        <?php echo $val['plant']; ?>
                      </option>
                    <?php
                    }
                    ?>
                  </select>
                </div>
              </label>

              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['plant_id'])) {
                    if (!empty($err_msg['plant_id'])) echo $err_msg['plant_id'];
                  }
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>

            <!-- Tag No -->
            <div class="js-form__group">
              <label class="c-form__label <?php if (!empty($err_msg['tag'])) {
                                            echo 'err';
                                          } ?>">
                TagNo.
                <input class="c-label__input js-form-tagno-validate" type="text" name="tag" placeholder="FRC-1801A" value="<?php echo getFormData('tag'); ?>"></br>
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['tag'])) echo $err_msg['tag'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>

            <!-- 不具合 -->
            <div class="js-form__group">
              <label class="c-form__label <?php if (!empty($err_msg['symptoms'])) {
                                            echo 'err';
                                          } ?>">
                不具合内容
                <input class="c-label__input js-form-symptoms-validate" type="text" name="symptoms"  placeholder="指示ズレ、ブロー０チェック良好" value="<?php echo getFormData('symptoms'); ?>"></br>
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['symptoms'])) {
                    if (!empty($err_msg['symptoms'])) echo $err_msg['symptoms'];
                  }
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>


            <!-- Test Day -->
            <div class="js-form__group">
              <label class="c-form__label <?php if (!empty($err_msg['testday'])) {
                                            echo 'err';
                                          } ?>">
                点検日
                <input class="c-label__input js-form-testday-validate" type="date" name="testday" min="2018-04-01" value="<?php echo getFormData('testday'); ?>"></br>
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['testday'])) {
                    if (!empty($err_msg['testday'])) echo $err_msg['testday'];
                  }
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>

            <!--            担当者-->
            <div class="js-form__group">
              <label class="c-form__label <?php if (!empty($err_msg['staff'])) {
                                            echo 'err';
                                          } ?>">
                担当者
                <input class="c-label__input js-form-name-validate" type="text" name="staff" placeholder="安室 透" value="<?php echo getFormData('staff'); ?>"></br>
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg"></span>
                <?php
                if (!empty($err_msg['staff'])) {
                  if (!empty($err_msg['staff'])) echo $err_msg['staff'];
                }
                ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>

            <!--            結果-->
            <div class="js-form__group">
              <!--          エラーメッセージを表示させる-->
              <label class="c-form__label c-label__selectbox__label <?php if (!empty($err_msg['status_id'])) {
                                                                      echo 'err';
                                                                    } ?>">
                点検結果
                <div class="c-label__selectbox p-selectbox__1">
                  <select class="js-form-statusid-validate" name="status_id">
                    <option value="0" <?php if (getFormData('status_id') == 0) {
                                        echo 'selected';
                                      } ?>>選択してください</option>
                    <?php
                    foreach ($dbStatusData as $key => $val) {
                      ?>
                      <option value="<?php echo $val['id'] ?>" <?php if (getFormData('status_id') == $val['id']) {
                                                                    echo 'selected';
                                                                  } ?>>
                        <?php echo $val['status_data']; ?>
                      </option>
                    <?php
                    }
                    ?>
                  </select>
                </div>
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['status_id'])) echo $err_msg['status_id'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>



            <!--所見入力-->
            <div class="js-form__group">
              <label class="c-form__label <?php if (!empty($err_msg['observation'])) {
                                            echo 'err';
                                          } ?>">
                所見
                <textarea class="c-label__textarea js-form-message-validate js-count-text" name="observation" cols="50" rows="2"><?php echo getFormData('observation'); ?></textarea>
              </label>
              <!-- エラー表示部 -->
              <div class="js-text-counter">
                <span class="js-show-count-text">0</span>/200
              </div>
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['observation'])) echo $err_msg['observation'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>
          </div>

          <!-- Right Position -->
          <div class="p-report__container" style="overflow:hidden;">

            画像1
            <div class="c-form__imgDropContainer">
              <label class="c-form__label c-form__drop js-drop-area <?php if (!empty($err_msg['pic1'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="c-label__file js-file-input">
                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="c-label__img js-prev-img" style="<?php if (empty(getFormData('pic1'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>


            画像２
            <div class="c-form__imgDropContainer">
              <label class="c-form__label c-form__drop js-drop-area <?php if (!empty($err_msg['pic2'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="c-label__file js-file-input">
                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="c-label__img js-prev-img" style="<?php if (empty(getFormData('pic2'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <!-- エラー表示部 -->
              <div class="c-form-errorMsgContainer">
                <span class="c-form__areaMsg">
                  <?php
                  if (!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                  ?>
                </span>
                <span class="js-help-block"></span>
              </div>
            </div>


            <?php
            // 編集フラグがあれば編集
            if ($edit_flg) { ?>
              <div class="c-button__container">
                <input type="submit" class="far fa-edit c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf044" disabled="disabled" onclick="return confirm('編集結果を記録し報告します。よろしいでしょうか？')">
              </div>


            <?php
              // 編集フラグがなければ新規作成
            } else { ?>
              <div class="c-button__container">
                <input type="submit" class="far fa-paper-plane c-button l-form__btn btn-gradient js-disabled-submit js-form-submit" value="&#xf1d8;" disabled="disabled" onclick="return confirm('Reportを報告します。よろしいでしょうか？')">
              </div>
            <?php
            } ?>
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