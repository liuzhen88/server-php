<?php defined('emall') or exit('Access Invalid!');?>

<div class="page new">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['store'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=store&op=store"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="index.php?act=store&op=store_joinin" ><span><?php echo $lang['pending'];?></span></a></li>
        <li><a href="index.php?act=store&op=reopen_list" ><span>续签申请</span></a></li>
        <li><a href="index.php?act=store&op=store_bind_class_applay_list" ><span>经营类目申请</span></a></li>
          <li><a href="index.php?act=store&op=store_joinin_o2o" ><span>本土开店申请</span></a></li>
          <li><a href="index.php?act=store&op=adt_add_store" class="current"><span>新增配送店铺</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <div class="step step1">
      <div class="step_show">

      </div>
      <div class="step_text">
          <div class="txt">设置用户名</div>
          <div class="txt">填写商户信息</div>
          <div class="txt">设置银行信息</div>
          <div class="txt">设置成功</div>
          <div class="clearfix"></div>
      </div>
      <div class="clearfix"></div>
  </div>

    <form action="index.php?act=store&op=adt_add_store&form_submit=ok" method="POST" name="new_form" class="new_form" enctype="multipart/form-data">
        <div class="content content1">
            <table >
                <tr>
                    <td class="t"><span>*</span>手机号码：</td>
                    <td>
                        <input type="text" maxlength="11" id="member_name" name="data[member_name]">
                        <div class="t_text tip none"></div>
                        <div class="t_text warn none"></div>
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr class="psd_tr none">
                    <td class="t text_right"><span>*</span>密码：</td>
                    <td>
                        <input type="password" id="member_passwd" name="data[member_passwd]">
                        <div class="t_text tip none"></div>
                        <div class="t_text warn none"></div>
                        <div class="t_text error none"></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content content2 none">
            <table >
                <tr>
                    <td class="t text_right"><span>*</span>商户名称：</td>
                    <td>
                        <input type="text"  name="data[store_name]" id="store_name">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr class="psd_tr ">
                    <td class="t text_right"><span>*</span>店主姓名：</td>
                    <td>
                        <input type="text" name="data[true_name]" id="true_name">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr>
                    <td class="t text_right"><span>*</span>联系方式：</td>
                    <td>
                        <input type="text"  id="contact_phone" name="data[contact_phone]" placeholder="手机或固话">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr class="psd_tr">
                    <td class="t text_right">配送时间：</td>
                    <td>
                        <select name="b_hour" id="b_hour">

                        </select>
                        <span>:</span>
                        <select name="b_minute" id="b_minute">

                        </select>
                        <span>至</span>
                        <select name="e_hour" id="e_hour">

                        </select>
                        <span>:</span>
                        <select name="e_minute" id="e_minute">

                        </select>
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr class="address_select">
                    <td class="t text_right"><span>*</span>商户地址：</td>
                    <td>
                        <select name="data[province_id]" id="province">

                        </select>
                        <select name="data[city_id]" id="city">

                        </select>

                        <select name="data[area_id]" id="district">

                        </select>
                        <div class="t_text error none"></div>
                    </td>
                    <input type="hidden" name="data[province_name]" id="province_name">
                    <input type="hidden" name="data[city_name]" id="city_name">
                    <input type="hidden" name="data[area_name]" id="area_name">
                </tr>
                <tr class="psd_tr ">
                    <td class="t text_right"></td>
                    <td>
                        <input type="text" id="address_detail" name="data[address_detail]" placeholder="详细地址">
                        <div class="t_text error none add_error"></div>
                        <div class="get_location text_center">定位</div>
                        <div id="allmap"></div>
                        <div class="du">经纬度：<span id="lng" name="data[lng]"></span>，<span id="lat" name="data[lat]"></span></div>
                        <input type="hidden" value="" name="data[lng]" class="lng">
                        <input type="hidden" value="" name="data[lat]" class="lat">
                    </td>
                </tr>
                <tr>
                    <td class="t text_right"><span>*</span>营业执照：</td>
                    <td>
                        <input type="text"  name="data[licence_number]" id="licence_number">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr>
                    <td class="t text_right"><span>*</span>法人：</td>
                    <td>
                        <input type="text"  name="data[legal_person]" id="legal_person">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr style="vertical-align: top">
                    <td class="t text_right"><span>*</span>上传营业执照：</td>
                    <td>
                        <div class="upload_file_tip">请上传2M以内照片</div>
                        <input type="file" name="licence_file" id="licence_file" onchange="check_file_size()" value="浏览">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content content3 none">
            <table >
                <tr>
                    <td class="t text_right"><span>*</span>开户名称：</td>
                    <td>
                        <input type="text"  name="data[bank_user]" id="storeName_">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr class="psd_tr ">
                    <td class="t text_right"><span>*</span>开户银行：</td>
                    <td>
                        <input type="text" name="data[bank_name]" id="bank_name">
                        <div class="t_text error none"></div>
                    </td>
                </tr>
                <tr>
                    <td class="t text_right"><span>*</span>对公账户：</td>
                    <td>
                        <input type="text"  id="bank_no" name="data[bank_no]" >
                        <div class="t_text error none"></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="btn_area">
            <div class="prev_btn  text_center btn">上一步</div>
            <div class="next_btn  text_center can_next s_1 btn">下一步</div>
            <div class="clearfix"></div>
        </div>

        <div class="clearfix"></div>
    </form>
</div>
<style type="text/css">
    #allmap {width: 465px;height: 300px;overflow: hidden;margin:24px 0;font-family:"微软雅黑";}
</style>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=uLhko8NKBiREseUxAWE0hVYc"></script>
<script type="text/javascript">
    // 百度地图API功能。初始化地图
    var map = new BMap.Map("allmap");    // 创建Map实例
    var aigegou=new BMap.Point(120.546131,31.28169);
    map.centerAndZoom(aigegou, 18);  // 初始化地图,设置中心点坐标和地图级别
    //    map.addControl(new BMap.MapTypeControl());   //添加地图类型控件
    map.setCurrentCity("苏州市");          // 设置地图显示的城市 此项是必须设置的
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
    //创建点
    var marker=new BMap.Marker(aigegou);
    map.addOverlay(marker);
    marker.enableDragging();    //可拖拽
    //    marker.setAnimation(BMAP_ANIMATION_BOUNCE);   跳动
    //拖拽停止监听
    marker.addEventListener("dragend", function(obj) {
        $('#lng').html(obj.point.lng);
        $('#lat').html(obj.point.lat);

    });

</script>
<script>
    var model={
        content1_t_text:["请输入商户注册使用的手机号码","该手机已经注册爱个购帐号，商户密码为账户原密码","该手机号未注册过，请为商户设置初始密码",
            "该手机号已经添加过商户，无法再次添加","请输入6~20位数字、字母"],
        isCheckPsd:false,
        checkPsd:function(psdstr){
            var reg=/^\w{6,20}$/;
            if(reg.test(psdstr)) return true;
        },
        checkPhone:function(phonestr){
            var reg=/^[]{11}$/;
            if(reg.test(phonestr)) return true;
        }
    };
    $(".next_btn").on("click",function(e){
        var el=this;
        if(this.className.indexOf("s_1")>-1)
        {
            var $phone=$("#member_name"),
                $psd=$("#member_passwd"),
                member_name=$phone.val(),
                member_passwd=$psd.val();
            //验证
            if(!member_name)
            {
                $phone.parent().find(".tip").html(model.content1_t_text[0]);
                $phone.parent().find(".t_text").addClass("none");
                $phone.parent().find(".tip").removeClass("none");
                return;
            }
            else
            {
                if(isNaN(member_name))
                {
                    $phone.parent().find(".tip").html(model.content1_t_text[0]);
                    $phone.parent().find(".t_text").addClass("none");
                    $phone.parent().find(".tip").removeClass("none");
                    return;
                }
                else
                {
                    if(member_name.toString().length!==11)
                    {
                        $phone.parent().find(".tip").html(model.content1_t_text[0]);
                        $phone.parent().find(".t_text").addClass("none");
                        $phone.parent().find(".tip").removeClass("none");
                        return;
                    }
                }
            }
            if(model.isCheckPsd)
            {
                //验证密码
                if(!member_passwd)
                {
                    $psd.parent().find(".tip").html(model.content1_t_text[4]);
                    $psd.parent().find(".t_text").addClass("none");
                    $psd.parent().find(".tip").removeClass("none");
                    return;
                }
                else
                {
                    if(!model.checkPsd(member_passwd))
                    {
                        $psd.parent().find(".error").html(model.content1_t_text[4]);
                        $psd.parent().find(".t_text").addClass("none");
                        $psd.parent().find(".error").removeClass("none");
                        return;
                    }
                }
                //清空所有提示
                $(".t_text").addClass("none");
                $(el).removeClass("s_1");
                $(el).addClass("s_2");
                $(".content").addClass("none");
                $(".content2").removeClass("none");
                $(".step").addClass("step1");
                $(".step").addClass("step2");
            }
            else
            {
                $.ajax({
                    type:'get',
                    url:'index.php?act=store&op=adt_check_mobile&mobile='+member_name,
                    dataType:'text',
                    success:function(msg){
                        if(msg==1) {
                            $phone.parent().find(".warn").html(model.content1_t_text[1]);
                            $phone.parent().find(".t_text").addClass("none");
                            $phone.parent().find(".warn").removeClass("none");

                            //清空所有提示
                            $(".t_text").addClass("none");
                            $(el).removeClass("s_1");
                            $(el).addClass("s_2");
                            $(".content").addClass("none");
                            $(".content2").removeClass("none");
                            $(".step").addClass("step1");
                            $(".step").addClass("step2");
                        }
                        if(msg==2){
                            $phone.parent().find(".warn").html(model.content1_t_text[2]);
                            $phone.parent().find(".t_text").addClass("none");
                            $phone.parent().find(".warn").removeClass("none");

//                    $psd.parent().find(".tip").html(model.content1_t_text[4]);
                            $psd.parents("tr").removeClass("none");
                            model.isCheckPsd=true;
                            return;
                        }
                        if(msg==0){
                            $phone.parent().find(".error").html(model.content1_t_text[3]);
                            $phone.parent().find(".t_text").addClass("none");
                            $phone.parent().find(".error").removeClass("none");
                            return false;
                        }
                    }
                });
            }
        }
        else if(this.className.indexOf("s_2")>-1)
        {
            if(!valid($("#store_name"),"input",{
                    null:"商家名称不能为空"
                })) return;
            if(!valid($("#true_name"),"input",{
                    null:"店主姓名不能为空"
                })) return;
            if(!valid($("#contact_phone"),"input",{
                    null:"联系方式不能为空"
                })) return;
            if(!valid($("#licence_number"),"input",{
                    null:"营业执照不能为空"
                })) return;
            if(!valid($("#legal_person"),"input",{
                    null:"法人不能为空"
                })) return;
            if($("#licence_file")[0].files.length===0)
            {
//                $('.upload_file_tip').text('');
                alert("请选择文件");
                return;
            }

//            ajax_form('dailog_id', '确认商户信息', '<?php //echo urlShop('agent_store','add_store_dialog');?>//&store_name='+store_name+'&login_name='+login_name,420,0);
            //清空所有提示
            $(".t_text").addClass("none");
            $(el).removeClass("s_2");
            $(el).addClass("s_3");
            $(".content").addClass("none");
            $(".content3").removeClass("none");
            $(".btn_area").addClass("two_btn");
            $(".step").addClass("step2");
            $(".step").addClass("step3");
        }
        else if(this.className.indexOf("s_3")>-1)
        {
            if(!valid($("#storeName_"),"input",{
                    null:"商家名称不能为空"
                })) return;
            if(!valid($("#bank_name"),"input",{
                    null:"开户银行名不能为空"
                })) return;
            if(!valid($("#bank_no"),"input",{
                    null:"对公账户帐号不能为空",
                    reg:[/^[6]{1}[0-9]{15,18}$/,"对公账户格式错误"]
                })) return;
            var postData=$(".new_form").serialize();
            postData+=("&data[lat]="+$("#lat").html()+"&data[lng]="+$("#lng").html());
            postData+=("&data[province_name]="+$("#province option:selected").html()
                +"&data[city_name]="+$("#city option:selected").html()
                +"&data[area_name]="+$("#district option:selected").html());
            $("input.lng").val($("#lng").html());
            $("input.lat").val($("#lat").html());
            $("#province_name").val($("#province option:selected").html());
            $("#city_name").val($("#city option:selected").html());
            $("#area_name").val($("#district option:selected").html());
//            $.ajax({
//                url:"/manage/index.php?act=store&op=adt_add_store&form_submit=ok",
//                dateType:"json",
//                data:postData,
//                type:'post',
//                success:function(data){
//                    if(data.res===1){
//                        alert(data.reason);
//
//                    }
//                    else{
//                        alert(data.reason);
//                    }
//                }
//            })
            $(".new_form")[0].submit();//提交表单
        }
    });
    $(".prev_btn").on("click",function(e){
        //清空所有提示
        $(".t_text").addClass("none");
        $(this).next().removeClass("s_3");
        $(this).next().addClass("s_2");
        $(".btn_area").removeClass("two_btn");
        $(".content").addClass("none");
        $(".content2").removeClass("none");
    });

    var model2={
        loadTime:function(){
            var optionHourStr='',optionMinuteStr='';
            for(var i= 0;i<24;i++)
            {
                optionHourStr+=("<option value='"+i+"'>"+ i.addZeroNumber()+"</option>");
            }
            for(var i= 0;i<60;i++)
            {
                optionMinuteStr+=("<option value='"+i+"'>"+ i.addZeroNumber()+"</option>");
            }
            $("#b_hour").html(optionHourStr);
            $("#b_minute").html(optionMinuteStr);
            $("#e_hour").html(optionHourStr);
            $("#e_minute").html(optionMinuteStr);
        },
        loadArea:function(type){
            areaAjax(0,0,"<option value='0'>选择省</option>",$("#province"));
            $("#province").on("change",function(){
                var select_id=this.value;
                areaAjax(parseInt(select_id),1,"<option value='0'>选择市/区</option>",$("#city"));
            });
            $("#city").on("change",function(){
                var select_id=this.value;
                areaAjax(parseInt(select_id),2,"<option value='0'>选择市/县</option>",$("#district"));
            })
        }
    };
    $(".get_location").on("click",function(){
        var address=$("#address_detail ").val(),
            city=$("#city option:selected").html();
        //验证
        if(!valid($("#province"),"select",'0',"地址选择请填写完整")) return;
        if(!valid($("#city"),"select",'0',"地址选择请填写完整")) return;
        if(!valid($("#district"),"select",'0',"地址选择请填写完整")) return;
        if(!valid($("#address_detail"),"input",{
            null:"详细地址不能为空"
        })) return;

        $.ajax({
            url:"http://api.map.baidu.com/geocoder/v2/?address="+encodeURIComponent(address)+"&city="+encodeURIComponent(city)+"&output=json&ak=uLhko8NKBiREseUxAWE0hVYc&callback=showLocation",
            dataType:"jsonp", //返回的数据类型,text 或者 json数据，建议为json
            type:"get", //传参方式，get 或post
            data:{//传过去的参数，格式为 变量名：变量值
            },
            error: function(msg){  //若Ajax处理失败后回调函数，msg是返回的错误信息
                $('.notice').html( "处理失败");
            },
            success: function(data) { //若Ajax处理成功后的回调函数，text是返回的页面信息
                if(data.status!=0){
                    alert( "请正确输入详细地址");
                    return;
                }
                $('#lng').html(data.result.location.lng);
                $('#lat').html(data.result.location.lat);
                map.clearOverlays();
                var current_point=new BMap.Point(data.result.location.lng,data.result.location.lat);
                map.centerAndZoom(current_point, 18);
                marker=new BMap.Marker(current_point);
                map.addOverlay(marker); //拖拽停止监听
                marker.addEventListener("dragend", function(obj) {
                    $('#lng').html(obj.point.lng);
                    $('#lat').html(obj.point.lat);
                });
                marker.enableDragging();
            }
        });
    });
    function areaAjax(area_id,type,OpStr,$selectElement){
        var provinceOp=cityOp=districtOp='';
        $.ajax({
            type:'get',
            url:'../mobile/index.php?act=unlimited_invitation&op=area_list',
            data:{area_id:area_id},
            dataType:'json',
            success:function(data){
                if(data.code===200)
                {
                    var dataArr=data.datas.area_list;
//                    if(!type)//加载省
//                    {
//                        provinceOp+=("<option value='0'>选择省</option>");
//                        for(var i= 0,l=dataArr.length;i<l;i++)
//                        {
//                            provinceOp+=("<option value='"+dataArr[i].area_id+"'>"+ dataArr[i].area_name+"</option>");
//                        }
//                        $("#province").html('');
//                        $("#province").append(provinceOp);
//                    }
//                    else if(type===1)
//                    {
//                        cityOp+=("<option value='0'>选择市/区</option>");
//                        for(var i= 0,l=dataArr.length;i<l;i++)
//                        {
//                            cityOp+=("<option value='"+dataArr[i].area_id+"'>"+ dataArr[i].area_name+"</option>");
//                        }
//                        $("#city").html('');
//                        $("#city").append(cityOp);
//                    }
//                    else
//                    {
//                        districtOp+=("<option value='0'>选择市/县</option>");
//                        for(var i= 0,l=dataArr.length;i<l;i++)
//                        {
//                            districtOp+=("<option value='"+dataArr[i].area_id+"'>"+ dataArr[i].area_name+"</option>");
//                        }
//                        $("#district").html('');
//                        $("#district").append(districtOp);
//                    }
                        for(var i= 0,l=dataArr.length;i<l;i++)
                        {
                            OpStr+=("<option value='"+dataArr[i].area_id+"'>"+ dataArr[i].area_name+"</option>");
                        }
                        $selectElement.html('');
                        $selectElement.append(OpStr);
                }
            }
        });
    }
    function valid($el,type,obj,errorText){
        if(type==="input")
        {
            for(var e in obj)
            {
                if(e==='null')
                {
                    $el.parent().children(".t_text").addClass("none");
                    if(!$el.val())
                    {
                        $el.next().html(obj[e]);
                        $el.next().removeClass("none");
                        return false;
                    }
                }
                else if(e==='reg')
                {
                    $el.parent().children(".t_text").addClass("none");
                    if(!obj[e][0].test($el.val()))
                    {
                        $el.next().html(obj[e][1]);
                        $el.next().removeClass("none");
                        return false;
                    }
                }
            }
        }
        else {//select只有空
            $el.parent().children(".t_text").addClass("none");
            if($el.val()===obj)
            {
                $el.parent().children(".error").html(errorText);
                $el.parent().children(".t_text").removeClass("none");
                return false;
            }
        }
        return true;
    }
    function check_file_size(){
        var fileId = "licence_file";
        var dom = document.getElementById(fileId);
        var fileSize =  dom.files[0].size;//文件的大小，单位为字节B
        if(fileSize>=2*1024*1024){
            $('.upload_file_tip').text('这个文件超过2M了，不能上传啦');
        }else{
            $('.upload_file_tip').text('');
        }
//        $('.upload_file').text('the size is : '+fileSize+'B--'+(fileSize/1024)+'KB--'+fileSize/1024/1024+'MB');
    }
    Number.prototype.addZeroNumber=function(){
        var numstr=this.toString();//this指向调用该方法的对象
        if(numstr.length==1)
        {
            numstr='0'+numstr;
        }
        return numstr;
    };

    model2.loadTime();
    model2.loadArea();


</script>
