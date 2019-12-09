<?php
//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax User Check　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//formの判別フラグを変数に格納
$formFlg = $_POST['form'];
debug('formFlg' . $formFlg);

$dbFormData = getUser($_SESSION['user_id']);
$email_now = $dbFormData['email'];
debug('emailNow' . print_r($email_now, true));

if (!empty($_POST)) {
  //DBへの接続準備
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $_POST['email']);


    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    //$result = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = $stmt->fetch(PDO::FETCH_COLUMN); //一つ目のキーの値
    debug('＊＊Email重複チェック：' . print_r($result, true));

    if ($formFlg === 'signup') {
      //login-formの場合
      //結果が0でない場合
      if (empty($result)) {
        debug('1.form empty :signup');
        echo json_encode(array(
          'errorFlg' => false,
          'msg' => '登録可能です'
        ));
      } else {
        debug('2.form !empty :signup');
        echo json_encode(array(
          'errorFlg' => true,
          'msg' => '既に登録されています'
        ));
      }
    } else if ($formFlg === 'login' || $formFlg === 'passRemindSend') {
      //login-formの場合
      //結果が0でない場合
      if (empty($result)) {
        debug('3.form empty :login or passRemindSend');
        echo json_encode(array(
          'errorFlg' => true,
          'msg' => 'ユーザー確認NG'
        ));
      } else {
        debug('4.form !empty :login or passRemindSend');
        echo json_encode(array(
          'errorFlg' => false,
          'msg' => 'ユーザー確認OK'
        ));
      }
    } else if ($formFlg === 'profEdit') {
      //自分のアドレスでも上書きでプロフ編集できるようにする
      if ($email_now === $_POST['email']) {
        debug('5.form empty :profEdit');
        echo json_encode(array(
          'errorFlg' => false,
          'msg' => '変更なし'
        ));
      }
      else if (empty($result)) {
        debug('5.form empty :profEdit');
        echo json_encode(array(
          'errorFlg' => false,
          'msg' => '登録可能です'
        ));
      } else {
        debug('6.form !empty :profEdit');
        echo json_encode(array(
          'errorFlg' => true,
          'msg' => '既に登録されています'
        ));
      }
    }
    exit();
  } catch (Exeption $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
