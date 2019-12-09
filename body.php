<!-- Body -->
<?php $value = $_COOKIE["myCookieName"]; ?>

<body class="l-body <?php if ($value == "isActive") {
                      echo "darkmode";
                    } ?>">