<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style type="text/css">
        object, embed {
        -webkit-animation-duration: .001s;
        -webkit-animation-name: playerInserted;
        -ms-animation-duration: .001s;
        -ms-animation-name: playerInserted;
        -o-animation-duration: .001s;
        -o-animation-name: playerInserted;
        animation-duration: .001s;
        animation-name: playerInserted;
    }

    @-webkit-keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }

    @-ms-keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }

    @-o-keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }
        .item-title{
            margin:10px 0;
        }
        .item-title div,.item-title select{
            float:left;
        }
        .item-title div{
            margin-left:15px;
        }
        .item-title select{
            margin:0 10px;
        }
        .fixed-bar{
            padding-bottom: 15px;
        }
        .item-title-ad {
            float:right;
        }
        .item-title{
            float: left;
        }
    </style>
<script type="text/javascript">
    function _adv_oper(status){
        var chk_value =[];
        $('input[name="checkIds"]:checked').each(function(){
            chk_value.push($(this).val());
        });
            if(chk_value.length==0){
                alert('你还没有选择任何内容！');
                return false;
            }else{
                $("#oper_type").val(status);
                $("#advIds").val(chk_value);
                document.clmdForm.action="index.php?act=fix_adv&op=oper_adv";
                document.clmdForm.submit();
            }
    }
     function _edit_oper(key_id){
        $("#key_id").val(key_id);
        document.clmdForm.action="index.php?act=fix_adv&op=preEdit";
        document.clmdForm.submit(); 
     }
</script>
</head>
<body>
<form method="post" id="clmdForm" name="clmdForm">
   <input type="hidden" name="advIds" id="advIds"/>
   <input type="hidden" name="oper_type" id="oper_type"/>
   <input type="hidden" name="key_id" id="key_id"/>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div>广告范围</div>
            <select name="adv_limit_area" id="">
                <option value="0"  <?php if($output['adv_limit_area'] == 0){echo "selected";}?>>不限</option>
                <option value="1" <?php if($output['adv_limit_area'] == 1){echo "selected";}?>>限制</option>
            </select>
            <div>投放城市</div>
            <select name="cityids" >
                <?php
                echo $output['adv_cityids']."<option value='-1'>全部</option>";
                foreach ($output['citylist'] as $var => $value) {
                   echo "<option value=\"$value[area_id]\" ";
                   if($output['cityids'] == $value[area_id]){
                                            echo "selected";
				 	}
                    echo ">$value[area_name]</option>";
                }
                ?>
            </select>
            <div>投放平台</div>
            <select name="adv_channel" >
                <?php
                foreach ($output['channel'] as $var => $value) {
                   echo "<option value=\"$var\" ";
                   if($output['adv_channel'] == $var){
                                            echo "selected";
				 	}
                   echo ">$value</option>";
                }
                ?>
            </select>
            <a class="btn-search " title="查询" href="javascript:document.clmdForm.submit();"></a>
        </div>
        <div class="item-title-ad">
            <a href="index.php?act=fix_adv&op=preAdd" class="btn" ><span>添加新广告</span></a>
        </div>
    </div>
    <div class="fixed-empty"></div>
   
        <input type="hidden" name="form_submit" value="ok">
        <input type="hidden" name="pre" value="ok">
        <table class="table tb-type2 nomargin">
            <thead>
            <tr class="thead">
                <th><input type="checkbox" class="checkall" /></th>
                <th>ID</th>
                <th>排序</th>
                <th class="">广告标题</th>
                <th class="">广告类型</th>
                <th class="">状态</th>
                <th class="">点击数</th>
                <th class="">图片</th>
                <th class="">上线时间</th>
                <th class="">下线时间</th>
                <th class="">添加时间</th>
                <th class="">管理操作</th>
            </tr>
            </thead>
            <tbody>
                <?php foreach($output['fix_adv'] as $k => $v){ ?>   
                    <tr>
                        <td class=""><input type="checkbox" class="" name="checkIds" value="<?php echo $v['id']?>"></td>
                        <td class=""><?php echo $v['id']?></td>
                        <td><font color="blue"><?php echo $v['adv_order']?></font></td>
                        <td><?php echo $v['adv_title']?></td>
                        <td><?php  if($v['adv_type']==1){echo '图片'; }else{echo '其他';} ?></td>
                        <td><?php if($v['adv_status']==1){echo "<font color=\"green\">启用</font>";}else{echo "<font color=\"red\">禁用</font>";}?></td>
                        <td><?php echo $v['adv_click']?></td>
                        <td><img src="<?php echo UPLOAD_SITE_URL."/".ATTACH_ADV."/".$v['adv_pic_path'];?>" style="max-width: 56px; max-height: 56px;"></td>
                        <td><?php echo  date('Y-m-d',$v['adv_start_date'])?></td>
                        <td><?php echo  date('Y-m-d',$v['adv_end_date'])?></td>
                        <td><?php echo  date('Y-m-d H:i:s',$v['adv_add_date'])?></td>
                        <td><a href="javascript:;" onclick="_edit_oper(<?php echo $v['id']?>)">修改</a>|<a href="">统计</a></td>
                    </tr>
                <?php } ?>
            </tbody>

            <tfoot>
            <?php if(!empty($output['fix_adv']) && is_array($output['fix_adv'])){ ?>
                <tr>
                    <td colspan="7">
                        <a href="javascript:;" class="btn" onclick="_adv_oper(1)"><span>启用</span></a>
                        <a href="javascript:;" class="btn" onclick="_adv_oper(2)"><span>停用</span></a>
                        <a href="javascript:; " class="btn" onclick="_adv_oper(3)"><span>删除</span></a>
                    </td>
                    <td>  </td>
                </tr>
             <?php } ?>
            </tfoot>
        </table>
  <div class="pagination"> <?php echo $output['page'];?> </div>
  <br/>
</div>
</form>
</body>
</html>