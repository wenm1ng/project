var sendSmsCodeStyle = function (type) {
    if (type == 0) {
        //默认状态
        $("#getSmsCode").css("display", "block");
        $("#sendingSmsCode").css("display", "none");
        $("#sendedSmsCode").css("display", "none");
    }
    else if (type == 1) {
        //正在发送状态
        $("#getSmsCode").css("display", "none");
        $("#sendingSmsCode").css("display", "block");
        $("#sendedSmsCode").css("display", "none");
    }
    else if (type == 2) {
        //已发送成功状态
        $("#getSmsCode").css("display", "none");
        $("#sendingSmsCode").css("display", "none");
        $("#sendedSmsCode").css("display", "block");
    }
};

var sendsmscode = function (phone, piccode, callback) {
    $.ajax({
        url: 'https://login.vancl.com/login/quickLogin.ashx?action=sendmobilecode&key=' + encodeURIComponent(phone) + '&piccode=' + encodeURIComponent(piccode),
        cache: false,
        async: true,
        datatype: "json",
        beforeSend: function () {
            //            $('.inviteTips_img a').hide().after("<img src='https://ssl.vanclimg.com/login/loading.gif' align='absmiddle'/>");
            //            $('.inviteTips_ft').html('');
            sendSmsCodeStyle(1);
        },
        success: function (data) {
            if (data != null && data != "" && data != "undefined") {
                //                if (typeof data.errortype == 'undefined') {
                //                    data = eval('(' + data + ')');
                //                }
                //                showError($('#input_phone'), data.message);
                //                $('.inviteTips_img a').show();
                //                if (data.errortype == 'imgValidate') {
                //                    $('.iFloatingwindow').show();
                //                    $('#img_phonevalidate').click();
                //                    $('#input_phonevalidate').focus();
                //                    $('.inviteTips_ft').html('<img src="https://ssl.vanclimg.com/login/inviteico1.gif">请输入验证码');
                //                } else {
                //                    $('.iFloatingwindow').hide();
                //                }
                sendSmsCodeStyle(0);
                var result = $.parseJSON(data); //JSON.parse(data);
                if (result.errortype != "imgValidate") {
                    if (result.message != undefined && result.message != null && result.message != "") {
                        $("#err_phone").css("visibility", "visible");
                        $("#err_phone").html(result.message);
                    }
                }
                else {
                    $("#_quickcode").css("display", "block");
                    $("#_quickpiccodevalidmsg").css("display", "block");
                    $("#_quickpiccodevalidmsg").css("visibility", "visible");
                    $("#_quickpiccodeimg").attr('src', '/Controls/CalculateValidateCode.ashx?key=quicklogin&t=' + new Date().getTime());
                    $("#_quickpiccodevalidmsg").html(result.message);
                }
            }
            else {
                var time = 120;
                var interval = setInterval(function () {
                    if (time == 0) {
                        sendSmsCodeStyle(0);
                        clearInterval(interval);
                        //$('.v2regList_Btnimg').hide();
                        //$('.asPhoneregBtn').show().html('重新获取验证码');
                        //$('#input_phone').removeAttr('disabled');
                        //clearInterval(interval);
                        //showTips($('#input_phone'), '');
                    }
                    else {
                        //showTips($('#input_phone'), '尊敬的用户，请耐心等待，如果在<font color="red">' + time + '</font>秒内没有收到验证码，请重新验证');
                        //$('.asPhoneregBtn').hide();
                        //$('#input_phone').attr('disabled', 'disabled');
                        //$('.v2regList_Btnimg').show().html('<img align="absmiddle" src="https://ssl.vanclimg.com/login/loading.gif">正在发送');
                        sendSmsCodeStyle(2);
                        var msg = time.toString() + "秒后重新获取";
                        $("#sendedSmsCode").html(msg);
                        time--;
                    }
                }, 1000);
            }
        }
    });
};



var checkphone = function (callback) {
    var phone = $("#_quickmobilenumber").val();
    var piccode = $("#_quickpiccode").val();
    if (!/^(13|15|18|14|17)\d{9}$/.test(phone)) {

        callback(false, phone, "", "请输入有效的手机号");
        return;
    }
    else {
        callback(true, phone, piccode, "");
        return;
    }
};

var checkPhoneCallback = function (success, phone, piccode, message) {
    if (success) {
        $("#err_phone").css("visibility", "hidden");
        $("#err_phone").html("");
        sendsmscode(phone, piccode, null);
    }
    else {
        $("#_quickmobilenumber").focus();
        $("#err_phone").css("visibility", "visible");
        $("#err_phone").html(message);
    }
};

var func_quickLogin = function () {
    var returnurl = "http://my.vancl.com";
    if (window.parent != window) {
        //外站登录
        returnurl = window.parent.location.href;
    }
    else {
        //内站
        var turl = window.location.href.split('?');
        if (turl != undefined && turl != null && turl.length == 2) {
            returnurl = turl[1];
        }
    }
    var code = $('#_quickmobilevalidcode').val();
    var piccode = $('#_quickpiccode').val();
    $.ajax({
        url: 'https://login.vancl.com/login/quickLogin.ashx?action=login&key=' + encodeURIComponent($('#_quickmobilenumber').val()) + '&valicode=' + encodeURIComponent(code) + '&piccode=' + encodeURIComponent(piccode) + '&url=' + encodeURIComponent(returnurl),
        cache: false,
        async: true,
        datatype: "json",
        beforeSend: function () {
            $("#_btnquicklogin").unbind("click");
            $("#_btnquicklogin").text("正在验证...");
        },
        success: function (data) {
            $("#_btnquicklogin").text("登录");
            $("#_btnquicklogin").bind("click", func_quickLogin);
            if (data != undefined && data != null && data != "") {
                var json = $.parseJSON(data); //JSON.parse(data);
                if (json.errortype == "success") {
                    $("#_quickpiccodevalidmsg").css('visibility', 'hidden');
                    $("#_quickmobilecodemsg").css('visibility', 'hidden');
                    $("#err_phone").css('visibility', 'hidden');

                    var url = json.message;
                    var wnd = window;
                    if (window.parent != window) {
                        wnd = window.parent;
                    }
                    if (url != undefined && url != null && url != "") {
                        wnd.location.href = url;
                    }
                    else {
                        wnd.location.href = "http://my.vancl.com";
                    }

                }
                else if (json.errortype == "validCodeErr") {
                    $("#_quickpiccode").val('');
                    $("#_quickpiccodeimg").attr('src', '/Controls/CalculateValidateCode.ashx?key=quicklogin&t=' + new Date().getTime());
                    $("#_quickpiccodevalidmsg").html(json.message);
                    $("#_quickpiccodevalidmsg").css('visibility', 'visible');
                }
                else if (json.errortype == "name") {
                    $("#_quickmobilecodemsg").html(json.message);
                    $("#_quickmobilecodemsg").css('visibility', 'visible');
                }
                else if (json.errortype == "validate") {
                    $("#err_phone").html(json.message);
                    $("#err_phone").css('visibility', 'visible');
                }
            }
        }
    });
};


$(document).ready(function () {
    $("#getSmsCode").click(function () { checkphone(checkPhoneCallback); });

    $("#_btnquicklogin").bind("click", func_quickLogin);

    $("#_quickpiccodeimg").attr('src', '/Controls/CalculateValidateCode.ashx?key=quicklogin&t=' + new Date().getTime());

    $('#_quickcode a').click(function () {
        $("#_quickpiccodeimg").attr('src', '/Controls/CalculateValidateCode.ashx?key=quicklogin&t=' + new Date().getTime());
    });
});