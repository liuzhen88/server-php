<?php
/**
 * 我的邀请
 *
 * @author lijunhua 
 * @since 2015-08-07
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_invitationControl extends mobileMemberControl {

    public function __construct() {
            parent::__construct();
    }

        
    /**
     * 我的邀请-人员列表
     * 
     * @author xuping 
     * @modify lijunhua
     * @param string $param['key']        登录后唯一校验码
     * @param int    $param['type']       邀请类别  1 一级邀请  2 二级邀请
     * @param string $param['start_time'] 开始时间(选填，日期格式, 如2015-08-07 00:00:00)
     * @param string $param['end_time']   结束时间(选填，日期格式, 如2015-08-08 12:59:43)
     * @param string $param['curpage']    当前页  默认第1页
     */
    public function indexOp(){
        $condition_arr = array();
        if (intval($_REQUEST['type']) == 1) {
            $condition_arr['firest_inviter'] = $this->member_info['member_id'];
            $total_number = $this->_count_firest_inviter($this->member_info['member_id']);
        } elseif (intval($_REQUEST['type'] )== 2) {
            $condition_arr['second_inviter'] = $this->member_info['member_id'];
            $total_number = $this->_count_second_inviter($this->member_info['member_id']);
        } else {
            output_error('参数不合法',array(), ERROR_CODE_ARG);
        }
        

        $start_unixtime = strtotime($_REQUEST['start_time']);
        $end_unixtime   = strtotime($_REQUEST['end_time']);
        if ($start_unixtime || $end_unixtime) {
            $condition_arr['member_time'] = array('time',array($start_unixtime, $end_unixtime));
        }
        
        // 查询受邀请的会员列表
        $member_model = Model('member');
        $invitation_list = $member_model->getMemberList($condition_arr, 'member_id,member_time,member_name,member_truename,member_mobile', $this->page);
        
        $result = array();
        if (!empty($invitation_list)) {
            foreach ((array)$invitation_list as $key => $value) {
                  $data['avator']          = getMemberAvatarForID($value['member_id']);
                  $data['member_name']     = $this->_check_null($value['member_name']);
                  $data['member_id']       = '';//$this->_check_null($value['member_id']);//老版本不需要上member_id 20
                  $data['member_truename'] = $this->_check_null($value['member_truename']);
                  $data['member_mobile']   = $this->_check_null($value['member_mobile']) ;
                  $data['member_time']     = $this->_check_null($value['member_time']);
                  $result[] = $data;
             } 
        }
        
        $page_count = $member_model->gettotalpage();

        output_data(array('invitation_list' => $result, 'total_number' => $total_number), mobile_page($page_count));

    }
    
   
    /**
     * 一级邀请
     */
    private function _count_firest_inviter($member_id) {
        return Model('member')->getMemberCount(array('firest_inviter' => $member_id));
    }
    /**
     * 二级邀请
     */
    private function _count_second_inviter($member_id) {
        return Model('member')->getMemberCount(array('second_inviter' => $member_id));
    }    
    
    
}
