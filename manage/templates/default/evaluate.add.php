
<form action="<?php echo urlAdmin('evaluate','add_evaluate'); ?>" method="post">
<table style="margin:25px">
    <tr>
        <td>评价内容</td>
        <td><textarea name="content" ></textarea></td>
    </tr>
    <tr>
        <td>评分</td>
        <td>
            <input type="radio" name="score" value="1">1
            <input type="radio" name="score" value="2">2
            <input type="radio" name="score" value="3">3
            <input type="radio" name="score" value="4">4
            <input type="radio" name="score" value="5" checked >5
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <input type="hidden" name="form_submit" value="ok" />
            <input type="hidden" name="goods_id" value="<?php echo $_GET['goods_id'] ?>" />
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