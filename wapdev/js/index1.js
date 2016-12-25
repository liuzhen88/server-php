(function () {
    var input = document.getElementById('send_img');
    //var inpp=document.getElementById("li_file");
    input.onchange = function() {
        // 也可以传入图片路径：lrz('../demo.jpg', ...
        lrz(this.files[0], {
			width:100,
			height:100,
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
			//alert(results.base64);
			//alert(results.base64);
            $.ajax({
            	url:"http://120.25.240.53/agg/mobile/index.php?act=index&op=eimage_upload&client_type=wap",
            	type:"post",
            	data:{"name":results.base64,"goods_id":1402},
            	dataType:"jsonp",
				jsonp:"callback",
            	success : function(data, status) { 
					 if(data){
						/*$("#img").attr("src",data);
						$("#toux").val(data);*/
						alert(data);
					 }
						else{
							 alert("上传失败！");
						 }
				},
				error:function(){
					alert("预览失败！");
				}
            	
            });
           
            // 以下为演示用内容
            /*var tip = document.querySelector('#tip'),
                report = document.querySelector('#report'),
                footer = document.querySelector('footer');

            report.innerHTML = footer.innerHTML =  '';
            tip.innerHTML = '<p>正在生成和上传..</p> <small class="text-muted">演示未优化移动端内存占用，可能会造成几秒内卡顿或闪退，不代表真实表现，请亲测。</small>';
            demo_report('原始图片', results.origin, results.origin.size);

            setTimeout(function () {
                demo_report('客户端预压的图片', results.base64, results.base64.length * 0.8);

                // 发送到后端
                var xhr = new XMLHttpRequest();
                var data = {
                    base64: results.base64,
                    size: results.base64.length // 校验用，防止未完整接收
                };

            }, 100);*/
            }
        });
    };
    
    /*inpp.onchange = function() {
        // 也可以传入图片路径：lrz('../demo.jpg', ...
        lrz(this.files[0], {
			width:480,
			height:480,
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
			//alert(results.base64);
			//alert(results.base64);
            $.ajax({
            	url:"../../ws/picuploadBase64",
            	type:"post",
            	data:{"images":results.base64.split(",")[1]},
            	dataType:"text",
            	success : function(data, status) {
					 if(data){
						 var subli="<li class='li'><img src='"+data+"' width='100%' height='100%'/><div class='close_li'><img src='images/colse_li.png' width='100%' height='100%'/></div></li> <input type='hidden' name='photo_life[]'  value='"+data.fileName+"'/>";
							if($(".li").length<6){
								$(".post_box_ul").append(subli);
								$(".close_li").click(function(){
									$(this).parent().remove();
								});
							}else{
								alert("亲，最多上传6个啊");
							}
					 }
					 else{
						 alert("上传失败！");
					 }
					 
				},
				error:function(){
					alert("预览失败！");
				}
            	
            });
           
            // 以下为演示用内容
            var tip = document.querySelector('#tip'),
                report = document.querySelector('#report'),
                footer = document.querySelector('footer');

            report.innerHTML = footer.innerHTML =  '';
            tip.innerHTML = '<p>正在生成和上传..</p> <small class="text-muted">演示未优化移动端内存占用，可能会造成几秒内卡顿或闪退，不代表真实表现，请亲测。</small>';
            demo_report('原始图片', results.origin, results.origin.size);

            setTimeout(function () {
                demo_report('客户端预压的图片', results.base64, results.base64.length * 0.8);

                // 发送到后端
                var xhr = new XMLHttpRequest();
                var data = {
                    base64: results.base64,
                    size: results.base64.length // 校验用，防止未完整接收
                };

            }, 100);
            }
        });
    };*/

    /**
     * 演示报告
     * @param title
     * @param src
     * @param size
     */
    function demo_report(title, src, size) {
        var img = new Image(),
            li = document.createElement('li'),
            size = (size / 1024).toFixed(2) + 'KB';

        if(size === 'NaNKB') size = '';

        img.onload = function () {
            var content = '<ul>' +
                '<li>' + title + '（' + img.width + ' X ' + img.height + '）</li>' +
                '<li class="text-cyan">' + size + '</li>' +
                '</ul>';

            li.className = 'item';
            li.innerHTML = content;
            li.appendChild(img);
            document.querySelector('#report').appendChild(li);
        };

        img.src = typeof src === 'string' ? src : URL.createObjectURL(src);
		
    }

 
})();
