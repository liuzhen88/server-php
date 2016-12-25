$(function () {
    //FastClick.attach(document.body);
    var thisGoodID=request("goods_id");
    var thisStoreID=request("store_id");
    var curpage=1;
    var more=true;

    if(thisGoodID!=""){//是商品
        $.ajax({
            url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_evaluate_goods_list_v2&geval_goodsid="+thisGoodID+"&client_type=wap&curpage="+curpage,
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    result.curpage=curpage;

                    var evaluateDoTmpl = doT.template($("#evaluatetmpl").html());
                    $("#goodsEvaluate").show().html(evaluateDoTmpl(result));

                    $('.sheader h2').html('评价('+result.data.goods_info.evaluation_count+')');

                    var evaluateListDoTmpl = doT.template($("#evaluateListtmpl").html());
                    $("#evaluateList").show().html(evaluateListDoTmpl(result));

                    if(result.data.evaluate_list.length==8){
                        more = true;
                    }else if(result.data.evaluate_list.length< 8){
                        more = false;
                    }
                    $('.previevList'+curpage).MobilePhotoPreview({
                        trigger: '.preview',
                        show: function(c) {}
                    });
                }
            }
        });

    }else{//是店铺
        $.ajax({
            url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_evaluate_store_list&store_id="+thisStoreID+"&client_type=wap&curpage="+curpage,
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    result.curpage=curpage;
                    var evaluateDoTmpl = doT.template($("#evaluatetmpl2").html());
                    $("#goodsEvaluate").show().html(evaluateDoTmpl(result));
                    $('.sheader h2').html('评价('+result.data.evaluate_info.evaluation_count+')');

                    var evaluateListDoTmpl = doT.template($("#evaluateListtmpl2").html());
                    $("#evaluateList").show().html(evaluateListDoTmpl(result));

                    if(result.data.evaluate_list.length==8){
                        more = true;
                    }else if(result.data.evaluate_list.length< 8){
                        more = false;
                    }
                    $('.previevList'+curpage).MobilePhotoPreview({
                        trigger: '.preview',
                        show: function(c) {}
                    });
                }
            }
        });
    }

    $(window).scroll(function () {
        var doc_h = $(document).height();
        var win_h = $(window).height();
        var scroll_top = $(window).scrollTop();
        if (scroll_top >= doc_h - win_h) {
            if(more==true){
                curpage++;
                ajaxData(curpage);
            }
        }
    });

    function ajaxData(curpage){
        if(thisGoodID!=""){//是商品
            $.ajax({
                url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_evaluate_goods_list_v2&geval_goodsid="+thisGoodID+"&client_type=wap&curpage="+curpage,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(result){
                    if(result.code==200){
                        result.curpage=curpage;
                        var evaluateListDoTmpl = doT.template($("#evaluateListtmpl").html());
                        $("#evaluateList").append(evaluateListDoTmpl(result));
                        if(result.data.evaluate_list.length==8){
                            more = true;
                        }else if(result.data.evaluate_list.length< 8){
                            more = false;
                        }
                        $('.previevList'+curpage).MobilePhotoPreview({
                            trigger: '.preview',
                            show: function(c) {}
                        });
                    }
                }
            });
        }else{//是店铺
            $.ajax({
                url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_evaluate_store_list&store_id="+thisStoreID+"&client_type=wap&curpage="+curpage,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(result){
                    if(result.code==200){
                        result.curpage=curpage;
                        var evaluateListDoTmpl = doT.template($("#evaluateListtmpl2").html());
                        $("#evaluateList").append(evaluateListDoTmpl(result));
                        if(result.data.evaluate_list.length==8){
                            more = true;
                        }else if(result.data.evaluate_list.length< 8){
                            more = false;
                        }
                        $('.previevList'+curpage).MobilePhotoPreview({
                            trigger: '.preview',
                            show: function(c) {}
                        });
                    }
                }
            });
        }

    }

});

function evaluateNum(num){
    var evaN;
    evaN=num*20;
    return evaN;

}
