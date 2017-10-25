$.fn.AjaxLoadPage = function (opt) {
    var sender = $(this);
    var defaultOpt = {
        cache: false,
        position: "replace"
    };
    if (opt) {
        defaultOpt = jQuery.extend(defaultOpt, opt);
    }
    $.ajax({
        dataType: "html",
        url: defaultOpt.url,
        success: function (html) {
            if (defaultOpt.position == "replace") {
                $(sender).replaceWith(html);
            } else {
                $(sender).empty().append(html);
            }

            if (defaultOpt.callback) {
                defaultOpt.callback();
            }
        },
        error: function (msg) {
        }
    });
}