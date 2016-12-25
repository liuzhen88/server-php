
<form action="<?php echo urlAdmin('evaluate','edit_evaluate'); ?>" method="post">
<table style="margin:25px">
    <tr>
        <td>评价内容</td>
        <td><textarea name="content" ><?php echo $output['evaluate_info']['geval_content'] ?></textarea></td>
    </tr>
    <tr>
        <td>评分</td>
        <td>
            <?php
                $score=array(1,2,3,4,5);
                foreach($score as $value){
            ?>
            <input type="radio" name="score" value="<?php echo $value ?>" <?php if($value==$output['evaluate_info']['geval_scores']) { echo 'checked';} ?>> <?php echo $value ?>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <input type="hidden" name="form_submit" value="ok" />
            <input type="hidden" name="geval_id" value="<?php echo $_GET['geval_id'] ?>" />
            <input type="button" class="button cancel" value="取消" />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="submit" class="button" value="提交" />
        </td>
    </tr>
</table>
</form>
<script>

    $('.cancel').click(function(){
        DialogManager.close("dialog_id");
    });
</script>