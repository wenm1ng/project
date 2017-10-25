function doPrint() {
	bdhtml = window.document.body.innerHTML;
	sprnstr = "<!--startprint-->"; //定义打印的开始部分
	eprnstr = "<!--endprint-->"; //定义打印的结束部分
	prnhtml = bdhtml.substr(bdhtml.indexOf(sprnstr) + 17);
	prnhtml = prnhtml.substring(0, prnhtml.indexOf(eprnstr));
	window.document.body.innerHTML = prnhtml;
	window.print();
	//				t = setTimeout(function() {
	//					window.document.body.innerHTML = bdhtml;
	//					doBase();
	//				}, 2);
}

//检查图片大小是否大于预期大小， 则显示为预期大小
function show(chkw) { //chk images width 
	if (chkw > 200) {

		chkw = 200;

	} else {

		chkw = chkw;

	}

	return chkw;

}
//点击放大图片
var imgs = document.getElementById("container").getElementsByTagName("img");
var lens = imgs.length;
var popup = document.getElementById("popup");

for (var i = 0; i < lens; i++) {
	imgs[i].onclick = function(event) {
		event = event || window.event;
		var target = document.elementFromPoint(event.clientX, event.clientY);
		showBig(target.src);
	}
}
popup.onclick = function() {
	popup.style.display = "none";
}

function showBig(src) {
	popup.getElementsByTagName("img")[0].src = src;
	popup.style.display = "block";
}