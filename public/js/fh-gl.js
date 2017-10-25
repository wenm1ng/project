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
				this.parentNode.childNodes[3].setAttribute("id","block");
				bodyBox[0].setAttribute("id","block");
				
		}
	}
	// 关闭菜单
	var button = document.getElementsByClassName("button");
	for(var i=0;i<button.length;i++){
		button[i].onclick = function(){
			this.parentNode.parentNode.setAttribute("id","none");
				bodyBox[0].setAttribute("id","none");
				history.go(0);
		}
	}


	// 编辑放号数
	// var countNum = document.getElementsByClassName("countNum");
	// for(var i = 0;i < countNum.length;i++){
	// 	countNum[i].onclick = function(){
	// 		if(this.innerHTML == '编辑放号数'){
	// 			var length = this.parentNode.parentNode.childNodes[5].childNodes[1].childNodes.length
	// 			for(var i=0;i<length;i++){
	// 				if(i%2 == 0 && i !=0){

	// 					var Num = this.parentNode.parentNode.childNodes[5].childNodes[1].childNodes[i].childNodes[7].innerHTML;
	// 					this.parentNode.parentNode.childNodes[5].childNodes[1].childNodes[i].childNodes[7].innerHTML = ' ';
	// 					var input = document.createElement("input");
	// 					input.setAttribute("name","type"+i);
	// 					input.setAttribute("value",""+Num);
	// 					input.style.width = 30+'px';
	// 					this.parentNode.parentNode.childNodes[5].childNodes[1].childNodes[i].childNodes[7].appendChild(input);
	// 				}
	// 			}
	// 			this.innerHTML = '完成';
	// 		}else{
	// 			date = $(this).parents('tr').find('.date').html();
	// 			alert(date);
	// 		}
	// 	}
	// }
	


})();
