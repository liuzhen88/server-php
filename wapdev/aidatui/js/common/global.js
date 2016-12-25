if (typeof app == "undefined") {
    var app = {};
}

function addcookie(name,value,expireHours){
    var cookieString=name+"="+escape(value)+"; path=/";
    //判断是否设置过期时间
    if(expireHours>0){
        var date=new Date();
        date.setTime(date.getTime+expireHours*3600*1000);
        cookieString=cookieString+"; expire="+date.toGMTString();
    }
    document.cookie=cookieString;
}

function getcookie(name){
    var strcookie=document.cookie;
    var arrcookie=strcookie.split("; ");
    for(var i=0;i<arrcookie.length;i++){
        var arr=arrcookie[i].split("=");
        if(arr[0]==name)return arr[1];
    }
    return "";
}

function delCookie(name){//删除cookie
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getcookie(name);
    if(cval!=null) document.cookie= name + "="+cval+"; path=/;expires="+exp.toGMTString();
}

function request(paras) {
    var url = location.href;
    url = decodeURI(url);
    var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
    var paraObj = {};
    for (var i = 0; j = paraString[i]; i++) {
        paraObj[j.substring(0, j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=") + 1, j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if (typeof(returnValue) == "undefined") {
        return "";
    } else {
        return returnValue;
    }
}

app.checkLogin = function(callback){
    if (getcookie('key') == '') {
        window.location.href = WapSiteUrl + "/aidatui/login1.html";
        return false;
    }else{
        if (typeof callback == "function") {
            callback();
        }
    }
};
app.optimize=new optimizeFun();
var that=this;
function optimizeFun(){
    this.lazyLoad=function(imgarr,specialObj){
        (function (root, factory) {
            if (typeof define === 'function' && define.amd) {
                define(function() {
                    return factory(root);
                });
            } else if (typeof exports === 'object') {
                module.exports = factory;
            } else {
                root.echo = factory(root);
            }
        })(that, function (root) {

            'use strict';

            var echo = {};

            var callback = function () {};

            var offset, poll, delay, useDebounce, unload;

            var isHidden = function (element) {
                return (element.offsetParent === null);
            };

            var inView = function (element, view) {
                if (isHidden(element)) {
                    return false;
                }

                var box = element.getBoundingClientRect();
                return (box.right >= view.l && box.bottom >= view.t && box.left <= view.r && box.top <= view.b);
            };

            var debounceOrThrottle = function () {
                if(!useDebounce && !!poll) {
                    return;
                }
                clearTimeout(poll);
                poll = setTimeout(function(){
                    echo.render();
                    poll = null;
                }, delay);
            };

            echo.init = function (opts) {
                opts = opts || {};
                var offsetAll = opts.offset || 0;
                var offsetVertical = opts.offsetVertical || offsetAll;
                var offsetHorizontal = opts.offsetHorizontal || offsetAll;
                var optionToInt = function (opt, fallback) {
                    return parseInt(opt || fallback, 10);
                };
                offset = {
                    t: optionToInt(opts.offsetTop, offsetVertical),
                    b: optionToInt(opts.offsetBottom, offsetVertical),
                    l: optionToInt(opts.offsetLeft, offsetHorizontal),
                    r: optionToInt(opts.offsetRight, offsetHorizontal)
                };
                delay = optionToInt(opts.throttle, 250);
                useDebounce = opts.debounce !== false;
                unload = !!opts.unload;
                callback = opts.callback || callback;
                echo.render();
                if (document.addEventListener) {
                    root.addEventListener('scroll', debounceOrThrottle, false);
                    root.addEventListener('load', debounceOrThrottle, false);
                } else {
                    root.attachEvent('onscroll', debounceOrThrottle);
                    root.attachEvent('onload', debounceOrThrottle);
                }
            };

            echo.render = function () {
                var nodes = document.querySelectorAll('img[data-echo], [data-echo-background]');
                var length = nodes.length;
                var src, elem;
                var view = {
                    l: 0 - offset.l,
                    t: 0 - offset.t,
                    b: (root.innerHeight || document.documentElement.clientHeight) + offset.b,
                    r: (root.innerWidth || document.documentElement.clientWidth) + offset.r
                };
                for (var i = 0; i < length; i++) {
                    elem = nodes[i];
                    if (inView(elem, view)) {

                        if (unload) {
                            elem.setAttribute('data-echo-placeholder', elem.src);
                        }

                        if (elem.getAttribute('data-echo-background') !== null) {
                            elem.style.backgroundImage = "url(" + elem.getAttribute('data-echo-background') + ")";
                        }
                        else {
                            elem.src = elem.getAttribute('data-echo');
                        }

                        if (!unload) {
                            elem.removeAttribute('data-echo');
                            elem.removeAttribute('data-echo-background');
                        }

                        callback(elem, 'load');
                    }
                    else if (unload && !!(src = elem.getAttribute('data-echo-placeholder'))) {

                        if (elem.getAttribute('data-echo-background') !== null) {
                            elem.style.backgroundImage = "url(" + src + ")";
                        }
                        else {
                            elem.src = src;
                        }

                        elem.removeAttribute('data-echo-placeholder');
                        callback(elem, 'unload');
                    }
                }
                if (!length) {
                    echo.detach();
                }
            };

            echo.detach = function () {
                if (document.removeEventListener) {
                    root.removeEventListener('scroll', debounceOrThrottle,false);
                } else {
                    root.detachEvent('onscroll', debounceOrThrottle);
                }
                clearTimeout(poll);
            };

            return echo;

        });

    };
    this.lazyLoadSelf=function(imgarr){
        var mobile=this,height=document.documentElement.clientHeight;
        bottom=document.body.scrollTop+height;
        loadImg();
        document.querySelector("body").addEventListener("touchmove",movefun,false);
        function movefun(){
            bottom=document.body.scrollTop+height;
            loadImg();
        }
        function loadImg()
        {
            for(var i=0;i<imgarr.length;i++)
            {
                if($(imgarr[i]).offset().top<=bottom)
                {
                    imgarr[i].src=imgarr[i].getAttribute("data-src");
                    imgarr[i].setAttribute("haveLoaded",'1');
                    imgarr.splice(i,1);
                    i--;
                }
            }
            if(imgarr.length==0)
            {
                document.querySelector("body").removeEventListener("touchmove",movefun,false);
            }
        }
    };
    this.autoChange=function(maxWidth,originSize){
        var width=document.documentElement.clientWidth;
        var Standard=originSize/(maxWidth*1.0/width);
        Standard=Standard>100?100:Standard;
        document.querySelector("html").style.fontSize=Standard+"px";
        app.font_size=Standard;
        return;
    };
}
app.optimize.lazyLoad();