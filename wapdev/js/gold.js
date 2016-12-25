$(function(){

    var moneyNum = request('money');
    var inviteCode = request('invite_code');
    var userId = request('user_id');

    $('#goldNum').text(moneyNum);
    var goLink = 'register.html?invite_code=' + inviteCode + '&user_id=' +userId;
    $('#goBtnLink').attr('href',goLink);

    var clientWidth = document.documentElement.clientWidth;

    var tmpl = '';
    for(var i=0;i<100;i++){
        var leftRandom = Math.random()*clientWidth;
        var k = Math.round(Math.floor(Math.random()*10+1)/2);
        var j = Math.round(Math.floor(Math.random()*10+1));
        tmpl+= '<div class="gold-item item-class-'+k+'" style="top:'+ (j)*20 +'px;left:'+ leftRandom +'px"></div>';
    }
    $('body').append(tmpl);


    function getStyle(obj,attr){
        if(obj.currentStyle) {
            return obj.currentStyle[attr];
        }
        else {
            return getComputedStyle(obj,false)[attr];
        }
    }

    function startMove(obj,attr,iTarget){

        clearInterval(obj.timer);
        obj.timer=setInterval(function(){
            // var iCur=parseInt(getStyle(obj,attr));
            var iCur=0;

            if(attr=='opacity')
            {
                iCur=parseInt(parseFloat(getStyle(obj,attr))*100);
            }
            else
            {
                iCur=parseInt(getStyle(obj,attr));
            }

            var iSpeed=(iTarget+iCur)/60;
            iSpeed=iSpeed>0?Math.ceil(iSpeed):Math.floor(iSpeed);

            if(iCur>=iTarget){
                clearInterval(obj.timer);
            }
            else{
                if(attr=='opacity')
                {
                    obj.style.opacity=(iCur+iSpeed)/100;
                    obj.style.filter='alpha(opacity:'+(iCur+iSpeed)+')';
                }
                else
                {
                    obj.style[attr]=iCur+iSpeed+'px';
                }
            }
        },30);
    }

    var clientHeight = document.documentElement.clientHeight-200;


    setInterval(function(){
        for(var i3=0;i3<100;i3++){
            var heightRandom = Math.random()*150;
            startMove(document.getElementsByClassName('gold-item')[i3],'top',clientHeight + heightRandom);
        }
    },200);


    $('.off-btn').on('click',function(){
        $(this).parent().height('0');
    });

    share_wx();

    function share_wx(){

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
            url:"http://www.51aigegou.cn/aigegou/ws/webGetTicketSignCoin?timestamp="+timestamp+"&url="+nurl+"&nonceStr="+ranStr,
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
                desc: '我已经在爱个购中赚取第一桶金，赚钱太easy,还不来！',
                link: 'http://shop.aigegou.com/agg/wap/tmpl/gold.html?money='+moneyNum+'&user_id='+userId+'&invite_code='+inviteCode+'&client_type=wap',
                imgUrl: 'http://shop.aigegou.com/agg/wap/images/gold_man.png',
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
                title: '我已经在爱个购中赚取第一桶金，赚钱太easy,还不来！',
                link: 'http://shop.aigegou.com/agg/wap/tmpl/gold.html?money='+moneyNum+'&user_id='+userId+'&invite_code='+inviteCode+'&client_type=wap',
                imgUrl: 'http://shop.aigegou.com/agg/wap/images/gold_man.png',
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
                desc: '我已经在爱个购中赚取第一桶金，赚钱太easy,还不来！',
                link: 'http://shop.aigegou.com/agg/wap/tmpl/gold.html?money='+moneyNum+'&user_id='+userId+'&invite_code='+inviteCode+'&client_type=wap',
                imgUrl: 'http://shop.aigegou.com/agg/wap/images/gold_man.png',
                success: function () {

                },
                cancel: function () {

                }
            });
            //分享到qq
            wx.onMenuShareQQ({
                title: '',
                desc: '我已经在爱个购中赚取第一桶金，赚钱太easy,还不来！',
                link: 'http://shop.aigegou.com/agg/wap/tmpl/gold.html?money='+moneyNum+'&user_id='+userId+'&invite_code='+inviteCode+'&client_type=wap',
                imgUrl: 'http://shop.aigegou.com/agg/wap/images/gold_man.png',
                success: function () {

                },
                cancel: function () {

                }
            });

            //分享到QQ空间
            wx.onMenuShareQZone({
                title: '',
                desc: '我已经在爱个购中赚取第一桶金，赚钱太easy,还不来！',
                link: 'http://shop.aigegou.com/agg/wap/tmpl/gold.html?money='+moneyNum+'&user_id='+userId+'&invite_code='+inviteCode+'&client_type=wap',
                imgUrl: 'http://shop.aigegou.com/agg/wap/images/gold_man.png',
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


});