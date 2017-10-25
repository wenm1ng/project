$(".ajax-post").click(function(){
	url = $('form').attr('action');
	target_form = $(this).attr('target-form');
	form = $('.'+target_form);
	query = form.serialize();

	$.post(url,query,function(data){
		if(data.status == 1){
			alert(data.info);
			window.location.href = data.url;
		}else{
			alert(data.info);
		}
	},'json')
})