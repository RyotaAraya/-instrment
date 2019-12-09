<?php
//共通変数・関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// Ajax処理
//================================

debug('$_POST[reportId]'.print_r($_POST['reportId'],true));
debug('$_SESSION[user_id]'.print_r($_SESSION['user_id'],true));



//postがありユーザーIDがあり、ログインしている場合
if(isset($_POST['reportId']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります。');
  $r_id = $_POST['reportId'];
  debug('点検ID：'.$r_id);

  //例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    //レコードがあるか検索
    //likeという単語はLIKE検索というSQLの命令文で使われるため、``で囲む
    $sql = 'SELECT * FROM `like` WHERE report_id = :r_id AND user_id = :u_id';
    $data = array(':r_id' => $r_id, ':u_id' => $_SESSION['user_id']);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug('$resultCount：'.print_r($resultCount,true));

    //レコードが1件でもある場合
    if(!empty($resultCount)){
      //レコードを削除する
      debug('お気に入りを削除します。');
      $sql = 'DELETE FROM `like` WHERE report_id = :r_id AND user_id = :u_id';
      $data = array(':r_id' => $r_id, ':u_id' => $_SESSION['user_id']);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('$stmt：'.print_r($stmt,true));

    }else{
      //レコードを挿入する
      debug('お気に入り登録します。');
      $sql = 'INSERT INTO `like` (report_id, user_id, create_date) VALUES (:r_id, :u_id, :date)';
      $data = array(':r_id' => $r_id, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('$stmt：'.print_r($stmt,true));

    }
  }catch(Exeption $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
debug('Ajax処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>

