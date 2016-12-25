(function () {

    if(request("client_type")!="android") {

        var input = document.getElementById('send_img');

        // 选择图片
        input.onchange = function (event) {

            var img = event.target.files[0];

            // 判断是否图片
            if (!img) {
                return;
            }

            // 判断图片格式
            //if(!( /\.(?:jpg|png|gif|jpeg)$/.test(img.name)) ){
            //alert('图片只能是jpg,gif,png,jpeg');
            //return ;
            //}

            if ($('.y-pin').find(".y-firstmargin").length >= 3) {
                //$("#send_img").css("display","none");
                $("#send_imgBox").css("display", "none");
            } else {
                //$("#send_img").css("display","block");
                $("#send_imgBox").css("display", "block");
            }

            var reader = new FileReader();
            reader.readAsDataURL(img);

            reader.onload = function (e) { // reader onload start

                // 判断图片格式
                if ((this.result).split("/")[0] != "data:image") {
                    if ((this.result).split(";")[0] != "data:") {
                        //alert((this.result).split("/")[0]);

                        alert("图片格式不对，请重新上传！");

                        return;
                    } else {

                        alert("尚不支持该机型图片上传，敬请谅解！");
                        return;
                    }
                } else {
                    $('.y-pin').append("<li class='y-firstmargin'><img src='" + this.result + "'/><span class='del'></span><span class='imgToBase64'>" + this.result + "</span></li>");
                    $(".y-pin").find("li").eq(0).removeClass("y-firstmargin");
                }

                $(".del").click(function () {

                    $(this).parents(".y-firstmargin").remove();

                    //$("#send_img").css("display","block");
                    $("#send_imgBox").css("display", "block");

                });


            } // reader onload end
        }
    }


    //var inpp=document.getElementById("li_file");
    /*input.onchange = function() {
        // 也可以传入图片路径：lrz('../demo.jpg', ..

        lrz(this.files[0], {
			width:300,
			height:300,
            before: function() {
                console.log('压缩开始');
            },
            fail: function(err) {
                console.error(err);
            },
            always: function() {
                console.log('压缩结束');
            },
            done: function (results) {

            // 你需要的数据都在这里，可以以字符串的形式传送base64给服务端转存为图片。
            console.log(results.base64);

			//实际上传的是results.base64.split(",")[1];

           setTimeout(function () {
                demo_report('', results.base64, results.base64.length * 0.8);

                // 发送到后端
                var xhr = new XMLHttpRequest();
                var data = {
                    base64: results.base64,
                    size: results.base64.length // 校验用，防止未完整接收
                };

            }, 100);
            }
        });
    };


     function demo_report(title, src, size) {
        var img = new Image(),
            li = document.createElement('li'),
            size = (size / 1024).toFixed(2) + 'KB';

        if(size === 'NaNKB') size = '';

        img.onload = function () {

            li.appendChild(img);
            document.querySelector('.y-pin').appendChild(li);
			$(".y-pin").find("li").addClass("y-firstmargin");
			$(".y-pin").find("li").eq(0).removeClass("y-firstmargin");
            $(".y-firstmargin").append("<span class='del'></span><span class='imgToBase64'>"+src+"</span>");

			$(".del").click(function(){
				putImgNum--;

				$(this).parents(".y-firstmargin").remove();

				if(putImgNum==3){
					$("#send_img").css("display","block");
					$("#send_imgBox").css("display","block");
				}

			});

        };

        img.src = typeof src === 'string' ? src : URL.createObjectURL(src);

    } */

})();