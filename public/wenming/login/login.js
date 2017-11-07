//全登陆不允许iframe嵌入 
if (window.top !== window.self) { window.top.location = window.location; }

var message = {
    UninputUserName: "用户名不能为空!",
    UninputPassword: "密码不能为空!",
    UninputValidate: "验证码不能为空!",
    BlackListUser: "非常抱歉，您的账户存在异常，已被限制登录!"
};

$(document).ready(function () {
//    $("#vanclUserName,#PartnerUserName").focus(function () {
//        if ($(this).val() == "Email/手机号") {
//            $(this).attr("class", "inputtextcolor");
//            $(this).val("");
//        }
//    });

//    $("#vanclUserName,#PartnerUserName").blur(function () {
//        if ($(this).val() == "" || $(this).val() == "Email/手机号") {
//            $(this).val("Email/手机号");
//            $(this).removeAttr("class");
//        }
//    })

    $("#vanclPassword").keypress(function (event) {
        if (event.keyCode == 13) {
            return loginVanclUser();
        }
    });

    $("#PartnerPassword").keypress(function (event) {
        if (event.keyCode == 13) {
            return loginVJiaUser();
        }
    });

    $("#vanclUserName,#PartnerUserName,#vanclPassword,#PartnerPassword,#calcultatevalidate").change(function () {
        clearError();
    });

    $("#vanclLogin").click(loginVanclUser);

    $("#vjiaLogin").click(loginVJiaUser);

    $("#gotoReg").click(function () {
        //window.location = "https://login.vancl.com/login/reg.aspx?" + getUrlParam();
        window.location = "http://login.vancl.com/login/reg.aspx?" + getUrlParam();
        return false;
    });


    $("#vanclUserName").keypress(function (event) {
        if (event.keyCode == 13) {
            var userName = $("#vanclUserName");
            if (userName.val() != "Email/手机号") {
                userName.attr("class", "inputtextcolor");
                $("#vanclPassword").focus();
            }
        }
    });

    $("#PartnerUserName").keypress(function (event) {
        if (event.keyCode == 13) {
            var userName = $("#PartnerUserName");
            if (userName.val() != "Email/手机号") {
                userName.attr("class", "inputtextcolor");
                $("#PartnerPassword").focus();
            }
        }
    });
    var userName = $("#vanclUserName");
    if (userName.val() != "Email/手机号") {
        userName.attr("class", "inputtextcolor");
        $("#vanclPassword").focus();
    }
    userName = $("#PartnerUserName");
    if (userName.val() != "Email/手机号") {
        userName.attr("class", "inputtextcolor");
    }
    $('#calculatevalidate').css({ imeMode: "disabled", '-moz-user-select': "none" })
    .bind("keypress", function (e) {
        if (e.keyCode == 13) {
            return loginVanclUser();
        }
        if (e.ctrlKey == true)
            return false;
    })
    .bind("contextmenu", function () { return false; })
    .bind("selectstart", function () { return false; })
    .bind("paste", function () { return false; });
    $('#img_validate,#pValidate a').click(function () {
        $('#img_validate').attr('src', '/Controls/CalculateValidateCode.ashx?key=Login&t=' + new Date().getTime());
        $('#calculatevalidate').val('').focus();
        $('#vanclPassword').unbind('keypress');
        $("#validateError").css("visibility", "hidden");
        return false;
    }).css('cursor', 'pointer');
    $('#img_vjiaValidate,#pVjiaValidate a').click(function () {
        $('#img_vjiaValidate').attr('src', '/Controls/CalculateValidateCode.ashx?key=VjiaLogin&t=' + new Date().getTime());
        $('#vjiacalculatevalidate').val('').focus();
        $('#PartnerPassword').unbind('keypress');
        $("#vjiaValidateError").css("visibility", "hidden");
        return false;
    }).css('cursor', 'pointer');

});

function validateVanclInfo() {
    clearError();

    var isValid = true;
    var userName = $("#vanclUserName");
    var password = $("#vanclPassword");
    if (userName.val().trim() == "" || userName.val().trim() == "Email/手机号") {
        userName.focus();
        var userNameError = $("#vanclUserNameError");
        userNameError.text(message.UninputUserName);
        userNameError.css("visibility", "visible");
        isValid = false;
    } else if (password.val().trim() == "") {
        password.focus();
        var passwordError = $("#vanclPasswordError");
        passwordError.text(message.UninputPassword);
        passwordError.css("visibility", "visible");
        isValid = false;
    } else if ($('#pValidate').is(':visible') && $('#calculatevalidate').val().trim() == '') {
        $('#calculatevalidate').focus();
        var validateError = $("#validateError");
        validateError.text(message.UninputValidate);
        validateError.css("visibility", "visible");
        isValid = false;
    }
    return isValid;
}

function validateVJiaInfo() {
    clearError();

    var isValid = true;
    var userName = $("#PartnerUserName");
    var password = $("#PartnerPassword");
    if (userName.val().trim() == "" || userName.val().trim() == "Email/手机号") {
        userName.focus();
        var userNameError = $("#vjiaUserNameError");
        userNameError.text(message.UninputUserName);
        userNameError.css("visibility", "visible");
        isValid = false;
    } else if (password.val().trim() == "") {
        password.focus();
        var passwordError = $("#vjiaPasswordError");
        passwordError.text(message.UninputPassword);
        passwordError.css("visibility", "visible");
        isValid = false;
    } else if ($('#pVjiaValidate').is(':visible') && $('#vjiacalculatevalidate').val().trim() == '') {
        $('#vjiacalculatevalidate').focus();
        var validateError = $("#vjiaValidateError");
        validateError.text(message.UninputValidate);
        validateError.css("visibility", "visible");
        isValid = false;
    }

    return isValid;
}

function loginVanclUser() {
    var success = false;

    success = validateVanclInfo();
    if (success) {
        success = login();
    }

    return success;
}

function login() {
    var loginButton = $("#vanclLogin");
    //loginButton.attr("class", "loading");
    //loginButton.html("<img src='https://ssl.vanclimg.com/login/loading.gif'/>正在验证...");
    loginButton.html("正在验证...");
    loginButton.unbind("click");

    var success = false;
    //var domain = 'login.vancl.com';
    var domain = 'login.vancl.com';
    if (window.location.href.toLowerCase().indexOf('demologin') > 0) domain = 'demologin.vancl.com';
    var loginUrl = document.location.protocol + "//" + domain + "/login/XmlCheckUserName.ashx";
    //    var loginData = "Loginasync=true&LoginUserName=" + encodeURIComponent($("#vanclUserName").val().trim()) + "&UserPassword=" + encodeURIComponent($("#vanclPassword").val().trim())
    var loginData = "Loginasync=true&LoginUserName=" + encodeURIComponent($("#vanclUserName").val().trim()) + "&UserPassword=" + encodeURIComponent($.trim($("#vanclPassword").val()))
    //    + "&Validate=" + encodeURIComponent($("#calculatevalidate").val().trim()) + "&type=web";
  + "&Validate=" + encodeURIComponent($.trim($("#calculatevalidate").val())) + "&type=web";
    $.ajax({
        type: "POST",
        url: loginUrl,
        cache: false,
        async: true,
        data: loginData,
        success: function (data) {
            if (data != null && data != "" && data.Error != "") {
                //data = eval("(" + data + ")");
                var showValidate = parseInt(data.ShowValidate);
                if (showValidate == 1) {
                    $("#img_validate").click();
                    $("#pValidate,#validateError").show();
                }
                var error = data.Error;
                var errorType = data.ErrorType;
                if (error != "") {
                    var errElement = $("#vanclUserNameError");
                    switch (errorType) {
                        case 3:
                            errElement = $("#vanclLoginError");
                            break;
                        case 2:
                            errElement = $("#validateError");
                            break;
                        case 1:
                            errElement = $("#vanclPasswordError");
                            break;
                        case 0:
                            errElement = $("#vanclUserNameError");
                            break;
                    }
                    errElement.text(error);
                    errElement.css("visibility", "visible");
                }
                success = false;
            } else {
                try {
                    VA_GLOBAL.va.track(null, "va_login", "username=" + $("#vanclUserName").val()); //用户登录完成后执行
                }
                catch (ex) {
                }
                if (data != null && data != "" && data.ErrorType == '5') {
                    var url = [];
                    //url.push("ht" + "tp://login.vancl.com");
                    url.push("ht" + "tp://tes-login.vancl.com");
                    url.push("/Login/ChangePwd.aspx");
                    url.push("?");
                    url.push(getRedirectUrl());
                    $.fn.popwindow({ href: url.join(""), width: 530, height: 360, title: "操作提示", noOverlayClose: true });
                    success = false;
                }
                else {
                    var redirectUrl = getRedirectUrl();
                    //2013-8-31
                    if (new Date() < new Date(1377878400000) && /[\w\W]+@yahoo\.(cn|com\.cn)$/i.test($("#vanclUserName").val()) && !/^http(s)?:\/\/(shopping|my)\.vancl\.com/i.test(redirectUrl)) {
                        redirectUrl = 'ht' + 'tp://catalog.vancl.com/zhuanti/support_081022.html#ref=hp-hp-focus-news-v:n';
                    }
                    window.location = redirectUrl;
                    success = true;
                }
            }

            if (!success) {
                //loginButton.attr("class", "log");
                loginButton.html("登　录");
                loginButton.click(loginVanclUser);
            }
        }
    });

    return success;
}

function loginVJiaUser() {
    var loginButton = $("#vjiaLogin");
    loginButton.attr("class", "loading");
    loginButton.html("<img src='https://ssl.vanclimg.com/login/loading.gif'/>正在验证...");
    loginButton.unbind("click");

    var success = false;
    success = validateVJiaInfo();
    if (success) {

        //var domain = 'login.vancl.com';
        var domain = 'tes-login.vancl.com';
        if (window.location.href.toLowerCase().indexOf('demologin') > 0) domain = 'demologin.vancl.com';
        var loginUrl = document.location.protocol + "//" + domain;
        var vjiaValidating = loginUrl + "/user/vjiavalidate.ashx?type=web";
        var b = true;
        $.ajax({
            type: "POST",
            url: vjiaValidating,
            datatype: "json",
            cache: false,
            async: false,
            data: {
                username: $('#PartnerUserName').val().trim(),
                password: $('#PartnerPassword').val().trim(),
                Validate: $("#vjiacalculatevalidate").val().trim()
            },
            success: function (data) {
                if (data != null && data != "") {
                    if (data.Success == undefined) {
                        if (data == 'Exist') {
                            //location.href = document.location.protocol + '//login.vancl.com/login/reggl.aspx';
                            location.href = document.location.protocol + '//tes-login.vancl.com/login/reggl.aspx';
                            return;
                        }
                        data = {
                            Success: false,
                            Error: data,
                            ShowValidate: 0,
                            ErrorType: 2
                        };
                    }
                    if (data.Success == false) {
                        var showValidate = parseInt(data.ShowValidate);
                        if (showValidate == 1) {
                            $("#img_vjiaValidate").click();
                            $("#pVjiaValidate,#vjiaValidateError").show();
                        }
                        var error = data.Error;
                        var errorType = data.ErrorType;
                        if (error != "") {
                            var errElement = $("#vjiaUserNameError");
                            switch (errorType) {
                                case 2:
                                    errElement = $("#vjiaValidateError");
                                    break;
                                case 1:
                                    errElement = $("#vjiaPasswordError");
                                    break;
                            }
                            errElement.text(error);
                            errElement.css("visibility", "visible");
                        }
                        success = false;
                    }
                    else {
                        try {
                            VA_GLOBAL.va.track(null, "va_login", "username=" + $("#PartnerUserName").val()); //用户登录完成后执行
                        }
                        catch (ex) {
                        }
                        window.location = getRedirectUrl();
                        success = true;
                    }
                }
            },
            error: function (XMLHttpRequest) { $.fn.alert("服务器内部错误！"); }

        });
    }
    if (!success) {
        loginButton.attr("class", "log");
        loginButton.html("登　录");
        loginButton.click(loginVJiaUser);
    }

    return success;
}

function isBlackListUser(userName) {
    var handler = "XmlCheckUserName.ashx?CheckUserInblack=true&blackUsername=" + encodeURIComponent(userName);
    var result = false;
    $.ajax({
        url: handler,
        cache: false,
        async: false,
        datatype: "json",
        contentType: "application/json",
        success: function (data) {
            if (data != null && data != "") {
                data = eval("(" + data + ")");
                result = data.Inblack == "true" ? true : false;
            }
        }
    })

    return result;
}

function getRedirectUrl() {
    var url = getUrlParam();
    if (url == "") {
        url = "ht" + "tp://my.vancl.com";
    }

    return decodeURIComponent(url);
}

function clearError() {
    var tips = $(".tips");
    tips.text("");
    tips.css("visibility", "hidden");
}

function getUrlParam() {
    var queryString = location.search;
    if (queryString.length > 1)
        return queryString.substr(1);
    return '';
}
