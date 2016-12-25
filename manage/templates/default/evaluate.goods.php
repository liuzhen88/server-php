<?php defined('emall') or exit('Access Invalid!');?>
<link href="<?php echo ADMIN_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_goods_evaluate']; ?></h3>
      <ul class="tab-base">
          <li><a href="index.php?act=evaluate&op=evalstore_list" ><span><?php echo $lang['admin_evaluate_list'];?></span></a></li>
          <li><a href="index.php?act=evaluate&op=evalstore_list" ><span><?php echo $lang['admin_evalstore_list'];?></span></a></li>
          <li><a href="JavaScript:void(0);" class="current"><span>添加假评价</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="evaluate">
    <input type="hidden" name="op" value="goods">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="search_goodsid">平台货号</label></th>
          <td><input type="text" value="<?php echo $output['search']['search_goodsid']?>" name="search_goodsid" id="search_goodsid" class="txt" /></td>
          <th><label for="search_store_name"><?php echo $lang['goods_index_store_name'];?></label></th>
          <td><input type="text" value="<?php echo $output['search']['search_store_name'];?>" name="search_store_name" id="search_store_name" class="txt"></td>
          <th><label for="search_store_name">店铺邀请码</label></th>
          <td><input type="text" value="<?php echo $output['search']['search_store_invitation'];?>" name="search_store_invitation" id="search_store_invitation" class="txt"></td>
          <th><label for="search_goods_name"> <?php echo $lang['goods_index_name'];?></label></th>
          <td><input type="text" value="<?php echo $output['search']['search_goods_name'];?>" name="search_goods_name" id="search_goods_name" class="txt"></td>
          <th><label>店铺类型</label></th>
          <td ><select name="good_type" >
                  <option value=""><?php echo $lang['nc_please_choose']; ?></option>
                  <?php if(!empty($output['good_type']) && is_array($output['good_type'])){ ?>
                  <?php foreach($output['good_type'] as $k => $v){ ?>
                      <option value="<?php echo $k;?>" <?php if($_GET['good_type'] == $k){?>selected<?php }?>><?php echo $v;?></option>
                  <?php }} ?>

              </select></td>


           <td ><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
          <td class="w120">&nbsp;</td>
        </tr>
      </tbody>
    </table>
  </form>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li></li>
            <li></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w24"></th>
<!--          <th class="w24"></th>-->
          <th class="w60 align-center">平台货号</th>
          <th colspan="2"><?php echo $lang['goods_index_name'];?></th>
		  <th class="w72 align-center">店铺名称</th>
		  <th class="w72 align-center">店铺类型</th>
          <th class="w72 align-center">商品状态</th>
          <th class="w72 align-center">审核状态</th>
          <th class="w108 align-center"><?php echo $lang['nc_handle'];?> </th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($output['goods_list']) && is_array($output['goods_list'])) { ?>
        <?php foreach ($output['goods_list'] as $k => $v) {?>
        <tr class="hover edit">
          <td></td>
<!--          <td><i class="icon-plus-sign" style="cursor: pointer;" nctype="ajaxGoodsList" data-comminid="--><?php //echo $v['goods_commonid'];?><!--" title="点击展开查看此商品全部规格；规格值过多时请横向拖动区域内的滚动条进行浏览。"></i></td>-->
          <td class="align-center"><?php echo $v['goods_id'];?></td>
          <td class="w60 picture"><div class="size-56x56"><span class="thumb size-56x56"><i></i><img src="<?php echo thumb($v, 60);?>" onload="javascript:DrawImage(this,56,56);"/></span></div></td>
          <td>
          <dl class="goods-info"><dt class="goods-name"><?php echo $v['goods_name'];?></dt>
          <dd class="goods-type">
              <?php if ($v['is_distribution'] ==1 && $v['distribution_recommend'] == 1) {?><span class="presell" title="店铺设置为分销且系统设置推荐的商品">分销已推荐</span><?php }?>
              <?php if ($v['is_distribution'] ==1 && $v['distribution_recommend'] == 0) {?><span class="presell" title="店铺设置为分销的商品">分销</span><?php }?>
              <?php if ($v['is_virtual'] ==1) {?><span class="virtual" title="虚拟兑换商品">虚拟</span><?php }?>
              <?php if ($v['is_fcode'] ==1) {?><span class="fcode" title="F码优先购买商品">F码</span><?php }?>
              <?php if ($v['is_presell'] ==1) {?><span class="presell" title="预先发售商品">预售</span><?php }?>
              <?php if ($v['is_appoint'] ==1) {?><span class="appoint" title="预约销售提示商品">预约</span><?php }?>
            </dd>
            <dd class="goods-store"><?php echo $output['ownShopIds'][$v['store_id']] ? '平台' : '三方'; ?>店铺：<?php echo $v['store_name'];?></dd></dl>
            </td>

            <td class="align-center"><?php echo $v['store_name'];?></td>
			<td class="align-center"><?php echo isset($output['good_type'][$v['good_type']])?$output['good_type'][$v['good_type']]:'未定义类型';?></td>
          <td class="align-center"><?php echo $output['state'][$v['goods_state']];?></td>
          <td class="align-center"><?php echo $output['verify'][$v['goods_verify']];?></td>
          <td class="align-center"><a href="javascript:void(0);" onclick="goods_evaluate(<?php echo $v['goods_id'];?>);">新增评价</a>&nbsp;|&nbsp;<a href="index.php?act=evaluate&op=goods_evaluate_detail&goods_id=<?php echo $v['goods_id'] ?>" >查看评价</a></td>
        </tr>
        <tr style="display:none;">
          <td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td>
        </tr>
        <?php } ?>
        <?php } else { ?>
        <tr class="no_data">
          <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td></td>
          <td colspan="16"> <div class="pagination"> <?php echo $output['page'];?> </div></td>
        </tr>
      </tfoot>
    </table>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>

<script type="text/javascript">
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";

function goods_evaluate(goods_id){
    ajax_form('dialog_id', '评价', 'index.php?act=evaluate&op=add_evaluate&goods_id='+goods_id);
}

$(function(){
	
    $('#ncsubmit').click(function(){
        $('input[name="op"]').val('goods');$('#formSearch').submit();
    });



    // ajax获取商品列表
    $('i[nctype="ajaxGoodsList"]').toggle(
        function(){
            $(this).removeClass('icon-plus-sign').addClass('icon-minus-sign');
            var _parenttr = $(this).parents('tr');
            var _commonid = $(this).attr('data-comminid');
            var _div = _parenttr.next().find('.ncsc-goods-sku');
            if (_div.html() == '') {
                $.getJSON('index.php?act=goods&op=get_goods_list_ajax' , {commonid : _commonid}, function(date){
                    if (date != 'false') {
                        var _ul = $('<ul class="ncsc-goods-sku-list"></ul>');
                        $.each(date, function(i, o){
                            $('<li><input type="checkbox" name="gid[]" value="' + o.goods_id + '"/><div class="goods-thumb" title="商家货号：' + o.goods_serial + '"><a href="' + o.url + '" target="_blank"><image src="' + o.goods_image + '" ></a></div>' + o.goods_spec + '<div class="goods-price">价格：<em title="￥' + o.goods_price + '">￥' + o.goods_price + '</em></div><div class="goods-storage">库存：<em title="' + o.goods_storage + '">' + o.goods_storage + '</em></div><a href="' + o.url + '" target="_blank" class="ncsc-btn-mini">查看商品详情</a></li>').appendTo(_ul);
                            });
                        _ul.appendTo(_div);
                        _parenttr.next().show();
                        // 计算div的宽度
                        _div.css('width', document.body.clientWidth-54);
                        _div.perfectScrollbar();
                    }
                });
            } else {
            	_parenttr.next().show()
            }
        },
        function(){
            $(this).removeClass('icon-minus-sign').addClass('icon-plus-sign');
            $(this).parents('tr').next().hide();
        }
    );
});

// 获得选中ID
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

</script>
