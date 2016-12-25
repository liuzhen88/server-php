<?php
/**
 * 代理商公告
 *
 ***/

defined('emall') or exit('Access Invalid!');
class agent_noticeControl extends SystemControl{
    public function __construct() {
        parent::__construct();
    }
    /**
     * 已经发的公告列表
     */
    public function indexOp() {
        $agent_notice = Model('agent_notice');
        $where = array();
        if ($_GET['search_name'] != '') {
            $where['notice_content'] = array('like', '%' . $_GET['search_name'] . '%');
            Tpl::output('search_name', $_GET['search_name']);
        }

        $result=$agent_notice->where($where)->select();
        Tpl::output('show_page', $agent_notice->showpage());
        Tpl::output('agent_notice', $result);        
        Tpl::showpage('agent_notice.index');
    }

    /*
     添加公告内容/
     xuping
     2015年9月25日10:59:20
     */
    public function add_noticeOP() {
        if($_POST['form_submit'] != 'ok'){
            if(!empty($_GET['id'])){
                 $agent_notice = Model('agent_notice');
                 $where['id']=intval($_GET['id']);
                 $result=$agent_notice->where($where)->find();
                 Tpl::output('notice_list',$result);
            }
            Tpl::showpage('agent_notice.add');
            exit;
        }
        //提交表单
        $obj_validate = new Validate();
        $validate_arr[] = array("input"=>$_POST["notice_agent"],"require"=>"true","message"=>'公告内容不能为空');
        $validate_arr[] = array("input"=>$_POST["notice_type"],"require"=>"true","message"=>'公告类型不能为空');
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != ''){
            showMessage(Language::get('error').$error,'','','error');
        }

        //保存公告
        $input  = array();

        $input['notice_content']    = trim($_POST['notice_agent']);
        $input['notice_type']       = intval($_POST['notice_type']);
        $input['state']             = 1;
        $input['addtime']           = time();
        $activity   = Model('agent_notice');
        if($_GET['id']){
            $input['id']           = intval($_GET['id']);
            $result = $activity->update($input);
        }else{
            $result = $activity->insert($input);
        }
        
        if($result){
            showMessage('公告添加成功','index.php?act=agent_notice&op=index');
        }else{
            showMessage('公告添加失败');
        }
    }

    /*
    删除公告内容
    xuping
    2015年9月25日10:41:10
     */
    public function del_noticeOP(){
        $id=intval($_GET['id']);
        $agent_notice = Model('agent_notice');
        $where['id']=$id;
        $result=$agent_notice->where($where)->select();
        if($result){
            $agent_notice->delete($id);
            showMessage('公告删除成功','index.php?act=agent_notice&op=index');
        }else{
            showMessage('公告删除失败');
        }
    }

}
