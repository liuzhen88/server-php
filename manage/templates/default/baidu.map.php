<div id="allmap"></div>

<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
    #allmap {width: 100%;height: 300px;overflow: hidden;margin:0;font-family:"微软雅黑";}
</style>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=uLhko8NKBiREseUxAWE0hVYc"></script>
<?php
$locat_city='苏州';
$locat_lat=floatval($_GET['lat']);
$locat_lng=floatval($_GET['lng']);
if(0==$locat_lat) $locat_lat=120.559147;
if(0==$locat_lng) $locat_lng=31.292095;var_dump($locat_lat,$locat_lng);
//?>
<script type="text/javascript">

    // 百度地图API功能
    var point=new BMap.Point(31.292095,120.559147);    //目标点<?php //echo $locat_lng.','.$locat_lat ?>

    var map = new BMap.Map("allmap");                                      // 创建Map实例
    map.centerAndZoom(point, 18);                                            // 初始化地图,设置中心点坐标和地图级别
    map.setCurrentCity("<?php echo $locat_city ?>");                       // 设置地图显示的城市 此项是必须设置的
    map.enableScrollWheelZoom(true);                                         //开启鼠标滚轮缩放

    //创建标记
    var marker=new BMap.Marker(point);
    map.addOverlay(marker);

    var opts = {
        position : point,                                                    // 指定文本标注所在的地理位置
        offset   : new BMap.Size(30, -30)                                    //设置文本偏移量
    }



</script>

