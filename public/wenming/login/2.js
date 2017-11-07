var isIE6 = $.browser.msie && $.browser.version < 7;
(function($) {
    $.fn.decorateIframe = function(options) {
        if (isIE6) {
            var opts = $.extend({}, $.fn.decorateIframe.defaults, options);
            $(this).each(function() {
                var $myThis = $(this);
                var divIframe = $("<iframe src='javascript:void(0);' />");
                divIframe.attr("id", opts.iframeId);
                divIframe.css({ "position": "absolute", "display": "block", "z-index": opts.iframeZIndex, "top": 0, "left": 0 });
                if (opts.width == 0) {
                    divIframe.css("width", $myThis.width() + parseInt($myThis.css("padding")) * 2 + "px");
                }
                if (opts.height == 0) {
                    divIframe.css("height", $myThis.height() + parseInt($myThis.css("padding")) * 2 + "px");
                }
                divIframe.css("filter", "mask(color=#fff)");
                $myThis.append(divIframe);
            });
        }
    };
    $.fn.decorateIframe.defaults = {
        iframeId: "decorateIframe1",
        iframeZIndex: -1,
        width: 0,
        height: 0
    };
})(jQuery);

(function($) {
    $.fn.popwindow = function(cssOptions) {
        var cssOptions = $.extend({}, cssOptions);
        if (this.context) {
            $(this).click(function() {
                open(this);
                return false;
            });
        } else {
            open(null);
        }

        function open(e) {
            html = [];
            html.push();
            html.push("<div id=\"Overlay\">");
            if (cssOptions.title) {
                html.push("<div class=\"KmainBox\">");
                html.push("<h2 class=\"msgboxhead\">");
                html.push("    <span>" + cssOptions.title + "</span> ");
                html.push("    <a style=\"color: #fff;\" href=\"#\" class=\"close\">¹Ø±Õ</a><a href=\"#\" class=\"close\"><img src=\"https://ssl.vanclimg.com/Others/2011/2/15/dpbox_06.gif\" /></a></h2>");
            }
            html.push("    <iframe id=\"LoadedContent\" frameborder=\"0\" src='javascript:void(0);'></iframe>");
            if (cssOptions.title) {
                html.push("</div>");
            }
            html.push("</div>");
            $("body").prepend(html.join(""));
            if (!cssOptions.noOverlayClose) {
                $("#Overlay").click(function() { $(this).remove(); });
            }
            $("#Overlay").decorateIframe();
            var $LoadedContent = $("#LoadedContent");
            var url = "";
            if (cssOptions.href) {
                url = cssOptions.href;
            } else {
                url = $(e).attr("href");
            }
            $LoadedContent.attr("src", url);

            $("#Overlay").children().eq(0).css(cssOptions);
            windowresize();

            $("#Overlay .close").click(function() {
                $("#Overlay").fadeOut().remove();
                return false;
            });
        }
    };

    $.fn.popwindow.close = function() {
        $("#Overlay").fadeOut().remove();
    };

    $.fn.popwindow.resize = function(css) {
        $("#LoadedContent").css(css);
    };

    function windowresize() {
        var $LoadedContent = $("#LoadedContent");

        if (isIE6) {
            $("body").css("position", "static");
            try{
                $("#Overlay").css({
                    position: "absolute",
                    width: $(window).width(),
                    height: $(window).height(),
                    top: $(window).scrollTop()
                });
                $(window).scroll(function() {
                    $("#Overlay").css({ top: $(window).scrollTop() });
                });
            }
            catch(error){
                $("#Overlay").css({
                    position: "absolute",
                    width: $(window).width(),
                    height: $(window).height(),
                    top: document.documentElement.scrollTop || document.body.scrollTop
                });
                $(window).scroll(function() {
                    $("#Overlay").css({ top: document.documentElement.scrollTop || document.body.scrollTop });
                });
            }
        } 
        else {
            $("#Overlay").css({ position: "fixed" });
        }
        var position = {
            marginLeft: ($(window).width() - $LoadedContent.width()) / 2,
            marginTop: ($(window).height() - $LoadedContent.height()) / 2
        };
        
        if (window.opera != undefined) {
            position.marginLeft = (window.innerWidth - $LoadedContent.width()) / 2;
            position.marginTop = bodyTop + (window.innerHeight - $LoadedContent.height()) / 2;
        }
        $("#Overlay").children().eq(0).css(position);
    };
    $(window).resize(windowresize);
})(jQuery);var _callBack;

function showLoginDialog(callBackUrl, isRegMode) {
    var url = [];
    url.push(document.location.protocol + "//login.vancl.com");
    url.push("/PublicControls/LoginDiv.aspx");
    url.push("?u=");
    url.push(escape(window.location.href));
    url.push("&location=");
    url.push(escape(callBackUrl));
    if (isRegMode) {
        url.push("&regmode=true");
    }
    $.fn.popwindow({ href: url.join(""), width: 384, height: 483, title: "µÇÂ¼ ×¢²á", noOverlayClose: true });
}

function closeLoginDialog() {
    $.fn.popwindow.close();
}