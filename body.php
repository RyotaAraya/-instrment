<!-- Body -->
<?php $modeFlg = $_COOKIE["myCookieName"];

//debug('darkmode:'.print_r($modeFlg,true));
?>


<body class="l-body <?php if ($modeFlg == "isActive") {
                      echo "darkmode";
                    } ?>">