<?php
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors', 'on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');


//===============================
// 開発フラグ
//===============================
//テスト環境と本番環境でdb接続先を変える
//trueがテスト環境、falseで本番環境
$dbTestmode_flg = false;

//================================
// デバッグ
//================================
//デバッグフラグ
//$debug_flg = true;
//デバッグログ関数
function debug($str)
{
  global $dbTestmode_flg;
  if (!empty($dbTestmode_flg)) {
    error_log('デバッグ：' . $str);
  }
}

//===============================
// セッション準備・セッション有効期限を延ばす
//===============================
//セッションファイルの置き場を変更する(/var/tmp/以下に置くと30日間は削除されない)
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定(30日以上経っているものに対してだけ100分の1の確率で削除)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える(なりすましのセキュリティ対策)
session_regenerate_id();



//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart()
{
  debug('＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞画面表示処理開始');
  debug('セッションID：' . session_id());
  debug('セッション変数の中身：' . print_r($_SESSION, true));
  debug('現在日時のタイムスタンプ；' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}


//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '文字以下で入力してください');
define('MSG07', 'エラー発生のためしばらく経ってからやり直してください');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います'); //未使用
define('MSG12', '現在のパスワードが違います');
define('MSG13', '新旧異なるパスワードにしてください');
define('MSG14', '文字で入力してください');
define('MSG15', '選択してください');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('MSG18', '認証キーが違います');
define('MSG19', '権限がありませんので変更できません');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '連絡掲示板');
define('SUC06', '問合せメールを送信しました');
define('SUC08', 'メールを送信しました。再発行パスワードを使用しログインしてください');
define('SUC09', '点検結果をデータベースに保存しました');
define('SUC10', 'ゲストログインのため制限があります。');
//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();


//================================
// バリデーション関数
//================================

//バリデーション関数
//未入力チェック 空文字はエラー
function validRequired($str, $key)
{
  if ($str === "") {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//バリデーション関数
//最大文字数チェック
function validMaxLen($str, $key, $max = 255)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = $max . MSG06;
  }
}
//バリデーション関数
//最小文字数チェック
function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数
//Email形式チェック
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
//バリデーション関数
//半角英数字チェック
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
//バリデーション関数
//パスワードの比較
function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
//バリデーション関数
//Email重複チェック
function validEmailDup($email)
{
  global $err_msg;
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果の値を取得
    debug('stmt：' . print_r($stmt, true));

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //array_shiftで配列の先頭を取り出す(カウント数)
    debug('result：' . print_r($result, true));

    if (!empty(array_shift($result))) {
      $err_msg['email'] = MSG08;
      debug('array_shift result：' . print_r($result, true));
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//バリデーション関数
//電話番号形式チェック
function validTel($str, $key)
{

  if (!preg_match("/\A(((0(\d{1}[-(]?\d{4}|\d{2}[-(]?\d{3}|\d{3}[-(]?\d{2}|\d{4}[-(]?\d{1}|[5789]0[-(]?\d{4})[-)]?)|\d{1,4}\-?)\d{4}|0120[-(]?\d{3}[-)]?\d{3})\z/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
//バリデーション関数
//半角数字チェック
function validNumber($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}
//バリデーション
//固定長チェック
function validLength($str, $key, $len = 6)
{
  if (mb_strlen($str) !== $len) {
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}

//バリデーション関数
//パスワードチェック
function validPass($str, $key)
{
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//バリデーション関数
//エラーメッセージ表示
function getErrMsg($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}

//バリデーション関数
//セレクトボックスチェック
function validSelect($str, $key)
{
  if (!preg_match("/^[1-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
//================================
// ログイン認証
//================================
function isLogin()
{
  //ログインしている場合
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン判定開始します');

    //現在日時が最終ログイン日時＋有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      debug('ログイン有効期限オーバーです。');

      // セッション変数を全て解除する
      $_SESSION = array();

      //セッション情報だけでなくセッションを破壊する。
      if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
      }

      //セッションを削除（ログアウト）する
      session_destroy();

      return false;
    } else {
      debug('ログイン有効期限以内です。');
      return true;
    }
  } else {
    debug('未ログインユーザーです。');
    return false;
  }
}

//================================================
// LogOut処理まとめ
//================================================
function isLogout()
{
  /*POST初期化*/
  $_POST = array();
  /*ログアウト処理*/
  /*1.セッション変数を全て解除する*/
  $_SESSION = array();
  /*2.セッション情報だけでなくセッションを破壊する*/
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
  }
  /*3.セッションIDを削除する*/
  session_destroy();
}


//================================
// データベース
//================================
//DB接続関数
function dbConnect()
{
  //global
  global $dbTestmode_flg;
  debug('DBへの接続開始');
  //DBへの接続準備
  if ($dbTestmode_flg) {
    debug('テスト環境：localhost');
    $dsn = 'mysql:dbname=instrment;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
  } else {
    /*外部ファイルを読み込む*/
    require('productionConfig.php');
    $dsn;
    $user;
    $password;
  }
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
//SQL実行関数
function queryPost($dbh, $sql, $data)
{
  global $err_msg;

  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL文：queryPost：' . print_r($stmt, true));
    debug('SQLエラー内容：queryPost：' . print_r($stmt->errorInfo(), true));
    $result = $stmt->errorInfo();
    debug('クエリ結果の中身：' . print_r($result, true));
    $err_msg['common'] = MSG07; //アドレス被り以外の失敗時に表示する
    return 0;
  }
  debug('クエリ成功：queryPost：' . print_r($stmt, true));
  return $stmt;
}
//ユーザー情報の取得 idで指定
function getUser($u_id)
{
  debug('getUser ユーザー情報を取得します。');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT id, class, user_name, tel, email, pic, delete_flg FROM users WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    //クエリ成功の場合
    if ($stmt) {
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('getUser 取得したユーザー情報：' . print_r($result, true));
      return $result;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
//ユーザー情報の取得 idで指定
function getUserInportantData($u_id)
{
  debug('getUserInportantData 重要なユーザー情報を取得します。');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT class, user_name, email, password FROM users WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    //クエリ成功の場合
    if ($stmt) {
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      //debug('getUserInportantData 取得した重要なユーザー情報：' . print_r($result, true));
      return $result;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

//点検情報の取得 idで指定
function getTestReport($u_id, $report_id)
{
  debug('点検データを取得します。');
  debug('ユーザID：' . $u_id);
  debug('点検ID：' . $report_id);
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM report WHERE user_id = :u_id AND id = :report_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':report_id' => $report_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    debug('クエリ結果getReport：' . print_r($stmt, true));

    //クエリ成功の場合
    if ($stmt) {
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ成功：getReport：' . print_r($result, true));
      return $result;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
//=======================================
// レポート情報取得
//=======================================
function getReportList($currentMinNum = 1, $plantSort, $statusSort, $dateSort, $span = 10)
{
  debug('reportデータを取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM report WHERE delete_flg=0';
    if (!empty($plantSort)) $sql .= ' AND plant_id=' . $plantSort;
    if (!empty($statusSort)) $sql .= ' AND status_id=' . $statusSort;
    if (!empty($dateSort)) {
      switch ($dateSort) {
        case 1:
          $sql .= ' ORDER BY testday ASC';
          break;
        case 2:
          $sql .= ' ORDER BY testday DESC';
          break;
      }
    }
    debug('sql；getReportList：' . print_r($sql, true));

    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //総レコード数をカウントする
    $rst['total'] = $stmt->rowCount();
    debug('総レコード数：' . $rst['total']);
    //総ページ数を計算する ceil関数は切り上げ用
    $rst['total_page'] = ceil($rst['total'] / $span);
    debug('総ページ数：' . $rst['total_page']);

    if (!$stmt) {
      return false;
    }

    //ページング用のSQL文作成
    //$sql = 'SELECT * FROM report WHERE delete_flg=0';
    $sql = 'SELECT r.id, r.plant_id, r.tag, r.symptoms, r.testday, r.staff, r.observation, r.pic1, r.pic2,';
    $sql .= ' r.status_id, r.user_id, r.delete_flg, r.create_date, r.update_date, p.plant, s.status_data';
    $sql .= ' FROM report AS r LEFT JOIN plants AS p ON r.plant_id = p.id LEFT JOIN statusdata AS s ON r.status_id = s.id WHERE r.delete_flg = 0';

    if (!empty($plantSort)) $sql .= ' AND plant_id=' . $plantSort;
    if (!empty($statusSort)) $sql .= ' AND status_id=' . $statusSort;
    if (!empty($dateSort)) {
      switch ($dateSort) {
        case 1:
          $sql .= ' ORDER BY testday ASC';
          break;
        case 2:
          $sql .= ' ORDER BY testday DESC';
          break;
          //  case 3:
          //    $sql .= ' ORDER BY create_date DESC';
          //    break;
      }
    }
    $sql .= ' LIMIT :span OFFSET :currentMinNum';
    debug('SQL：' . $sql);
    // クエリ実行
    $stmt = $dbh->prepare($sql);
    //LIMITとOFFSETの後は文字列不可のため数字で書く
    $stmt->bindValue(':span', $span, PDO::PARAM_INT);
    $stmt->bindValue(':currentMinNum', $currentMinNum, PDO::PARAM_INT);
    $stmt->execute();

    // $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================================
// reportテーブルからidを指定して点検データを取得する
// plantsテーブルのplantデータを外部結合する
// GETで取得したreport_id
// reportDetail.php
//================================================
function getReportOne($report_id)
{
  debug('getReportOne 点検データを取得します。');
  debug('reportテーブルのid：' . $report_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT r.id , p.plant , r.tag, r.symptoms, r.testday, r.staff, r.observation, r.pic1, r.pic2, r.status_id, r.user_id, r.create_date, r.update_date FROM report AS r LEFT JOIN plants AS p ON r.plant_id = p.id WHERE r.id = :report_id AND r.delete_flg = 0 AND p.delete_flg = 0';
    $data = array(':report_id' => $report_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//==================================================
//プラント情報を取得
//==================================================
function getPlant()
{
  debug('プラント情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM plants';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//==================================================
// Status情報を取得
//==================================================
function getStatus()
{
  debug('ステータス情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM statusdata';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================================
// 自分の点検データを取得
// mypage.php
//================================================
function getMyReports($u_id)
{
  debug('自分の点検データを取得します。');
  debug('ユーザーID：' . $u_id);
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT r.id, r.plant_id, r.tag, r.symptoms, r.testday, r.staff, r.observation, r.pic1, r.pic2,';
    $sql .= ' r.status_id, r.user_id, r.delete_flg, r.create_date, r.update_date, p.plant, s.status_data';
    $sql .= ' FROM report AS r LEFT JOIN plants AS p ON r.plant_id = p.id LEFT JOIN statusdata AS s ON r.status_id = s.id WHERE r.user_id = :u_id AND r.delete_flg = 0 ORDER BY r.create_date DESC';

    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
//================================================
// 自分のmsg情報をチェック
// mypage.php
//================================================
function getMyMsgsAndBord($u_id)
{
  debug('自分のメッセージ情報を取得します');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //掲示板のレコードを取得 bordテーブル
    //SQL文作成
    $sql = 'SELECT * FROM bord WHERE ( reporter_id = :id OR questioner_id = :id ) AND delete_flg = 0';
    $data = array(':id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    //debug('$rst:' . print_r($rst, true));
    if (!empty($rst)) {
      foreach ($rst as $key => $val) {
        //SQL文作成
        //メッセージテーブルから降順で取得する
        $sql = 'SELECT * FROM message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }
    if ($stmt) {
      //クエリ結果の全データを返却
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

//================================================
// 連絡掲示板がすでにあるかチェック
//================================================
function getBord($s_uid, $report_id)
{
  debug('連絡掲示板情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT id FROM bord WHERE reporter_id = :s_uid AND questioner_id = :b_uid AND report_id = :report_id AND delete_flg = 0';
    $data = array(':s_uid' => $s_uid, ':b_uid' => $_SESSION['user_id'], ':report_id' => $report_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================================
// メッセージがない連絡掲示板を削除
//================================================
function nonMsgdeleteMsgBord($bord_id)
{
  debug('連絡掲示板を削除します');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    // $sql = 'SELECT id FROM bord WHERE reporter_id = :s_uid AND questioner_id = :b_uid AND report_id = :report_id AND delete_flg = 0';

    $sql = 'UPDATE bord SET delete_flg = 1 WHERE id = :b_id';

    //$sql = 'UPDATE users  SET user_name = :u_name, tel = :tel, email = :email, pic = :pic WHERE id = :u_id';

    $data = array(':b_id' => $bord_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//================================================
// GETパラメータで掲示板idを渡し、各種データを取得する
// bordテーブルとmessageテーブルのデータを結合
// msg.php で使用
//================================================
function getMsgsAndBord($id)
{
  debug('msg情報を取得します。');
  debug('掲示板ID：' . $id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT b.reporter_id, b.questioner_id, b.report_id, b.create_date, m.id AS m_id, m.bord_id, m.send_date, m.to_user, m.from_user, m.msg, m.delete_flg FROM message AS m RIGHT JOIN bord AS b ON b.id = m.bord_id WHERE b.id = :id AND b.delete_flg = 0 ORDER BY send_date ASC';

    $data = array(':id' => $id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    debug('$stmt' . print_r($stmt, true));

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================================
// 相手のユーザーIDから名前を取得する
//================================================
function getOpponentName($id1, $id2, $u_id)
{
  debug('相手のユーザーIDから名前を判別します');
  //例外処理
  try {
    //DBへの接続準備
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT user_name FROM users WHERE id = :u_id';

    //相手を判別
    if ($id1 === $u_id) {
      $data = array(':u_id' => $id2);
    } else {
      $data = array(':u_id' => $id1);
    }
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果の全データを返却
      $rst = $stmt->fetch(PDO::FETCH_ASSOC);
      //debug('掲示板一覧' . print_r($rst, true));
      foreach ($rst as $key => $val) {
        $opponent = $val;
      }
      //debug('相手の名前は：'.print_r($opponent,true));

      return $opponent;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

//================================================
// 自分のお気に入り情報の確認
//================================================
function getMyLike($u_id)
{
  debug('自分のお気に入り情報を取得します。');
  debug('ユーザーID：' . $u_id);
  //例外処理
  try {
    //DBへの接続準備
    $dbh = dbConnect();
    //SQL文作成
    //$sql = 'SELECT * FROM `like` AS l LEFT JOIN report AS r ON l.report_id = r.id WHERE l.user_id = :u_id';
    $sql = 'SELECT * FROM `like` AS l LEFT JOIN report AS r ON l.report_id = r.id LEFT JOIN plants AS p ON r.plant_id = p.id LEFT JOIN statusdata AS s ON r.status_id = s.id WHERE l.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

//================================================
// お気に入り情報の確認
//================================================
function isLike($u_id, $r_id)
{
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID：' . $u_id);
  debug('reportID：' . $r_id);
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM `like` WHERE report_id = :r_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':r_id' => $r_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt->rowCount()) {
      debug('お気に入りです');
      return true;
    } else {
      debug('特に気に入っていません。');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
//================================================
// メール送信
//================================================
function sendMail($from, $to, $subject, $comment)
{
  if (!empty($from) && !empty($to) && !empty($subject) && !empty($comment))
    //文字化けしないように設定おきまりのパターン
    mb_language("Japanese"); //現在使っている言語を設定
  mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング(機械がわかる言語へ変換)するかを設定

  //メールを送信(送信結果は true or false で返ってくる)
  $result = mb_send_mail($to, $subject, $comment, $from);
  //送信結果を判定
  if ($result) {
    debug('メールを送信しました。');
  } else {
    debug('エラー発生メールの送信に失敗しました。');
  }
}
//================================================
// お問い合わせ送信と確認メール
//================================================
function contactMail($contact_name, $contact_from, $message, $key)
{
  //文字化けしないように設定
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");
  //変数の初期化
  $contact_to = "camerondiaztest@gmail.com";
  //問合せのタイトル
  $contact_title = "instrment問合せメール";
  //自動返信のタイトル
  $confirmation_title = "instrment自動返信メール";
  //エラーメッセージ格納
  $contact_answer = false;
  //問合せメール格納
  $contact_message = "";
  //送信確認メール
  $confirmation_message = "";


  //問合せメール
  $contact_message = <<<EOT
{$contact_name}様からの問合せ
FROM：{$contact_from}

メッセージ：
{$message}

EOT;

  //送信確認メールを送信者に送る
  $confirmation_message = <<<EOT
{$contact_name}様

instrmentにお問い合わさせありがとうございます。


回答まで今しばらくお待ちください。
お心当たりがない場合は削除願います。



//////////////////////
instrment アラヤ
   FROM：{$contact_to}
   TEL:090-9999-9999
EOT;

  //問合せメールと送信者への自動返信メールが成功したら
  if (mb_send_mail($contact_to, $contact_title, $contact_message)  &&  mb_send_mail($contact_from, $confirmation_title, $confirmation_message)) {
    return $contact_answer = true;
  } else {
    global $err_msg;
    $err_msg[$key] = MSG07;
    debug('error');
    return $contact_answer = false;
  }
}
//================================================
// 画像処理
//================================================
function uploadImg($file, $key)
{
  debug('画像アップロード処理開始');
  debug('FILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: // その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：' . $path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

//================================================
// その他
//================================================

// フォーム入力保持
function getFormData($str, $flg = false)
{
  debug('getFormData処理開始');

  /*デフォルトGET*/
  if ($flg) {
    debug('GET');
    $method = $_GET;
  } else {
    debug('POST');
    $method = $_POST;
  }
  //debug('メソッドは：'.print_r($method,true));
  global $dbFormData;
  //debug('dbFormData'.print_r($dbFormData,true));
  global $err_msg;
  //debug('err_msg：'.print_r($err_msg,true));

  // ユーザーデータがある場合
  if (!empty($dbFormData)) {
    //フォームのエラーがある場合
    if (!empty($err_msg[$str])) {
      //POSTにデータがある場合
      if (isset($method[$str])) { //金額や郵便番号などのフォームで数字や数値の0が入っている場合もあるので、issetを使うこと
        return sanitize($method[$str]);
      } else {
        //ない場合（フォームにエラーがある＝POSTされてるハズなので、まずありえないが）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else {
      //データがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームでひっかかっている状態）
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else { //そもそも変更していない
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

//SESSIONを一度だけ取得できる
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//パスワード再発行のための認証キー生成
function makeRandKey($length = 6)
{
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}
//サニタイズ
//DBからのデータを画面に表示する時に浄化するために使用する
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}
//ページング
//$currentPageNum:現在のページ
//$totalPageNum:総ページ数
//$link:検索用GETパラメーターリンク
//$pageColNum:ページネーション表示数
function pagenation($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5)
{

  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
    // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
    // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    // 現ページが1の場合は左に何も出さない。右に５個出す。
  } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
    // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    // それ以外は左に２個出す。
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="c-pagenation">';
  echo '<ul class="c-pagenation__menu">';

  if ($currentPageNum != 1) {
    echo '<li class="c-pagenation__list"><a class="c-pagenation__link" href="?p=1' . $link . '">&lt;</a></li>';
  }
  for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
    echo '<li class="c-pagenation__list ';
    if ($currentPageNum == $i) {
      echo 'active';
    }
    echo '"><a class="c-pagenation__link" href="?p=' . $i . $link . '">' . $i . '</a></li>';
  }
  if ($currentPageNum != $maxPageNum && $maxPageNum > 1) {
    echo '<li class="c-pagenation__list"><a class="c-pagenation__link" href="?p=' . $totalPageNum . $link . '">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
}
//画像表示用関数
function showImg($path)
{
  if (empty($path)) {
    return 'images/nonimage.jpeg';
  } else {
    return $path;
  }
}
//================================================
//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
//================================================
function appendGetParam($arr_del_key = array())
{
  debug('appendGetParam');
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {

      debug(' ');
      debug('$key：' . print_r($key, true));
      debug('$val：' . print_r($val, true));
      debug('$arr_del_key：' . print_r($arr_del_key, true));
      debug('$str1：' . print_r($str, true));

      if (!in_array($key, $arr_del_key, true)) { //取り除きたくないパラメータの時、urlにくっつけるパラメータを生成
        $str .= $key . '=' . $val . '&';
        debug('$str2：' . print_r($str, true));
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    debug('$str3：' . print_r($str, true));

    return $str;
  }
}
