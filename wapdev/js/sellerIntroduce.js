$(document).ready(function(){
	var store_id=request("store_id");
	$.ajax({
		url:ApiUrl+"/index.php?act=unlimited_invitation&op=store_detail&client_type=wap&store_id="+store_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success: function(data){
			if(data.code==200){
				/*var subdiv="<img src='"+data.data.store_info.image+"'/>";
				$("#native").append(subdiv);
				var details="<div>"+data.data.store_info.store_name+"</div>";
				var id="<div>店铺的id是:"+data.data.store_info.store_id+"</div>";
				$("#native").append(details);
				$("#native").append(id);*/
				var describe="<div>"+data.data.store_info.store_describe+"</div>";
				$("#native").append(describe);
			}
		}	
	});	
});