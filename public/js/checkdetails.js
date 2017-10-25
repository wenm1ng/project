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
			var tblue = this;
			if(this.parentNode.childNodes[2] == undefined){
				alert("已取消");
			}else{
				var oid = this.getAttribute("oid");
				var url = document.getElementById("url").value;
				console.log(url);
				$.ajax({
					type:'POST',
					url:url,
					data:'oid='+oid,
					success:function(data){
						// console.log(data[0]['foodname'])
						var length = data.length;
						msg = '<li><h4>查看菜单</h4></li>';
						for(var i=0;i<length;i++){
							msg += '<li><div id="food-name">' +data[i]["foodname"] + ' X'+ data[i]["foodnum"] + '</div><div id="food-pay">' + data[i]["foodprice"] + '</div></li>'
						}
						msg += '<li class="button" onclick="closeFood(this)"><div>关闭</div></li>';
						tblue.parentNode.childNodes[2].childNodes[1].innerHTML = msg;
					},
					dataType:'json'
				});
				this.parentNode.childNodes[2].setAttribute("id","block");
				bodyBox[0].setAttribute("id","block");
			}
		}
	}
	// 关闭菜单
	// var button = document.getElementsByClassName("button");
	// for(var i=0;i<button.length;i++){
	// 	button[i].onclick = function(){
	// 		this.parentNode.parentNode.setAttribute("id","none");
	// 			bodyBox[0].setAttribute("id","none");
	// 	}
	// }
})();
function closeFood(o)
{
	var bodyBox = document.getElementsByClassName("body-box");
	var button = document.getElementsByClassName("button");
	o.parentNode.parentNode.setAttribute("id","none");
	bodyBox[0].setAttribute("id","none");
}
