<?php defined('emall') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>代理商/商户公告</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=agent_notice&op=add_notice" ><span>新增公告</span></a></li>
      </ul>
    </div>
  </div>

  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" name="act" value="agent_notice">
    <input type="hidden" name="op" value="index">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="searchtitle">内容关键字</label></th>
          <td><input type="text" name="search_name" id="searchtitle" class="txt" value='<?php echo $_GET['search_name'];?>'></td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="查询">&nbsp;</a></td>
        </tr>
      </tbody>
    </table>
  </form>


    <table class="table tb-type2">
      <thead>
        <tr class="thead">
      
          <th class="w48 ">序号</th>
          <th class="w96">公告内容</th>
          <th class="align-center">公告类型</th>
          <th class="align-center">添加时间</th>
          <th class="align-center">操作</th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['agent_notice']) && is_array($output['agent_notice'])){ ?>
        <?php foreach($output['agent_notice'] as $k => $v){ ?>
        <tr class="hover edit row">

          <td class="name"><span> <?php echo $v['id'];?></span></td>

          <td class="name"><span><?php echo mb_substr($v['notice_content'],0 ,5,"utf-8").'...';?></span></td>
         
           <td class="align-center"><?php switch($v['notice_type']){
      					case '1':
      						echo '代理商公告';
      						break;
      					case '2':
      						echo '商户公告';
      						break;
      				}?>
        </td>
        
         <td class="nowrap align-center"><?php echo @date('Y-m-d H:i:s',$v['addtime']);?></td>
       
        <td class="nowrap align-center"><span><a href="index.php?act=agent_notice&op=add_notice&id=<?php echo $v['id'];?>" class="btn-blue">
        <p>编辑</p>
        </a></span> <span><a class="btn-red" href="index.php?act=agent_notice&op=del_notice&id=<?php echo $v['id'];?>" class="btn-blue">
        <p><?php echo $lang['nc_del'];?></p>
        </a></span></td>
          

        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <?php if(!empty($output['agent_notice']) && is_array($output['agent_notice'])){ ?>
        <tr class="tfoot">
          <td colspan="16">
            <div class="pagination"> <?php echo $output['show_page'];?> </div></td>
          </tr>

        <?php } ?>
      </tfoot>
    </table>

</div>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/jquery.ui.js";?>"></script> 
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.goods_class.js" charset="utf-8"></script>
<script type="text/javascript">
$("#searchstartdate").datepicker({dateFormat: 'yy-mm-dd'});
$("#searchenddate").datepicker({dateFormat: 'yy-mm-dd'});
function submit_form(op){
	if(op=='del'){
		if(!confirm('<?php echo $lang['nc_ensure_del'];?>')){
			return false;
		}
	}
	$('#listop').val(op);
	$('#listform').submit();
}
</script>