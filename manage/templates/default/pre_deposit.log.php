<?php defined('emall') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>预充值管理</h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span>预充值记录</span></a></li>
        <li><a href="<?php echo urlAdmin('pre_deposit', 'setting');?>"><span>设置</span></a></li>
        <li><a href="<?php echo urlAdmin('pre_deposit', 'charge');?>"><span>预充值</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <form method="get" name="formSearch" id="formSearch">
        <input type="hidden" value="pre_deposit" name="act">
        <input type="hidden" value="log" name="op">
        <table class="tb-type1 noborder search">
            <tbody>
                <tr>
                    <th>支付编号:</th>
                    <td><input type="text" name="pre_sn"></td>
                    <th>店铺名称:</th>
                    <td><input type="text" name="store_name" value="<?php echo $output['store_name'];?>"></td>
                    <th>充值时间:</th>
                    <td>
                        <input type="text" class="txt date"  name="begin_time" id="search_stime" class="txt" value="<?php echo $output['query_start_time'];?>">
                        <label for="search_etime">~</label>
                        <input type="text" class="txt date"  name="end_time" id="search_etime" class="txt" value="<?php echo $output['query_end_time'];?>">
                    </td>
                    <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a></td>
                </tr>
            </tbody>
        </table>
    </form>


<table class="table tb-type2" id="prompt">
    <tbody>
    <tr class="space odd">
        <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
    </tr>
    <tr>
        <td>
            <ul>
                <li>1.点击输入框</li>
                <li>2.输入店铺名称</li>
                <li>3.搜索</li>
            </ul></td>
    </tr>
    </tbody>
</table>
 <div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_debt_list"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
<table class="table tb-type2 nobdb">
    <thead>
    <tr class="thead">
        <th>店铺名称</th>
        <th>店铺账号</th>
        <th>所属区域</th>
        <th>充值类型</th>
        <th class="align-center">预充值金额</th>
        <th class="align-center">当前欠款</th>
        <th>充值日期</th>
        <th>操作人</th>
    </tr>
    </thead>
    <tbody>
    <?php if(count($output['list'])>0){?>
        <?php foreach($output['list'] as $order){?>
            <tr class="hover">
                <td><?php echo $order['store_name'];?></td>
                <td><?php echo $order['member_name'];?></td>
                <td><?php echo $order['area_info'];?></td>
                <td><?php echo (1==$order['type'])?'充值':'还款';?></td>
                <td class="align-center"><?php echo $order['sum_amount'];?></td>
                <td class="align-center"><?php echo $order['debt'];?></td>
                <td><?php echo date('Y-m-d H:i:s',$order['create_time']);?></td>
                <td><?php echo $order['create_name'];?></td>
            </tr>
        <?php }?>
    <?php }else{?>
        <tr class="no_data">
            <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
        </tr>
    <?php }?>
    </tbody>
    <tfoot>
    <tr class="tfoot">
        <td colspan="15" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?></div></td>
    </tr>
    </tfoot>
</table>

</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script>

        function charge(id,store_name){
                ajax_form('dialog_id', '预充值', 'index.php?act=pre_deposit&op=dialog&member_id='+id+'&stroe_name='+encodeURIComponent(store_name));
        }

</script>


<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script>
    $(function(){
        $('#search_stime').datepicker({dateFormat: 'yy-mm-dd'});
        $('#search_etime').datepicker({dateFormat: 'yy-mm-dd'});
        $('#ncsubmit').click(function(){
        $('#formSearch').submit();
  });
    });
</script>