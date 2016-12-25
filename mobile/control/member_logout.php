<?php

/**
 * 注销
 * 
 * @author lijunhua
 * @since 2015-08-28
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_logoutControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 注销
     * 
     * @param string $param['key']                唯一校验码
     */
    public function indexOp() {

        $model_mb_user_token = Model('mb_user_token');
        $condition = array();
        $condition['member_id']   = $this->member_info['member_id'];
        $condition['client_type'] = $_REQUEST['client_type'];
        $result = $model_mb_user_token->delMbUserToken($condition);
        if (!$result) {
            output_error('注销失败', array(), ERROR_CODE_OPERATE);
        }
        output_data(array());
    }

}
