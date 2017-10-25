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

//点击放大图片