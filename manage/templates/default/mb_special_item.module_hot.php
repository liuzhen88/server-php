<?php defined('emall') or exit('Access Invalid!');?>
<style type="text/css">
    .hot .item{
        text-align: left;
    }
</style>
<?php if($item_edit_flag) { ?>
<table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"> <div class="title nomargin">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span> </div>
        </th>
      </tr>
      <tr>
        <td><ul>
            <li>鼠标移动到内容上出现编辑按钮可以对内容进行修改</li>
            <li>操作完成后点击保存编辑按钮进行保存</li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <?php } ?>
<div class="index_block hot">
  <?php if($item_edit_flag) { ?>
  <h3>热点板块</h3>
  <?php } ?>
    <div nctype="item_content" class="content">
        <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
            <?php foreach($item_data['item'] as $item_key => $item_value) {?>
                <div nctype="item_image" class="item">
                    <?php if($item_edit_flag) { ?>
                        热点:<input nctype="image_title" name="item_data[item][<?php echo $item_key;?>][title]" type="text" value="<?php echo $item_value['title'];?>">
                        <input nctype="image_type" name="item_data[item][<?php echo $item_key;?>][type]" type="hidden" value="<?php echo $item_value['type'];?>">
                        <input nctype="image_data" name="item_data[item][<?php echo $item_key;?>][data]" type="hidden" value="<?php echo $item_value['data'];?>">
                        <a nctype="btn_del_item_image" href="javascript:;"><i class="icon-trash"></i>删除</a>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
    <?php if($item_edit_flag):?>
    <a nctype="btn_add_item_image" class="btn-add" data-desc="640*240" href="javascript:;">添加热点</a>
    <?php endif;?>
</div>
