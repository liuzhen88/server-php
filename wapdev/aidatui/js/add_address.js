$(function(){
    FastClick.attach(document.body);
    var key = getcookie('key');
    var name = request('name');
    var mob = request('mob');
    var address = request('addr');
    var addrNum = request('addrNum') ;
    var order = request ('order');
    var lat = request('lat');
    var lng = request('lng');

    function isPhone() {
        mob = $('#mob').val();
        var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
        if(mob===''){
            $('.message').text("请输入收货人手机号码~");
            return false;
        }else if (!reg.test(mob)) {
            $('.message').text("请输入正确的手机号码~");
            return false;
        }else  {
            $('.message').text("");
            return true;
        }
    }

    function isName(){
        name=$('#name').val();
        if(name===''){
            $('.message').text("收货人姓名不能为空~");
            return false;
        }else{
            $('.message').text("");
            return true;
        }
    }

    function isAddress(){
        address = $('#details').text();
        if(address == '请输入地址'){
            $('.message').text("请选择地址~");
            return false;
        }else{
            $('.message').text("");
            return true;
        }
    }



    if(mob == ''){
        mob = getcookie('mobile');
        if(mob != 'null'){
            $("#mob").val(mob);
        }
    }else{
        $("#mob").val(mob);
    }
    if(address != ""){
        $('#details').text(address).css('color','#666');
    }

    $("#name").val(name).blur(function(){isName()});
    $("#mob").blur(function(){isPhone()});
    $("#detailsNum").val(addrNum);
    $("#mapDetails").on('click',function(){
        window.location.href = WapSiteUrl + "/aidatui/map1.html?name="+$("#name").val()+"&mob="+$("#mob").val()+"&addrNum="+$("#detailsNum").val()+"&lat="+lat+"&lng="+lng+"&status=add&order="+order;
    });
    $("#details").on('click',function(){
        window.location.href = WapSiteUrl + "/aidatui/addr_search1.html?name="+$("#name").val()+"&mob="+$("#mob").val()+"&addrNum="+$("#detailsNum").val()+"&addr="+address+"&lat="+lat+"&lng="+lng+"&status=add&order="+order;
    });
    if (key == '') {
        window.location.href = WapSiteUrl + "/aidatui/login1.html";
    }else {
        //保存收货地址
        $(".addrBtn").click(function () {
            addrNum = $("#detailsNum").val();
            name = $("#name").val();
            mob = $("#mob").val();
            if(isPhone()&&isName()&&isAddress()){
                $.ajax({
                    url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_add&client_type=wap&key=" + key + "&true_name=" + name + "&mob_phone=" + mob  + "&address=" + address + "&door_number=" + addrNum+ "&lat=" + lat + "&lng=" + lng,
                    type: "get",
                    dataType: "jsonp",
                    jsonp: "callback",
                    success: function (data) {
                        if(data.code==200){
                            if(order=='yes'){
                                window.location.href = WapSiteUrl + "/aidatui/confirm_order1.html?address_id="+data.data.address_id;
                            }else{
                                window.location.href = WapSiteUrl + "/aidatui/address1.html";
                            }
                        }else if(data.code == 80001){
                            alert('账号已失效，请重新登录');
                            window.location.href = WapSiteUrl + "/aidatui/login1.html";
                        }else {
                            alert(data.message);
                        }
                    }
                });
            }else{
                if(!isName()){
                    isName();
                }else if(!isPhone()){
                    isPhone();
                }else if(!isAddress()){
                    isAddress();
                }
            }
        });
    }
});