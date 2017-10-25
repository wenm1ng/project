
function isArray(o){
return Object.prototype.toString.call(o)=='[object Array]';
}
// 存储的就是各地的地区
		var list = [];
		list[0] = ['海定','昌平','朝阳','三里屯'];
		list[1] = ['静安','普陀','闸北','松江'];
		list[2] = ['天河','海珠区','白云','越秀'];
		list[3] = ['罗湖','保安','福田','南山'];

	/*	list[0][0][0] = ['长春桥','魏公村','白石桥','车道沟'];
		list[0][1][1] = ['南邵镇','昌翠路','东崔村','南邵中学'];
		list[0][2][2] = ['十里宝','青年路','朝阳北','朝阳东'];
		list[0][3][3] = ['西门','东门','倍门','南门'];
		list[1][0][0] = ['上海火车站','汉中路','南京路','宝山路'];
		list[1][1][1] = ['真如','上海西路','长征','梅春路'];
		list[1][2][2] = ['真如','上海西路','长征','梅春路'];
		list[1][3][2] = ['松江老城','谷阳北路','广富林路','永丰'];


		list[2][0][0] = ['岗顶','天河汽车站','燕塘','广州东站'];
		list[2][1][1] = ['珠海','天堂','南唐','北塘'];
		list[2][2][2] = ['白云机场','白云公园','白云公寓','白云路'];
		list[2][3][3] = ['区庄','动物园','淘金','黄花岗'];

		list[3][0][0] = ['东门','京基','国贸','深圳火车站'];
		list[3][1][1] = ['沙井','马鞍山','桥头','福永'];
		list[3][2][2] = ['竹子林','购物公园','车公庙','华侨城'];
		list[3][3][3] = ['后海','前海','蛇口','科技园'];*/
		// console.log(list);
		// 1.找对象
		var area = document.getElementById('area');

		var city = document.getElementById('city');

		var son = document.getElementById('son');
		area.onchange = function(){
			// alert(this.value);
			city.length = 1;

			// alert(list[this.value]);
			var arealist = list[this.value];
			for(var i = 0; i < arealist.length; i++){
				// console.log(isArray(arealist[i]));
				// 创建option
				var options = document.createElement('option');
				// 把地区添加到option标签内
				// if(isArray(arealist[i]) == false){
					options.innerHTML = arealist[i];
					options.value = i;
					// 添加城市列表 
					city.appendChild(options);
				// }
				
			}
		}
		// city.onchange = function(){
		// 	// // alert(this.value);
		// 	// son.length = 1;

		// 	// // alert(list[this.value]);
		// 	// var arealist = list[area.value][this.value];
		// 	// console.log(list[area.value]);
		// 	// for(var i = 0; i < arealist.length; i++){
		// 	// 	console.log(arealist[i]);

		// 	// 	// 创建option
		// 	// 	var options = document.createElement('option');
		// 	// 	// 把地区添加到option标签内
		// 	// 	options.innerHTML = arealist[i];
		// 	// 	// 添加城市列表 
		// 	// 	son.appendChild(options);
		// 	}
		// }