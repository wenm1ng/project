(function(){
	var tablePage = document.getElementById('tablePage');
	var tr = tablePage.childNodes[1].childNodes;
	var result = new Array();
	var count = document.getElementById("countnum").value;

	for(var i = 1;i < tr.length;i++){
		if(i%2 == 1){
		}else{
			result[i/2] = tr[i];
		}
	}
	
	//统计数量
	var length = result.length;
	// 统计分页数量
	var page = Math.ceil(length/count);
	// 如果是整数则减一
	if(length%count==1){
		page = page-1;
	}
	// 输出的分页
	var msg = '<li class="disabled"><a href="javascript:void(0)" onclick="touch(0)">&laquo;</a></li>';
	for(var p = 1;p <= page;p++){
		msg += '<li class="active"><a href="javascript:void(0)" onclick="page('+p+',this)">'+p+'</a></li>';
	}
	msg += '<li class="disabled"><a href="javascript:void(0)" onclick="touch(1)">&raquo;</a></li>';
	var ulPage = document.getElementById("page");
	ulPage.innerHTML = msg;
	// 输出结束

	// 开始数据分离
	// console.log(result[20])
	for(var c = 1;c < length;c++){
		if(c>count){
			// console.log(c)
		result[c].style.display = "none";
		}
	}
	// 停止数据分离
	var ulPage = document.getElementById("page");
	// 第一个按钮
	ulPage.childNodes[1].childNodes[0].setAttribute("id","page-touch");
	// 总条数，每页条数
		var show = document.getElementById("number-show");
		length = length -1;
		msg = "共"+ length +"条。每页显示"+ count +"条";
		show.childNodes[0].innerHTML = msg;
})();
function page(num,o){
	var tablePage = document.getElementById('tablePage');
	var tr = tablePage.childNodes[1].childNodes;
	var result = new Array();
	var count = document.getElementById("countnum").value;

	for(var i = 1;i < tr.length;i++){
		if(i%2 == 1){
		}else{
			result[i/2] = tr[i];
		}
	}
	//统计数量
	var length = result.length;
	// 统计分页数量
	var page = Math.ceil(length/count);
	// 如果是整数则减一
	if(length%count==1){
		page = page-1;
	}
	min = (num-1)*count;
	max = (num)*count;
	for(var c = 1;c < length;c++){
		if(c>min && c<=max){
			result[c].setAttribute("style",'');
		}else{
			// console.log(result[c])
			result[c].style.display = "none";
		}
	}

	// 按钮上色
	var ulPage = document.getElementById("page");
	for(var i = 0; i < ulPage.childNodes.length; i++){
		ulPage.childNodes[i].childNodes[0].setAttribute("id","");
		if(i==num){
			ulPage.childNodes[i].childNodes[0].setAttribute("id","page-touch");
		}
	}
}
function touch(sw){
	if(sw == 0){
		var tablePage = document.getElementById('tablePage');
		var tr = tablePage.childNodes[1].childNodes;
		var result = new Array();
		var count = document.getElementById("countnum").value;
		// 获取当前页码
		var pagege = document.getElementById("page-touch").innerHTML;
		pagege -= 1;
		if(pagege<1){
			pagege = 1;
		}
		for(var i = 1;i < tr.length;i++){
			if(i%2 == 1){
			}else{
				result[i/2] = tr[i];
			}
		}
		//统计数量
		var length = result.length;
		// 统计分页数量
		var page = Math.ceil(length/count);
		// 如果是整数则减一
		if(length%count==1){
			page = page-1;
		}
		min = (pagege-1)*count;
		max = (pagege)*count;
		for(var c = 1;c < length;c++){
			if(c>min && c<=max){
				result[c].setAttribute("style",'');
			}else{
				// console.log(result[c])
				result[c].style.display = "none";
			}
		}

		// 按钮上色
		var ulPage = document.getElementById("page");
		for(var i = 0; i < ulPage.childNodes.length; i++){
			ulPage.childNodes[i].childNodes[0].setAttribute("id","");
			if(i==pagege){
				ulPage.childNodes[i].childNodes[0].setAttribute("id","page-touch");
			}
		}
		
	}else{
		var tablePage = document.getElementById('tablePage');
		var tr = tablePage.childNodes[1].childNodes;
		var result = new Array();
		var count = document.getElementById("countnum").value;
		// 获取当前页码
		var pagege = document.getElementById("page-touch").innerHTML;
		pagege = parseInt(pagege) + 1;
		for(var i = 1;i < tr.length;i++){
			if(i%2 == 1){
			}else{
				result[i/2] = tr[i];
			}
		}
		//统计数量
		var length = result.length;
		// 统计分页数量
		var page = Math.ceil(length/count);
		if(page < pagege){
			pagege = page;
		}
		// 如果是整数则减一
		if(length%count==1){
			page = page-1;
		}
		min = (pagege-1)*count;
		max = (pagege)*count;
		for(var c = 1;c < length;c++){
			if(c>min && c<=max){
				result[c].setAttribute("style",'');
			}else{
				// console.log(result[c])
				result[c].style.display = "none";
			}
		}

		// 按钮上色
		var ulPage = document.getElementById("page");
		for(var i = 0; i < ulPage.childNodes.length; i++){
			ulPage.childNodes[i].childNodes[0].setAttribute("id","");
			if(i==pagege){
				ulPage.childNodes[i].childNodes[0].setAttribute("id","page-touch");
			}
		}
	}
}

function count(){
		var tablePage = document.getElementById('tablePage');
		var tr = tablePage.childNodes[1].childNodes;
		var result = new Array();
		var count = document.getElementById("countnum").value;
		// 获取当前页码
		var pagege = document.getElementById("page-touch").innerHTML;
		pagege = parseInt(pagege);
		for(var i = 1;i < tr.length;i++){
			if(i%2 == 1){
			}else{
				result[i/2] = tr[i];
			}
		}
		//统计数量
		var length = result.length;
		// 统计分页数量
		var page = Math.ceil(length/count);
		if(page < pagege){
			pagege = page;
		}
		// 如果是整数则减一
		if(length%count==1){
			page = page-1;
		}
		// 输出的分页
		var msg = '<li class="disabled"><a href="javascript:void(0)" onclick="touch(0)">&laquo;</a></li>';
		for(var p = 1;p <= page;p++){
			msg += '<li class="active"><a href="javascript:void(0)" onclick="page('+p+',this)">'+p+'</a></li>';
		}
		msg += '<li class="disabled"><a href="javascript:void(0)" onclick="touch(1)">&raquo;</a></li>';
		var ulPage = document.getElementById("page");
		ulPage.innerHTML = msg;
		// 输出结束
		min = (pagege-1)*count;
		max = (pagege)*count;
		for(var c = 1;c < length;c++){
			if(c>min && c<=max){
				result[c].setAttribute("style",'');
			}else{
				// console.log(result[c])
				result[c].style.display = "none";
			}
		}

		// 按钮上色
		var ulPage = document.getElementById("page");
		for(var i = 0; i < ulPage.childNodes.length; i++){
			ulPage.childNodes[i].childNodes[0].setAttribute("id","");
			if(i==pagege){
				ulPage.childNodes[i].childNodes[0].setAttribute("id","page-touch");
			}
		}

		// 总条数，每页条数
		var show = document.getElementById("number-show");
		length = length -1;
		msg = "共"+ length +"条。每页显示"+ count +"条";
		show.childNodes[0].innerHTML = msg;
		
}

//提示用户，提示框
/*
	根据返回的ID重新写验证
	id
	1 打包
	2 就餐剩余时间
	3 迟到就餐
	4 超时就餐
*/
function msgUser(id,o){
	if(o.getAttribute("checked") !== "true"){
		var con;
		con=confirm("是否已为顾客打包？"); //在页面上弹出对话框
		if(con==true){
			switch(id){
				case 1:
				msg = "已完成 打包";
				break;
				case 2:
				msg = "已完成 堂食";
				break;
				case 3:
				msg = "已完成 逾期";
				break;
				case 4:
				msg = "已完成 超时";
				break;
			}
			o.innerHTML = msg;
			o.setAttribute("checked","true");
			alert("已发送!");
		}else{
			alert("已取消!");
		}
	}
}