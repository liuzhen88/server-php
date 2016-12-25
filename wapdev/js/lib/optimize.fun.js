//基于zepto
(function(w,d){
    function optimizeFun(){
        this.lazyLoad=function(imgarr,specialObj){
            var optimize=this,height=document.documentElement.clientHeight;
            bottom=document.body.scrollTop+height;
            loadImg();
            $("body").on("touchmove",movefun);
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
                        //imgarr[i].setAttribute("haveLoaded",'1');
                        imgarr.splice(i,1);
                        i--;
                    }
                }
                if(imgarr.length==0)
                {
                    $("body").off("touchmove",movefun);
                }
            }
        };
        this.autoChange()
    }
    w.optimize=new optimizeFun();
})(window,document);