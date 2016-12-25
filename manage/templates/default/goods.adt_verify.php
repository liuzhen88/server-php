<?php defined('emall') or exit('Access Invalid!');?>
<link href="<?php echo ADMIN_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['goods_index_goods'];?></h3>
      <ul class="tab-base">
        <li><a href="<?php echo urlAdmin('goods', 'goods');?>" ><span><?php echo $lang['goods_index_all_goods'];?></span></a></li>
        <li><a href="<?php echo urlAdmin('goods', 'goods', array('type' => 'lockup'));?>"><span><?php echo $lang['goods_index_lock_goods'];?></span></a></li>
        <li><a href="<?php echo urlAdmin('goods', 'goods', array('type' => 'waitverify'));?>"><span>等待审核</span></a></li>
        <li><a href="<?php echo urlAdmin('goods', 'goods_set');?>"><span><?php echo $lang['nc_goods_set'];?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>配送商品申请</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="goods" />
    <input type="hidden" name="op" value="adt_goods" />
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <td>
              <select name="province" id="provice" onchange="get_sub_area('city','provice')">
                  <option value="0" >全国</option>
                  <?php foreach($output['province'] as $key=>$value): ?>
                      <option value="<?php echo $key ?>"><?php echo $value; ?></option>
                  <?php endforeach; ?>
              </select>
              <select name="city" id="city"  onchange="get_sub_area('area','city')">
                  <option  value="0" >请选择</option>
              </select>
              <select name="area" id="area" >
                  <option value="0"  >请选择</option>
              </select>
          </td>
          <td>类型</td>
          <td>
              <select name="verify_type">
                  <option value="0" >全部</option>
                  <option value="1" >新增</option>
                  <option value="2" >改价</option>
              </select>
          </td>
          <td> <label for="search_goods_name"><?php echo $lang['goods_index_name'];?></label></td>
          <td><input type="text" value="<?php echo $output['search']['search_goods_name'];?>" name="search_goods_name" id="search_goods_name" class="txt"></td>
          <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
        </tr>
      </tbody>
    </table>
  </form>
  <form method='post' id="form_goods" action="<?php echo urlAdmin('goods', 'goods_del');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <thead>
        <tr class="space">
          <th colspan="15"><?php echo $lang['nc_list'];?></th>
        </tr>
        <tr class="thead">
          <th class="w24"></th>
          <th class="w24"></th>
          <th class="w60">平台货号</th>
          <th class="w120">店铺</th>
          <th colspan="2" class="w96"><?php echo $lang['goods_index_name'];?></th>
            <th class="w72 align-center">城市</th>
            <th class="w72 align-center">区县</th>
            <th class="w72 align-center">申请类目</th>
          <th>规格</th>
          <th class="w72 align-center">价格（元）</th>
          <th class="w72 align-center">返点</th>
          <th class="w96 align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['goods_list']) && is_array($output['goods_list'])){ ?>
        <?php foreach($output['goods_list'] as $k => $v){  ?>
        <tr class="hover edit">
          <td class="w24"><input type="checkbox" name="id[]" value="<?php echo $v['goods_commonid'];?>" class="checkitem"></td>
          <td><i class="icon-plus-sign" nctype="ajaxGoodsList" data-comminid="<?php echo $v['goods_commonid'];?>" style="cursor: pointer;"></i></td>
          <td><?php echo $v['goods_commonid'];?></td>
          <td><?php echo $v['store_name'];?></td>
          <td class="w60"><div class="goods-picture"><span class="thumb size-goods"><i></i><img src="<?php echo thumb($v, 60);?>" onload="javascript:DrawImage(this,56,56);"/></span></div></td>
          <td><dl class="goods-info">
              <dt class="goods-name"><?php echo $v['goods_name'];?></dt>
            </dl></td>
            <td class="align-center"><?php echo $v['city_name']?></td>
            <td class="align-center"><?php echo $v['district_name']?></td>
            <td class="align-center"><?php echo isset($output['class'][$v['gc_id']])?$output['class'][$v['gc_id']]['gc_name']:''; ?></td>
          <td class>
            <p class="goods-brand"><?php echo $v['goods_size'];?></p></td>
          <td class="align-center">
              <?php
                    if($v['league_goods_verify']==10){
                        echo $lang['currency'].$v['league_goods_price'] ;
                    }else{
                        echo '<s>'.$lang['currency'].$v['league_goods_price'].'</s><br/>'.$lang['currency'].$v['league_goods_change_price'];
                    }
              ?>
          </td>
          <td class="align-center"><?php echo $v['commis_rate']?>%</td>
          <td class="w48 align-center"><a href="javascript:void(0);" onclick="goods_verify_pass(<?php echo $v['leage_id'];?>);">通过</a>&nbsp;|&nbsp;<a href="javascript:void(0);" onclick="goods_verify_reject(<?php echo $v['leage_id'];?>);">拒绝</a></td>
        </tr>
        <tr style="display:none;">
          <td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td>
        </tr>
        <?php } ?>
        <?php } else { ?>
        <tr class="no_data">
          <td colspan="13"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <!--<td><input type="checkbox" class="checkall" id="checkallBottom"></td>-->
          <td colspan="16"><!--<label for="checkallBottom"><?php /*echo $lang['nc_select_all']; */?></label>
            &nbsp;&nbsp;<a href="javascript:void(0);" class="btn" nctype="verify_batch"><span>审核</span></a>-->
            <div class="pagination"> <?php echo $output['page'];?> </div></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript">
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
$(function(){

    $('#ncsubmit').click(function(){
        $('#formSearch').submit();
    });

    // 审核批量处理
    $('a[nctype="verify_batch"]').click(function(){
        str = getId();
        if (str) {
            goods_verify(str);
        }
    });

});

// 获得选中哎
function getId() {
    var str = '';
    $('#form_goods').find('input[name="id[]"]:checked').each(function(){
        id = parseInt($(this).val());
        if (!isNaN(id)) {
            str += id + ',';
        }
    });
    if (str == '') {
        return false;
    }
    str = str.substr(0, (str.length - 1));
    return str;
}

//区域选择框
function get_sub_area(child_id,self_id){
    var parent_id=$('#'+self_id+' option:selected').val();
    $("#address").val('');
    if('provice'==self_id){
        $('#area').empty();
        $('#area').append('<option>请选择</option>');
    }
    $('#'+child_id).empty();
    if('请选择'==parent_id){
        $('#'+child_id).append('<option>请选择</option>');
        return;
    }
    $.ajax({
        url:'<?php echo SHOP_SITE_URL;?>/index.php?act=store_joinin_o2o&op=get_sub_area&id='+parent_id,
        dataType:"text",
        success: function(data) {
            $('#'+child_id).append(data);
        }
    });
}

// 商品通过
function goods_verify_pass(ids) {
    _uri = "<?php echo ADMIN_SITE_URL;?>/index.php?act=goods&op=adt_goods_verify_pass&id=" + ids;
    CUR_DIALOG = ajax_form('goods_verify', '提示', _uri, 350);
}
// 商品拒绝
function goods_verify_reject(ids) {
    _uri = "<?php echo ADMIN_SITE_URL;?>/index.php?act=goods&op=adt_goods_verify_reject&id=" + ids;
    CUR_DIALOG = ajax_form('goods_verify', '提示', _uri, 350);
}
</script>
