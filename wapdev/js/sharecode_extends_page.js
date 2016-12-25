$(function () {
    FastClick.attach(document.body);

    var user_id=request("user_id");
    var photo="";

    $.ajax({
        url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_member_info_by_id&client_type=wap&user_id="+user_id,
        type:'get',
        dataType:'jsonp',
        jsonp:'callback',
        success:function(result){
            if(result.code==200){
                $(".avator-img-box img").attr("src",result.data.member_avatar);
                $(".user-text").text(result.data.member_truename);
                $(".invite-text").text("邀请码："+result.data.invitation);

                photo=result.data.member_avatar;

                $(".code-image-main").css("background","url('https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value="+WapSiteUrl+"/tmpl/register.html?user_id="+user_id+",,,invite_code="+result.data.invitation+"') center center no-repeat");
                $(".code-image-main").css("background-size","120% 120%");

                $(".btn-register").click(function(){
                    window.location.href=WapSiteUrl+"/tmpl/register.html?user_id="+user_id+"&invite_code="+result.data.invitation;
                });

                share_wx();

                function share_wx(){
                    var shareUrl=WapSiteUrl+"/sharecode_extends_page.html?user_id="+user_id+"&client_type=wap";
                    var goodsImageUrl=WapSiteUrl+"/images/gigegoulogo.png";
                    if(photo!=""){
                        goodsImageUrl=photo;
                    }

                    var timestamp=new Date().getTime()+"";
                    timestamp=timestamp.substring(0,10);
                    var ranStr=randomString();
                    var nurl=document.URL;

                    function randomString(len) {
                        len = len || 20;
                        var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
                        var maxPos = $chars.length;
                        var pwd = '';
                        for (i = 0; i < len; i++) {
                            pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
                        }
                        return pwd;
                    }

                    $.ajax({
                        url:"http://www.51aigegou.cn/aigegou/ws/webGetTicketSignByUserId?timestamp="+timestamp+"&url="+nurl+"&nonceStr="+ranStr,
                        type:'get',
                        dataType:'jsonp',
                        cache : false,
                        jsonp:"jsonpcallback",
                        success:function(data){
                            wx.config({
                                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                                appId: 'wxa0641282049ed265', // 必填，公众号的唯一标识
                                timestamp:timestamp, // 必填，生成签名的时间戳
                                nonceStr: ranStr, // 必填，生成签名的随机串
                                signature: data.sign.toLowerCase(),// 必填，签名，见附录1
                                jsApiList: [    'checkJsApi',
                                    'onMenuShareTimeline',
                                    'onMenuShareAppMessage',
                                    'onMenuShareQQ',
                                    'onMenuShareQZone'
                                ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                            });
                        },
                        error:function(){
                            console.log("错了");
                        }
                    });
                    wx.ready(function(){

                        //分享给朋友
                        wx.onMenuShareAppMessage({
                            title: '',
                            desc: '简简单单一键分享，轻轻松松坐享其成',
                            link: shareUrl,
                            imgUrl: goodsImageUrl,
                            trigger: function (res) {
                            },
                            success: function (res) {
                            },
                            cancel: function (res) {
                            },
                            fail: function (res) {
                                alert(JSON.stringify(res));
                            }
                        });

                        //分享到朋友圈
                        wx.onMenuShareTimeline({
                            title: '简简单单一键分享，轻轻松松坐享其成',
                            link: shareUrl,
                            imgUrl: goodsImageUrl,
                            trigger: function (res) {
                            },
                            success: function (res) {
                            },
                            cancel: function (res) {
                            },
                            fail: function (res) {
                                alert(JSON.stringify(res));
                            }
                        });

                        //分享到qq
                        wx.onMenuShareQQ({
                            title: '',
                            desc: '简简单单一键分享，轻轻松松坐享其成',
                            link: shareUrl,
                            imgUrl: goodsImageUrl,
                            success: function () {

                            },
                            cancel: function () {

                            }
                        });

                        //分享到QQ空间
                        wx.onMenuShareQZone({
                            title: '',
                            desc: '简简单单一键分享，轻轻松松坐享其成',
                            link: shareUrl,
                            imgUrl: goodsImageUrl,
                            success: function () {

                            },
                            cancel: function () {

                            }
                        });
                    });
                    wx.error(function(res){
                        alert("error");
                    });
                }

            }
        }
    });

});

