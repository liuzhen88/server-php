window.onload=function(){
    var order_sn=request("order_sn");
    var pay_sn=request("pay_sn");
    var return_order=document.getElementById("return-order");
    return_order.onclick=function(){
        window.location.href="local_refund_success.html?order_sn="+order_sn+"&pay_sn="+pay_sn;
    }
}