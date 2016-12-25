$(function(){
    FastClick.attach(document.body);
    var key = getcookie('key');
    var addressId=request('address_id');
    var name = request('name');
    var mob = request('mob');
    var address = request('addr');
    var addrNum = request('addrNum') ;
    var order = request ('order');
    var lat = request('lat');
    var lng = request('lng');

    $.ajax({
        url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_info&client_type=wap&key=" + key + "&address_id=" + addressId,
        type: "get",
        dataType: "jsonp",
        jsonp: "callback",
        success: function (data) {
            if(data.code==200){
                if(name == ''){
                    name = data.data.address_info.true_name;
                }
                if(mob == ''){
                    mob = data.data.address_info.mob_phone;
                }
                if(address == ""){
                    address = data.data.address_info.address;
                }
                if(addrNum == ''){
                    addrNum = data.data.address_info.door_number;
                }
                if(lat == ''){
                    lat = data.data.address_info.lat;
                }
                if(lng == ''){
                    lng = data.data.address_info.lng;
                }
                $("#name").val(name).blur(function(){isName()});
                $("#mob").val(mob).blur(function(){isPhone()});
                $("#detailsNum").val(addrNum);
                $('#details').text(address).css('color','#666');

            }else if(data.code == 80001){
                alert(data.message);
                window.location.href = WapSiteUrl + "/aidatui/login1.html";
            }else {
                alert(data.message);
            }
        }
    });

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

    $("#mapDetails").on('click',function(){
        window.location.href = WapSiteUrl + "/aidatui/map1.html?name="+$("#name").val()+"&mob="+$("#mob").val()+"&addrNum="+$("#detailsNum").val()+"&lat="+lat+"&lng="+lng+"&status=edit&address_id=" + addressId+"&order="+order;
    });
    $("#details").on('click',function(){
        window.location.href = WapSiteUrl + "/aidatui/addr_search1.html?name="+$("#name").val()+"&mob="+$("#mob").val()+"&addrNum="+$("#detailsNum").val()+"&addr="+address+"&lat="+lat+"&lng="+lng+"&status=edit&address_id=" + addressId+"&order="+order;
    });
    if (key == '') {
        window.location.href = WapSiteUrl + "/aidatui/login1.html";
    } else {
        //保存收货地址
        $(".addrBtn").click(function () {
            addrNum = $("#detailsNum").val();
            name = $("#name").val();
            mob = $("#mob").val();
            if(isPhone()&&isName()){
                $.ajax({
                    url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_edit&client_type=wap&key=" + key + "&address_id=" + addressId + "&true_name=" + name + "&mob_phone=" + mob  + "&address=" + address + "&door_number=" + addrNum + "&lat=" + lat + "&lng=" + lng,
                    type: "get",
                    dataType: "jsonp",
                    jsonp: "callback",
                    success: function (data) {
                        if(data.code==200){
                            if(order=='yes'){
                                window.location.href = WapSiteUrl + "/aidatui/confirm_order1.html?address_id="+addressId;
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
                }
            }
        });
    }
});