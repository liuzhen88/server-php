<?php defined('emall') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>预充值管理</h3>
      <ul class="tab-base">
        <li><a href="<?php echo urlAdmin('pre_deposit', 'log');?>"><span>预充值记录</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>设置</span></a></li>
        <li><a href="<?php echo urlAdmin('pre_deposit', 'charge');?>"><span>预充值</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <form method="post">
        <table class="tb-type1 noborder search">
            <tr>
                <th>活动时间</th>
                <td>
                    <input type="text" class="txt date" value="<?php echo ($output['setting']['begin_time'])?date('Y-m-d',$output['setting']['begin_time']):'';?>" name="data[begin_time]" id="search_stime" class="txt">
                    <label for="search_etime">~</label>
                    <input type="text" class="txt date" value="<?php echo ($output['setting']['end_time'])?date('Y-m-d',$output['setting']['end_time']):'';?>" name="data[end_time]" id="search_etime" class="txt">
                </td>
            </tr>
            <tr>
                <th>预充值金额</th>
                <td><input type="text" name="data[amount]" value="<?php echo $output['setting']['amount'] ?>"></td>
            </tr>
            <tr>
                <th>是否启用</th>
                <td>
                    <input type="radio" name="data[type]" value="1" <?php if(isset($output['setting']['type']) && 1==$output['setting']['type']) echo "checked"; ?>>启用
                    <input type="radio" name="data[type]" value="2" <?php if(!isset($output['setting']['type']) || 1!=$output['setting']['type']) echo "checked"; ?>>禁用
                </td>
            </tr>
            <tr><td></td><td><input type="submit" value="保存"/></td> </tr>

        </table>
    </form>


  </div>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script>
    $(function(){
        $('#search_stime').datepicker({dateFormat: 'yy-mm-dd'});
        $('#search_etime').datepicker({dateFormat: 'yy-mm-dd'});
    });
</script>