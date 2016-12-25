$(function () {

    FastClick.attach(document.body);

	$(".report_list li").click(function () {
	    if ($(this).hasClass("on")){
	        $(this).removeClass('on');
	    } else {
	       $(".report_list li").removeClass('on');
	       $(this).addClass('on');
	    }
    });

    var theme_id =request("theme_id");

    $(".report_list li").on("click",function(){
        var comment=$(this).text();
            $.ajax({
                url: ApiUrl + "/index.php?act=circle_info&op=reportTheme&key="+getcookie('key')+"&theme_id="+theme_id+"&comment="+comment,
                type: 'get',
                dataType: 'jsonp',
                success: function(result) {
                    if (result.code == 200) {
                        if(result.message){
                            alert(result.message);
                        }else if(result.datas.error){
                            alert(result.datas.error);
                        }
                        history.back();
                    }
                }
            });
    });


});