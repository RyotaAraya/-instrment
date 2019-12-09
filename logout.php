<?php

//共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログアウトページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
debug('ログアウトします。');

/*LogOut処理 POSTも初期化*/
isLogout();

debug('セッションID：'.session_id());
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('ログインページへ遷移します。');
header("Location:login.php");
exit();
