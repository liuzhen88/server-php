$(function(){
    FastClick.attach(document.body);
    var key = getcookie('key');
    var order = request ('order');
    if (key == '') {
        window.location.href = WapSiteUrl + "/aidatui/login.html";
    } else {
        //地址模板渲染
        $.ajax({
            url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_list&client_type=wap&key=" + key  ,
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if(data.code==200){
                    if(order=='yes'){
                        data.data.order='yes';
                        $('#addLink').attr('href','add_address.html?order=yes');
                    }else{
                        data.data.order='no';
                    }
                    data.data.address_id = localStorage.getItem("addrId");
                    var addrListTmpl = doT.template($("#addrListTmpl").html());
                    $("#addressList").append(addrListTmpl(data.data));

                }else if(data.code == 80001){
                    alert(data.message);
                    window.location.href = WapSiteUrl + "/aidatui/login.html";
                }else {
                    alert(data.message);
                }
                //删除地址
                $('.del').on('click',function(){
                    if(confirm("确定要删除地址吗？")){
                        var addrId=$(this).attr('data-addrId');
                        var parentLi=$(this).parents('li');
                        $.ajax({
                            url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_del&client_type=wap&key=" + key +"&address_id=" + addrId,
                            type: "get",
                            dataType: "jsonp",
                            jsonp: "callback",
                            success: function (data) {
                                if (data.code==200){
                                    parentLi.remove();
                                }else{
                                    alert(data.message);
                                }
                            }
                        });
                    }
                })
            }
        });

    }


});