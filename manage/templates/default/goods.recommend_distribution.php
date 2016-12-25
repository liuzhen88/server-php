<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['goods_recommend_batch_handle'];?></h3>
      <ul class="tab-base">
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="post" name="form1">
    <input type="hidden" value="<?php echo $output['goods_id'];?>" name="goods_id">
    <input type="hidden" value="ok" name="form_submit">
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td class="required"><label>分销推荐:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><ul class="nofloat w830">
              <li class="left w18pre h36">
                 <input type="radio" value="2" name="distribution_recommend" checked="true">
                 <label for="recommend_id_2">推荐</label>
                 <input type="radio" value="1" name="distribution_recommend">
                 <label for="recommend_id_1">不推荐</label>
              </li>
            </ul></td>
        </tr>
      <tfoot>
        <tr class="tfoot">
          <td ><a href="JavaScript:void(0);" class="btn" onclick="document.form1.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
