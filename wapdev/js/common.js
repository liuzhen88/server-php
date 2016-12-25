if (typeof AGG == "undefined") {
    var AGG = {};
}

function GetQueryString(name){
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	var r = window.location.search.substr(1).match(reg);
	if (r!=null) return unescape(r[2]); return null;
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

function checklogin(state){
	if(state == 0){
		location.href = WapSiteUrl+'/tmpl/member/login.html';
		return false;
	}else {
		return true;
	}
}

function contains(arr, str) {
    var i = arr.length;
    while (i--) {
           if (arr[i] === str) {
           return true;
           }
    }
    return false;
}

function buildUrl(type, data) {
    switch (type) {
        case 'keyword':
            return WapSiteUrl + '/tmpl/product_list.html?keyword=' + encodeURIComponent(data);
        case 'special':
            return WapSiteUrl + '/special.html?special_id=' + data;
        case 'goods':
            return WapSiteUrl + '/tmpl/product_detail.html?goods_id=' + data;
        case 'url':
            return data;
		case '':
			return data;
		case 'undefined':
			return data;
    }
    return WapSiteUrl;
}

//获取url参数
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

AGG.isWeixin=function (){
	var ua = navigator.userAgent.toLowerCase();
	if (ua.match(/MicroMessenger/i) != "micromessenger") {

		var html = [
			'<div id="MustWeichat" style="display: none;position:absolute;top:0;left: 0; width: 100%;height:100%; z-index: 1000000;text-align: center;background:#fff;">',
			'<div style="display:inline-block;width:60%;margin-top:25%;text-align: center;"><img src="images/pretty_girl.png" style="width:100%;"></div>',
			'<div style="display:inline-block;width:80%;margin-top:10px;text-align: center;">对不起，此页面无法用浏览器打开，请返回微信页面。<br/>',
			'<br/>您可以添加微信公众号<span style="color:red;">aigegou51</span>进入商城首页</div></div>'].join('');
		$("body").html(html);
		$("#MustWeichat").show();
		return false;
	} else {
		return true;
	}
};

AGG.client={
	type:function(){
		var clientType = request("client_type").toLowerCase()||getcookie("client_type").toLowerCase();
        if (getcookie("client_type") == '') {
            addcookie('client_type', clientType);
        }
		return clientType;
	},
	isApp:function(){
        if(this.type()=="ios" ||this.type()=="android"){
            return true;
        }else{
            return false;
        }
    }
};
AGG.optimize=new optimizeFun();
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
		AGG.font_size=Standard;
		return;
	};
}
AGG.optimize.lazyLoad();
