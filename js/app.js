$(function() {
  //====================================================================
  // 定数と変数の初期化
  //====================================================================
  //error message格納
  const MSG_EMPTY = "入力必須です";
  const MSG_NAME_MAX = "20文字以下で入力してください";
  const MSG_TEXT_MAX = "255文字以下で入力してください";
  const MSG_TEXT_MIN = "6文字以上入力してください";
  const MSG_MSG_MIN = "10文字以上入力してください";
  const MSG_TEXT_HALF = "半角英数字で入力してください(記号不可)";
  const MSG_EMAIL_TYPE = "emailの形式で入力してください";
  const MSG_REPASSWORD_MISS = "再入力が違います";
  const MSG_OLD_NEWPASSWORD_MISS = "新旧異なるパスワードにしてください";
  const MSG_MESSAGE_MAX = "200文字以下で入力してください";
  const MSG_SYMPTOMS_MAX = "50文字以下で入力してください";
  const MSG_TEL＿ERROR = "電話番号の形式で入力してください";
  const MSG_SELECT_NON = "選択してください";
  //submit するための状態フラグ
  let formFlg = ""; //formの判別用
  let loginFlg = 0;
  let alreadyUseEmailFlg = 0; //登録済みのメールアドレスか確認する
  let nameFlg = 0;
  let emailFlg = 0;
  let telFlg = 0;
  let moonFlg = "off";
  let passwordFlg = 0;
  let rePasswordFlg = 0;
  let oldPasswordFlg = 0;
  let authPasswordFlg = 0; //パスワード再発行認証キー
  let oldPasswordNewMatchFlg = 0; //新旧パスワードの比較
  let newPasswordConfirmMatchFlg = 0; //新パスワード確認用との比較
  let messageFlg = 0;
  let messageBordFlg = 0; //掲示板のメッセージ
  let plantIdFlg = 0; //プラント名
  let tagNoFlg = 0; //タグNo
  let symptomsFlg = 0; //不具合内容
  let testDayFlg = 0; //点検日
  let statusIdFlg = 0; //点検結果
  //各DOMを取得
  let loginCheckContact = $(".js-contact-logincheck").text();
  let telCheck = $(".js-tel-validate"); //tel
  let nameCheck = $(".js-form-name-validate"); //name
  let emailCheck = $(".js-form-email-validate"); //email
  let passwordCheck = $(".js-form-password-validate"); //パスワード
  let rePasswordCheck = $(".js-form-repassword-validate"); //パスワード再入力
  let oldPasswordCheck = $(".js-form-oldpassword-validate"); //パスワード古いもの
  let messageCheck = $(".js-form-message-validate"); //contact message
  let messageBordCheck = $(".js-form-messagebord-validate"); //contact message
  let authenticationKeyCheck = $(".js-form-authentication-key-validate"); //パスワード再発行の認証キー
  let plantIdCheck = $(".js-form-plantid-validate"); //plant id
  let tagNoCheck = $(".js-form-tagno-validate"); //tagno id
  let symptomsCheck = $(".js-form-symptoms-validate"); //symptoms id
  let testDayCheck = $(".js-form-testday-validate"); //testday id
  let statusIdCheck = $(".js-form-statusid-validate"); //testday id

  let submitCheck = $(".js-form-submit"); //submit
  let messageCounter = $(".js-text-counter"); //message text count show
  let jsShowMsg = $(".js-show-msg"); //パスワード変更時などにサクセスメッセージを表示させる
  let msg = jsShowMsg.text(); //パスワード変更時などにサクセスメッセージなどを表示させる
  let $dropArea = $(".js-drop-area"); //画像ドロップ
  let $fileInput = $(".js-file-input"); //画像ファイルが入っているか
  let $countUp = $(".js-count-text"); //カウントしたいテキストエリア
  let $countView = $(".js-show-count-text"); //カウントした値を表示するエリア
  let $switchImgMain = $(".js-switch-img-main"); //画像表示切り替え
  let $switchImgSubs = $(".js-switch-img-sub"); //画像表示切り替え

  //====================================================================
  //  Dark Mode
  //====================================================================
  function setMyCookie() {
    myCookieVal = $("body").hasClass("darkmode") ? "isActive" : "notActive";
    $.cookie("myCookieName", myCookieVal, { expires: 365, path: "/" });
  }

  $("#js-switch-mode").on("click", function() {
    console.log("dark");
    $("body").toggleClass("darkmode");
    $(this).toggleClass("is-active");
    if (moonFlg == "off") {
      //DarkMode
      $(this).html("<i class='fas fa-sun'></i>");
      moonFlg = "on";
    } else {
      //LightMOde
      $(this).html("<i class='fas fa-moon'></i>");
      moonFlg = "off";
    }
    setMyCookie();
  });

  //====================================================================
  //  localStorage保存と読み込み
  //====================================================================
  $(".js-form-submit").on("click", function() {
    // 1.JSでフォームの内容をJSONデータで一括取得
    var form = $(".js-formdata-localStorage"); //値を保存するフォーム
    var formData = form.serializeArray(); //serializeArrayでフォームの内容をオブジェクト化する
    var formJson = JSON.stringify(formData); //JSON.stringifyメソッドでJSON化する
    // 2.ローカルストレージへ保存する
    localStorage.setItem("form_data", formJson);
  });
  // 3.ローカルストレージからデータを取り出す
  var localData = localStorage.getItem("form_data");
  // 4.取り出したJSONデータから再度フォームに値をセットする。JSONデータから扱いやすいオブジェクトデータに戻す
  localData = JSON.parse(localData);
  // 5.データをフォームにセットする
  if (localData !== null) {
    console.log(localData);
    for (var index in localData) {
      var data = localData[index];
      var formName = data["name"]; //name属性
      var formVal = data["value"]; //値
      $("[name=" + formName + "]").val(formVal);
    }
    emailFlg = 1;
    nameFlg = 1;
    messageFlg = 1;
    plantIdFlg = 1;
    passwordFlg = 1;
    rePasswordFlg = 1;
    symptomsFlg = 1;
    statusIdFlg = 1;
    tagNoFlg = 1;
    telFlg = 1;
    testDayFlg = 1;
    oldPasswordFlg = 1;
    oldPasswordNewMatchFlg = 1;
    newPasswordConfirmMatchFlg = 1;
    alreadyUseEmailFlg = 1;
  }
  // 6.一連の処理が終わったらローカルストレージのデータを消去する
  localStorage.removeItem("form_data");
  validSubmit();

  //====================================================================
  //  slideshow
  //====================================================================
  function getCookie(key) {
    //cookieから値を取得する
    var cookieString = document.cookie;

    //要素ごとに";"で区切られているので";"で切り出しを行う
    var cookieKeyArray = cookieString.split(";");

    //要素分ループを行う
    for (var i = 0; i < cookieKeyArray.length; i++) {
      var targetCookie = cookieKeyArray[i];

      //前後のスペースをカットする
      targetCookie = targetCookie.replace(/^\s+|\s+$/g, "");
      console.log(targetCookie);

      //name=kazukichi 4がはいる
      var valueIndex = targetCookie.indexOf("=");

      //substringで 0から4文字を切り取る、 name
      if (targetCookie.substring(0, valueIndex) == key) {
        //キーが引数と一致した場合、値を返す
        //sliceの第一引数で指定した文字から末尾まで切り取る 4+1 5 kazukichi
        return decodeURIComponent(targetCookie.slice(valueIndex + 1));
      }
    }
    return "";
  }

  const pics_src = [
    "images/topbaner01.jpg",
    "images/topbaner02.jpg",
    "images/topbaner03.jpg",
    "images/topbaner04.jpg",
    "images/topbaner05.jpg",
    "images/topbaner06.jpg",
    "images/topbaner07.jpg"
  ];
  let num = -1;

  function slideshow_timer() {
    if (num === 6) {
      num = 0;
    } else {
      num++;
    }
    document.cookie = "slideNum=" + num;
    document.getElementById("js-topbaner-slideshow").src =
      pics_src[getCookie("slideNum")];
  }

  if (document.URL.match(/index/)) {
    setInterval(slideshow_timer, 6000);
  }

  //====================================================================
  //  Contact Form ログイン認証など
  //====================================================================
  if (document.URL.match(/contact/)) {
    if (loginCheckContact) {
      console.log("ログイン");
      loginFlg = 1;
      console.log(loginCheckContact);
    } else {
      loginFlg = 0;
      console.log("未ログイン");
      console.log(loginCheckContact);
    }
  }

  //====================================================================
  //  nameチェック prof
  //====================================================================
  nameCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      nameFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else if ($(this).val().length > 20) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_NAME_MAX);
      nameFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      nameFlg = 1;
      validSubmit();
    }
  });

  //====================================================================
  //  emailチェック
  //====================================================================
  emailCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      emailFlg = 0; //
      validSubmit();
      //バリデーションチェックでエラー
    } else if ($(this).val().length > 255) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MAX);
      emailFlg = 0;
      validSubmit();
      //サクセス
    } else if (
      !$(this)
        .val()
        .match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)
    ) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMAIL_TYPE);
      emailFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      emailFlg = 1;
      validSubmit();
    }
  });

  //====================================================================
  //  passwordチェック
  //====================================================================
  passwordCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group"); //直近の親要素を取得
    //console.log($(this).val());
    //console.log(passwordCheck.val());
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      passwordFlg = 0;
      validSubmit();
      //半角英数字チェック
    } else if (
      !$(this)
        .val()
        .match(/^[a-zA-Z0-9]+$/)
    ) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_HALF);
      passwordFlg = 0;
      validSubmit();
      //6文字以上かチェック
    } else if ($(this).val().length < 6) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MIN);
      passwordFlg = 0;
      validSubmit();
      //255文字以下かチェック
    } else if ($(this).val().length > 255) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MAX);
      passwordFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      passwordFlg = 1;
      validSubmit();
    }
  });

  //====================================================================
  //  repasswordチェック
  //====================================================================
  rePasswordCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group"); //直近の親要素を取得
    passMatchCheck = form_g;
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      rePasswordFlg = 0;
      validSubmit();
      //半角英数字かチェック
    } else if (
      !$(this)
        .val()
        .match(/^[a-zA-Z0-9]+$/)
    ) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_HALF);
      rePasswordFlg = 0;
      validSubmit();
      //6文字以上かチェック
    } else if ($(this).val().length < 6) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MIN);
      rePasswordFlg = 0;
      validSubmit();
      //255文字以下かチェック
    } else if ($(this).val().length > 255) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MAX);
      rePasswordFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      rePasswordFlg = 1;
      validSubmit();
    }
  });

  //====================================================================
  //  old passwordチェック
  //====================================================================
  oldPasswordCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group"); //直近の親要素を取得
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      oldPasswordFlg = 0;
      validSubmit();
      //半角英数字チェック
    } else if (
      !$(this)
        .val()
        .match(/^[a-zA-Z0-9]+$/)
    ) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_HALF);
      oldPasswordFlg = 0;
      validSubmit();
      //6文字以上かチェック
    } else if ($(this).val().length < 6) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MIN);
      oldPasswordFlg = 0;
      validSubmit();
      //255文字以下かチェック
    } else if ($(this).val().length > 255) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MAX);
      oldPasswordFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      oldPasswordFlg = 1;
      validSubmit();
    }
  });

  //====================================================================
  //  messageチェック contact
  //====================================================================
  messageCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      messageFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else if ($(this).val().length < 10) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_MSG_MIN);
      messageFlg = 0;
      validSubmit();
      //255文字以下かチェック
    } else if ($(this).val().length > 200) {
      messageCounter.removeClass("has-success").addClass("has-error");
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_MESSAGE_MAX);
      messageFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      messageFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  messageBord チェック msg
  //====================================================================
  messageBordCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      messageBordFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else if ($(this).val().length < 6) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MIN);
      messageBordFlg = 0;
      validSubmit();
      //255文字以下かチェック
    } else if ($(this).val().length > 200) {
      messageCounter.removeClass("has-success").addClass("has-error");
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_MESSAGE_MAX);
      messageBordFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      messageBordFlg = 1;
      validSubmit();
    }
  });

  //====================================================================
  //  old new  password 比較
  //====================================================================
  $(".js-form-password-validate, .js-form-oldpassword-validate").on(
    "keyup change blur",
    function() {
      //console.log(passwordCheck.val());
      //console.log(rePasswordCheck.val());
      if (passwordFlg === 1 && oldPasswordFlg === 1) {
        if (passwordCheck.val() !== oldPasswordCheck.val()) {
          $(".js-oldnew-passwordMatchFlg").text("");
          oldPasswordNewMatchFlg = 1;
          validSubmit();
        } else {
          $(".js-oldnew-passwordMatchFlg").text(MSG_OLD_NEWPASSWORD_MISS);
          oldPasswordNewMatchFlg = 0;
          validSubmit();
        }
      }
    }
  );

  //====================================================================
  //  new renew  password 比較
  //====================================================================
  $(".js-form-password-validate, .js-form-repassword-validate").on(
    "keyup change blur",
    function() {
      //console.log(passwordCheck.val());
      //console.log(rePasswordCheck.val());
      if (rePasswordFlg === 1) {
        if (passwordCheck.val() !== rePasswordCheck.val()) {
          //console.log("unmuch");
          $(".js-passwordMatchFlg")
            .removeClass("has-success")
            .addClass("has-error");
          $(".js-passwordMatchFlg")
            .find(".js-help-block")
            .text(MSG_REPASSWORD_MISS);
          newPasswordConfirmMatchFlg = 0;
          validSubmit();
        } else {
          //console.log("much");
          $(".js-passwordMatchFlg")
            .removeClass("has-error")
            .addClass("has-success");
          $(".js-passwordMatchFlg")
            .find(".js-help-block")
            .text("");
          newPasswordConfirmMatchFlg = 1;
          validSubmit();
        }
      }
    }
  );
  console.log(newPasswordConfirmMatchFlg);

  //====================================================================
  //  パスワード再発行認証キーチェック
  //====================================================================
  authenticationKeyCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group"); //直近の親要素を取得
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      authPasswordFlg = 0;
      validSubmit();
      //半角英数字チェック
    } else if (
      !$(this)
        .val()
        .match(/^[a-zA-Z0-9]+$/)
    ) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_HALF);
      authPasswordFlg = 0;
      validSubmit();
      //半角英数字チェック
    } else if ($(this).val().length < 6) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MIN);
      authPasswordFlg = 0;
      validSubmit();
      //サクセス
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      authPasswordFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  telチェック
  //====================================================================
  //電話番号のバリデーション profEdit
  telCheck.on("change blur", function() {
    let form_g = $(this).closest(".js-form__group");

    //フォームの値を取得
    format_before = $(this).val();
    //ハイフン除去
    format_before = format_before.replace(/-/g, "");
    // 全角英数字を半角に変換
    let format_after = format_before.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
      return String.fromCharCode(s.charCodeAt(0) - 0xfee0);
    });

    if ($(this).val().length === 0) {
      telFlg = 0;
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      validSubmit();
    } else if (!format_after.match(/^(0[5-9]0[0-9]{8}|0[1-9][1-9][0-9]{7})$/)) {
      telFlg = 0;
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEL＿ERROR);
      validSubmit();
    } else {
      telFlg = 1;
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      validSubmit();
    }

    if (format_after.length === 11) {
      //090-1234-5678
      $(this).val(
        format_after.substr(0, 3) +
          "-" +
          format_after.substr(3, 4) +
          "-" +
          format_after.substr(7, 4)
      );
    } else if (format_after.length === 10) {
      //03-1234-5678
      $(this).val(
        format_after.substr(0, 2) +
          "-" +
          format_after.substr(2, 4) +
          "-" +
          format_after.substr(6, 4)
      );
    } else {
      //そのままの値に書き換える
      $(this).val(format_after);
    }
  });
  //====================================================================
  //  plant id チェック
  //====================================================================
  plantIdCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    console.log($(this).val());
    //未選択でエラー
    if ($(this).val() == 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_SELECT_NON);
      plantIdFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      plantIdFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  tag No チェック
  //====================================================================
  tagNoCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    console.log($(this).val());
    //未入力でエラー
    if ($(this).val().length === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      tagNoFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else if ($(this).val().length > 50) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_NAME_MAX);
      tagNoFlg = 0;
      validSubmit();
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      tagNoFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  symptoms チェック
  //====================================================================
  symptomsCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    console.log($(this).val());
    //未入力でエラー
    if ($(this).val() === 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_EMPTY);
      symptomsFlg = 0;
      validSubmit();
      //6文字未満でエラー
    } else if ($(this).val().length < 6) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_TEXT_MIN);
      symptomsFlg = 0;
      validSubmit();
      //20文字超過でエラー
    } else if ($(this).val().length > 50) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_SYMPTOMS_MAX);
      symptomsFlg = 0;
      validSubmit();
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      symptomsFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  testDay チェック
  //====================================================================
  testDayCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    // console.log("testday");
    // console.log($(this).val());
    //未選択でエラー
    if ($(this).val().length == 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_SELECT_NON);
      testDayFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      testDayFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  status チェック
  //====================================================================
  statusIdCheck.on("keyup change blur", function() {
    let form_g = $(this).closest(".js-form__group");
    console.log($(this).val());
    //未選択でエラー
    if ($(this).val() == 0) {
      form_g.removeClass("has-success").addClass("has-error");
      form_g.find(".js-help-block").text(MSG_SELECT_NON);
      statusIdFlg = 0;
      validSubmit();
      //文字数超過でエラー
    } else {
      form_g.removeClass("has-error").addClass("has-success");
      form_g.find(".js-help-block").text("");
      statusIdFlg = 1;
      validSubmit();
    }
  });
  //====================================================================
  //  submitの活性化・非活性化
  //====================================================================
  function validSubmit() {
    //contact
    if (document.URL.match(/contact/)) {
      formFlg = "contact";
      if (
        (nameFlg === 1 && emailFlg === 1 && messageFlg === 1) ||
        (loginFlg === 1 && messageFlg === 1)
      ) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //signup
    if (document.URL.match(/signup/)) {
      formFlg = "signup";
      if (
        alreadyUseEmailFlg === 1 &&
        nameFlg === 1 &&
        emailFlg === 1 &&
        passwordFlg === 1 &&
        rePasswordFlg === 1 &&
        newPasswordConfirmMatchFlg === 1
      ) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //login
    if (document.URL.match(/login/)) {
      formFlg = "login";
      if (emailFlg === 1 && passwordFlg === 1 && alreadyUseEmailFlg === 1) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //profEdit
    if (document.URL.match(/profEdit/)) {
      formFlg = "profEdit";
      if (nameFlg === 1 && telFlg === 1 && emailFlg === 1) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //passEdit
    if (document.URL.match(/passEdit/)) {
      formFlg = "passEdit";
      if (
        oldPasswordFlg === 1 &&
        passwordFlg === 1 &&
        rePasswordFlg === 1 &&
        newPasswordConfirmMatchFlg === 1 &&
        oldPasswordNewMatchFlg === 1
      ) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //passRemind Send
    if (document.URL.match(/passRemindSend/)) {
      formFlg = "passRemindSend";
      if (emailFlg === 1) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //passRemind Recieve
    if (document.URL.match(/passRemindRecieve/)) {
      formFlg = "passRemindRecieve";
      if (authPasswordFlg === 1) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //msg
    if (document.URL.match(/msg/)) {
      formFlg = "msg";
      if (messageBordFlg === 1) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
    //report
    if (document.URL.match(/report/)) {
      console.log("plantid".plantIdFlg);
      console.log("tatno".tagNoFlg);
      formFlg = "report";
      if (
        plantIdFlg === 1 &&
        tagNoFlg === 1 &&
        symptomsFlg === 1 &&
        testDayFlg === 1 &&
        nameFlg === 1 &&
        statusIdFlg === 1 &&
        messageFlg === 1
      ) {
        //活性化
        submitCheck.prop("disabled", false);
      } else {
        //非活性化
        submitCheck.prop("disabled", true);
      }
    }
  }

  //====================================================================
  //  AjaxUserCheck
  //====================================================================
  emailCheck.on("keyup change blur", function(e) {
    var $that = $(this);
    //emailのバリデーション問題なければデータベースとemailを照合する
    if (emailFlg === 1) {
      //submit選択可とし活性に戻す(disabledを戻す)
      // コールバック関数内では、thisはajax関数自体になってしまうため、
      // ajax関数内でイベントのthisを使いたいなら変数に保持しておく
      var $that = $(this);
      //console.log($that);

      // Ajaxを実行する
      $.ajax({
        type: "post",
        url: "ajaxUserCheck.php",
        dataType: "json", // 必ず指定すること。指定しないとエラーが出る＆返却値を文字列と認識してしまう
        data: {
          //emailに格納  入力フォームjs-keyup-valid-email の値を
          email: $(this).val(),
          form: formFlg
        }
        //doneは古い thenを使う
        //ajaxの処理が終わったらthenを実行する
        //dataに格納された値を使用する
      }).then(function(data) {
        console.log(data);

        //dataが入っていたら
        if (data) {
          // フォームにメッセージをセットし、背景色を変更する
          if (data.errorFlg) {
            //email重複 error
            //クラス属性をsuccess→errorに変更
            $(".js-set-msg-email").addClass("is-error");
            $(".js-set-msg-email").removeClass("is-success");
            //親クラスのラベル
            $that.closest(".js-form__group").addClass("has-error");
            $that.closest(".js-form__group").removeClass("has-success");
            //$that.addClass("is-error");
            //$that.removeClass("is-success");
            $(".js-set-msg-email").text(data.msg);
            emailFlg = 0;
            alreadyUseEmailFlg = 0;
            validSubmit();
          } else {
            //email重複なし success
            //クラス属性をerror→successに変更
            $(".js-set-msg-email").addClass("is-success");
            $(".js-set-msg-email").removeClass("is-error");
            $that.closest(".js-form__group").addClass("has-success");
            $that.closest(".js-form__group").removeClass("has-error");
            //$that.addClass("is-success");
            //$that.removeClass("is-error");
            emailFlg = 1;
            alreadyUseEmailFlg = 1;
            validSubmit();
            $(".js-set-msg-email").text(data.msg);
          }
        }
      });
      /////////
    } else {
      //エラーのためリセット
      //バリデーションに引っかかっているのでajax通信は行っていないFontAwesomeは外す
      $(".js-set-msg-email").removeClass("is-error");
      $(".js-set-msg-email").removeClass("is-success");
      //親クラスのラベル フラグの付け替え
      $that.closest(".js-form__group").addClass("has-error");
      $that.closest(".js-form__group").removeClass("has-success");
      emailFlg = 0; //リセット
      alreadyUseEmailFlg = 0;
      validSubmit(); //submitの非活性化
      $(".js-set-msg-email").text(""); //下段のerror messageを消す
    }
  });

  //====================================================================
  //  Passwordの表示/非表示を切替
  //====================================================================
  $(".js-oldpass-blind-checkbox").change(function() {
    if ($(this).prop("checked")) {
      $(".js-oldpassword-blind").attr("type", "text");
    } else {
      $(".js-oldpassword-blind").attr("type", "password");
    }
  });

  $(".js-pass-blind-checkbox").change(function() {
    if ($(this).prop("checked")) {
      $(".js-password-blind").attr("type", "text");
    } else {
      $(".js-password-blind").attr("type", "password");
    }
  });

  $(".js-repass-blind-checkbox").change(function() {
    if ($(this).prop("checked")) {
      $(".js-repassword-blind").attr("type", "text");
    } else {
      $(".js-repassword-blind").attr("type", "password");
    }
  });

  // SPメニュー
  $(".js-hamburger-menu-icn").on("click", function() {
    $(this).toggleClass("active");
    $(".js-hamburger-menu-target-toggle-class").toggleClass("active");
  });

  /*パスワード変更などのメッセージを表示する */
  if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
    jsShowMsg.slideToggle("slow");
    setTimeout(function() {
      jsShowMsg.slideToggle("slow");
    }, 5000);
  }

  /*画像エリアにドラッグ＆ドロップした画像を動的に表示する*/

  $dropArea.on("dragover", function(e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css("border", "3px #ccc dashed");
  });
  $dropArea.on("dragleave", function(e) {
    e.stopPropagation();
    1;
    e.preventDefault();
    $(this).css("border", "none");
  });
  $fileInput.on("change", function(e) {
    $dropArea.css("border", "none");
    var file = this.files[0], //2. files配列にファイルが入っている
      $img = $(this).siblings(".js-prev-img"), //3.Jqueryのsiblingsメソッドで兄弟のimgを取得
      fileReader = new FileReader(); //4.ファイルを読み込むFileReaderオブジェクト

    //5.読み込みが完了した際のイベントハンドラ。imgのsrcにデータセット
    fileReader.onload = function(event) {
      //読み込んだデータをimgに設定
      $img.attr("src", event.target.result).show();
    };
    //6.画像読み込み
    //DataURLとは画像を文字列として扱えるものでimgタグのsrc属性に画像のパスを入れる代わりに画像自体を文字列にして入れてしまうことで表示させる。
    fileReader.readAsDataURL(file);
  });

  // テキストエリアカウント
  $countUp.on("keyup", function(e) {
    $countView.html($(this).val().length);
  });

  //画像切り替え

  $switchImgSubs.on("click", function(e) {
    $switchImgMain.attr("src", $(this).attr("src"));
  });

  /*confirm*/
  /*退会時の確認*/
  $(".js-withdraw__confirm").submit(function() {
    if (!confirm("退会しますか？")) {
      return false;
    }
  });

  //お気に入り登録・削除
  var $like, likeReportId;
  //DOMがなかった場合などにundifinedが入ってしまい、後続の処理が実行されない。
  //DOMがなかった時は null を入れるようにする。
  $like = $(".js-click-like") || null; //nullというのはnull値という値で変数の中身は空ですよ。と明示するために使う。
  likeReportId = $like.data("reportid") || null;
  //数値の0はfalseと判定されてしまう。Report_idが0の場合もあり得るので0もtrueとするためにundefinedとnullを判定する。
  if (likeReportId !== undefined && likeReportId !== null) {
    $like.on("click", function() {
      var $this = $(this);
      $.ajax({
        type: "POST",
        url: "ajaxLike.php",
        data: { reportId: likeReportId }
      })
        .done(function(data) {
          //本来入れない
          console.log("Ajax Success");
          //クラス属性をtoggleで付け外しする
          //toggleファンクションで付けたり外したりできる。
          $this.toggleClass("active");
        })
        .fail(function(msg) {
          //本来入れない
          //通信先が見当たらなかったり、サーバーがダウンしてたり、phpのコード間違いなど
          console.log("Ajax Error");
        });
    });
  }

  /*掲示板ページでのみjsを発火させるため/msg/でページ指定している*/
  if (document.URL.match(/msg/)) {
    /*掲示板の最新メッセージを表示するようにスクロールする*/
    /*scrollHeightは要素のスクロールビューの高さを取得するもの*/
    var scrollheight = $(".js-scroll-bottom")[0].scrollHeight;

    $(".js-scroll-bottom").animate(
      {
        scrollTop: scrollheight
      },
      "fast"
    );
  }
});
