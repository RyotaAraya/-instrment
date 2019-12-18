<!-- ナビゲーション -->
<header class="l-header">
  <div class="p-header">
    <h1 class="p-header__logo">
      <a class="p-logo" href="index.php">Instrment</a>
    </h1>
    <!-- ハンバーガーメニュー -->
    <div class="p-hamburger-menu-icn js-hamburger-menu-icn">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <nav class="p-header__nav  js-hamburger-menu-target-toggle-class">
      <div class="p-hamburger__menu">menu</div>
      <ul class="p-nav__menu">
        <?php
        if (empty($_SESSION['login_date'])) {
        ?>
          <!-- 未ログイン時のヘッダー -->
          <li class="p-nav__list">
            <a class="p-nav__link" href="loginGest.php">Gest</a></li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="contact.php">Contact</a></li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="howtouse.php">Use</a></li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="signup.php">Register</a></li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="login.php">Login</a></li>
          <li class="p-nav__list">
            <!-- Dark Mode 切替 -->
            <div id="js-switch-mode" class="c-switch__mode p-nav__link">
              <!-- mode change -->
              <i class="c-mode__icn"></i>
            </div>
          </li>
        <?php
        } else {
        ?>
          <?php
          $userData = getUser($_SESSION['user_id']);
          ?>
          <!-- ログイン後のヘッダー -->
          <li class="p-nav__list">
            <a class="p-nav__link" href="contact.php">Contact</a></li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="howtouse.php">仕様説明</a>
          </li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="history.php">計器一覧</a>
          </li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="mypage.php"><?php if (!empty($userData)) echo $userData['user_name']; ?>のMypage</a>
          </li>
          <li class="p-nav__list c-sp__sidebar">
            <a class="p-nav__link" href="profEdit.php">Profile編集</a>
          </li>
          <li class="p-nav__list c-sp__sidebar">
            <a class="p-nav__link" href="passEdit.php">Password変更</a>
          </li>
          <li class="p-nav__list c-sp__sidebar">
            <a class="p-nav__link" href="report.php">Report</a>
          </li>
          <li class="p-nav__list c-sp__sidebar">
            <a class="p-nav__link" href="withdraw.php">退会</a>
          </li>
          <li class="p-nav__list">
            <a class="p-nav__link" href="logout.php" onclick="return confirm('ログアウトしますよろしいですか？')">Logout</a>
          </li>
          <li class="p-nav__list">
            <!-- Dark Mode 切替 -->
            <div id="js-switch-mode" class="c-switch__mode p-nav__link">
              <!-- mode change -->
              <i class="c-mode__icn"></i>
            </div>
          </li>

        <?php
                                                    }
        ?>
      </ul>
    </nav>
  </div>
</header>