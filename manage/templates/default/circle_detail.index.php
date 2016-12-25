<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <table class="table tb-type2 order">
    <tbody>
      <tr class="space">
        <th colspan="2">查看帖子详情</th>
      </tr>
      <tr>
        <th>文字信息</th>
      </tr>
      <tr>
        <td colspan="2">
            <strong>文字部分:</strong><?php echo $output['cirlce']['theme_content'];?>

          </td>
      </tr>

      <tr>
        <th>图片信息</th>
      </tr>
      <tr>
        <td>
          <img src="<?php echo getMemberAvatar($output['cirlce']['theme_pic']);?>"  width="400" height="300"/>
        </td>
          </tr>


      <tr>
        <th><?php echo $lang['product_info'];?></th>
      </tr>
      <tr>
        <td><table class="table tb-type2 goods ">
            <tbody>
              <tr>
                <th class="align-center">举报编号</th>
                <th class="align-center">举报人</th>
                <th class="align-center">举报类型</th>
                <th class="align-center">举报时间</th>
                <th class="align-center">操作</th>
              </tr>
              <?php if($output['cirlce']['report_info']){ ?>
              <?php foreach($output['cirlce']['report_info'] as $report){?>
              <tr>
                <td class="w96 align-center"><span class="red_common"><?php echo $report['id'];?></span></td>
                <td class="w96 align-center"><span class="red_common"><?php echo $report['report_member_id'];?></span></td>
                <td class="w96 align-center"><?php echo $report['comment'];?></td>
                <td class="w96 align-center"><?php echo date('Y-m-d H:i:s',$report['addtime'])?></td>
                <td class="w96 align-center">
                <a href="index.php?act=circle_manage&op=theme_nodel&theme_id=<?php echo $report['theme_id'];?>">屏蔽帖子</a> 
                </td>
              </tr>
              <?php }?>
              <?php }?>

            </tbody>
          </table></td>
      </tr>
    
    </tbody>
    <tfoot>
      <tr class="tfoot">
        <td><a href="JavaScript:void(0);" class="btn" onclick="history.go(-1)"><span><?php echo $lang['nc_back'];?></span></a></td>
      </tr>
    </tfoot>
  </table>
</div>
