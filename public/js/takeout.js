(function(){
	// 给菜单绑定事件
	var blue = document.getElementsByClassName('font-blue');
	var bodyBox = document.getElementsByClassName("body-box");
	var Width = document.body.scrollWidth;
	var Height = document.body.scrollHeight;

	bodyBox[0].style.height = Height + "px";
	bodyBox[0].style.width = Width + "px";
	for(var i=0;i<blue.length;i++){
		blue[i].onclick = function(){
			if(this.parentNode.childNodes[2] == undefined){
				alert("已取消");
			}else{
				this.parentNode.childNodes[2].setAttribute("id","block");
				
				bodyBox[0].setAttribute("id","block");
			}
		}
	}
	// 关闭菜单
	var button = document.getElementsByClassName("button");
	for(var i=0;i<button.length;i++){
		button[i].onclick = function(){
			this.parentNode.parentNode.setAttribute("id","none");
				bodyBox[0].setAttribute("id","none");
		}
	}
})();