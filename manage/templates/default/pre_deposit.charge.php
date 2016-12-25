<?php defined('emall') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>预充值管理</h3>
      <ul class="tab-base">
        <li><a href="<?php echo urlAdmin('pre_deposit', 'log');?>"><span>预充值记录</span></a></li>
        <li><a href="<?php echo urlAdmin('pre_deposit', 'setting');?>"><span>设置</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>预充值</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <form method="post">
        <table class="tb-type1 noborder search">
            <tbody>
                <tr>
                    <th>店铺名称:</th>
                    <td><input type="text" name="store_name"></td>
                    <th>卖家账号:</th>
                    <td><input type="text" name="seller_name"></td>
                    <td><input type="submit" value="搜索" /></td>
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
                <li>1.找到商家</li>
                <li>2.点击预充值</li>
                <li>3.输入金额，点击确定</li>
            </ul></td>
    </tr>
    </tbody>
</table>
<table class="table tb-type2 nobdb">
    <thead>
    <tr class="thead">
        <th>店铺名称</th>
        <th>卖家账号</th>
        <th>联系电话</th>
        <th class="align-center">预存款</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(count($output['list'])>0){?>
        <?php foreach($output['list'] as $order){?>
            <tr class="hover">
                <td><?php echo $order['store_name'];?></td>
                <td><?php echo $order['seller_name'];?></td>
                <td><?php echo $order['store_phone'];?></td>
                <td class="align-center"><?php echo $order['available_predeposit'];?></td>
                <td>
                    <span onclick="charge(<?php echo $order['member_id'];?>,'<?php echo $order['store_name'];?>')">预充值 </span>
                </td>
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