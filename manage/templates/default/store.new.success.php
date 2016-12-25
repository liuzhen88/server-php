<?php defined('emall') or exit('Access Invalid!');?>

<div class="page new">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['store'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=store&op=store"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="index.php?act=store&op=store_joinin" ><span><?php echo $lang['pending'];?></span></a></li>
        <li><a href="index.php?act=store&op=reopen_list" ><span>续签申请</span></a></li>
        <li><a href="index.php?act=store&op=store_bind_class_applay_list" ><span>经营类目申请</span></a></li>
          <li><a href="index.php?act=store&op=store_joinin_o2o" ><span>本土开店申请</span></a></li>
          <li><a href="index.php?act=store&op=adt_add_store" class="current"><span>新增店铺</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <div class="step step4">
      <div class="step_show">

      </div>
      <div class="step_text">
          <div class="txt">设置用户名</div>
          <div class="txt">填写商户信息</div>
          <div class="txt">设置银行信息</div>
          <div class="txt">添加成功</div>
          <div class="clearfix"></div>
      </div>
      <div class="clearfix"></div>
  </div>
    <div class="new_success">
        <div class="new_success_logo"></div>
        <div class="color_333">新用户添加成功</div>
    </div>
    <a class="new_success_btn text_center " href="index.php?act=store&op=store">确定</a>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>

