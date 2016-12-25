<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <h3>发现后台管理</h3>
            <ul class="tab-base">
                <li><a href="JavaScript:void(0);" class="current"><span>发现管理</span></a></li>
                <!-- <li><a href="index.php?act=agent&op=agent_add" ><span><?php echo $lang['nc_new'];?></span></a></li> -->
            </ul>
        </div>
    </div>
    <div class="fixed-empty"></div>
    <table class="table tb-type2" id="prompt">
        <!--     <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li><?php echo $lang['agent_help1'];?></li>
            <li><?php echo $lang['agent_help2'];?></li>
          </ul></td>
      </tr>
    </tbody> -->
    </table>
    <!--   <form method="post" name="formSearch">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="agent_company_name"><?php echo $lang['agent_company_name'];?></label></th>
          <td><input type="text" value="<?php echo $output['like_ac_name'];?>" name="like_ac_name" id="like_ac_name" class="txt"></td>
          <th><label><?php echo $lang['agent_mode']; ?></label></th>
          <td><select name="agent_mode">
                <option value=''><?php echo $lang['nc_please_choose']; ?>...</option>
                <option value="1" <?php echo $_GET['agent_mode'] == '1' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_mode_1']; ?></option>
                <option value="2" <?php echo $_GET['agent_mode'] == '2' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_mode_2']; ?></option>
            </select></td>
          <th><label><?php echo $lang['agent_grade']; ?></label></th>
          <td><select name="agent_grade">
                <option value=''><?php echo $lang['nc_please_choose']; ?>...</option>
                <?php for ($i=1;$i<=5;$i++) : ?>
                     <option value="<?php echo $i;?>" <?php echo $_GET['agent_grade'] == $i ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_grade_' . $i]; ?></option>
                <?php endfor;?>
            </select></td>
           <th><label><?php echo $lang['agent_area']; ?></label></th>
           <td><input type="hidden" class="txt w300"  name="agent_area_name" id="agent_area_name" /></td>
            <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a>
            <?php if($output['like_ac_name'] != ''){?>
               <a class="btns " href="index.php?act=agent&op=agent" title="<?php echo $lang['cancel_search'];?>"><span><?php echo $lang['cancel_search'];?></span></a>
            <?php }?></td>
        </tr>

      </tbody>
    </table>
  </form> -->
    <form method='post'>
        <input type="hidden" name="form_submit" value="ok" />
        <table class="table tb-type2 nobdb">
            <thead>
            <tr class="thead">
                <th>帖子id</th>
                <th>排序</th>
                <!--       <th>文字类容</th> -->
                <th>图片</th>
                <th>状态</th>
                <th>评论数量</th>
                <th>点赞数量</th>
                <th>添加时间</th>
                <th>管理操作</th>
            </tr>
            </thead>

            <tbody>

            <?php if(!empty($output['cirlce_list']) && is_array($output['cirlce_list'])){ ?>
                <?php foreach($output['cirlce_list'] as $k => $data){ ?>
                    <tr class="hover edit">
                        <!--  <td class="w36"><input type="checkbox" name='check_agent_id[]' value="<?php echo $data['agent_id'];?>" class="checkitem"></td> -->
                        <td><?php echo $data['theme_id'];?></td>
                        <td><?php echo $data['theme_id']; if($data['is_recommend']==1) echo '<span style="color:red">(推荐中..)</span>'; ?></td>
                        <!-- <td class="nowrap"><?php echo mb_substr($data['theme_content'], 0,5,'UTF-8'); ?></td> -->
                        <td class="nowrap"><img src="<?php echo getMemberAvatar($data['theme_pic']); ?>" width="120" height="80"></td>
                        <td class="nowrap">
                            <?php echo $data['is_closed']==0 ? '显示' : '<span style="color:red">屏蔽</span>'?>
                        </td>
                        <td class="nowrap"><?php echo $data['theme_commentcount']; ?></td>
                        <td class="nowrap"><?php echo $data['theme_likecount'];?></td>
                        <td class="nowrap"><?php echo date('Y-m-d H:i:s',$data['theme_addtime']) ?></td>
                        <td class="w84"><span>
          <?php if($data['is_recommend']==1) { ?>
              <a href="index.php?act=circle_manage&op=theme_recommed&theme_id=<?php echo $data['theme_id'];?>">取消推荐|</a>
          <?php  }else{ ?>
              <a href="index.php?act=circle_manage&op=re_theme_recommed&theme_id=<?php echo $data['theme_id'];?>">推荐|</a> <?php } ?>
                                <a href="index.php?act=circle_manage&op=theme_detail&theme_id=<?php echo $data['theme_id'];?>">查看</a>|
                  <a href="index.php?act=circle_manage&op=theme_del&theme_id=<?php echo $data['theme_id'];?>">删除</a>
              </span></td>
                    </tr>
                <?php } ?>
            <?php }else { ?>
                <tr class="no_data">
                    <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <?php if(!empty($output['cirlce_list']) && is_array($output['cirlce_list'])){ ?>
                <tr id="batchAction" >
                    <td></td>
                    <td colspan="16" id="dataFuncs">
                        <div class="pagination"> <?php echo $output['show_page'];?> </div></td>
                    </td>
                </tr>
            <?php } ?>
            </tfoot>
        </table>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script>
    var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
    $(function(){
        $("#agent_area_name").nc_region();
    });
