<?php

/**
 * 我的地址
 *
 */
use Tpl;

defined('emall') or exit('Access Invalid!');

class member_addressControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 地址列表
     */
    public function address_listOp() {
        $model_address = Model('address');
        $address_list = $model_address->getAddressList(array('member_id' => $this->member_info['member_id']));
        output_data(array('address_list' => $address_list));
    }

    /**
     * 地址详细信息
     * 
     * @param string $param['key']	  唯一校验码
     * @param int $param['address_id']   地址ID
     */
    public function address_infoOp() {
        $address_id = intval($_REQUEST['address_id']);

        $model_address = Model('address');

        $condition = array();
        $condition['address_id'] = $address_id;
        $address_info = $model_address->getAddressInfo($condition);
        if (!empty($address_id) && $address_info['member_id'] == $this->member_info['member_id']) {
            output_data(array('address_info' => $address_info));
        } else {
            output_error('地址不存在');
        }
    }

    /**
     * 删除地址
     * 
     * @param string $param['key']	  唯一校验码
     * @param int $param['address_id']   地址ID
     */
    public function address_delOp() {
        $address_id = intval($_REQUEST['address_id']);

        $model_address = Model('address');

        // 验证地址是否为本人
        $address_info = $model_address->getOneAddress($address_id);
        if ($address_info['member_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }
        
        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['member_id'] = $this->member_info['member_id'];
        $model_address->delAddress($condition);
        output_data('1');
    }

    /**
     * 新增地址
     *  
     * @param string $param['key']	   唯一校验码
     * @param string $param['true_name']  姓名
     * @param string $param['area_info']  地区
     * @param string $param['address']    地址
     * @param string $param['tel_phone']  电话号码 
     * @param string $param['mob_phone']  手机号码
     * @param string $param['is_default'] 是否默认地址（选填） 默认地址为1
     * @param string $param['post'] 邮编
     */
    public function address_addOp() {
        $model_address = Model('address');

        $address_info = $this->_address_valid();

        /*---------将已默认地址设置为非默认 START--------*/
        if (isset($_REQUEST['is_default']) && $_REQUEST['is_default'] == 1) {
            $default_cond = array(
                'member_id'   => $address_info['member_id'],
                'is_default'  => 1,
            );
            $model_address->editAddress(array('is_default' => 0), $default_cond);
        }
       /*---------将已默认地址设置为非默认 END--------*/
        
        $result = $model_address->addAddress($address_info);
        if ($result) {
            output_data(array('address_id' => $result));
        } else {
            output_error('保存失败');
        }
    }

    /**
     * 编辑地址
     * 
     * @param string $param['key']	   唯一校验码
     * @param string $param['address_id'] 地址ID  
     * @param string $param['true_name']  姓名
     * @param string $param['area_info']  地区
     * @param string $param['address']    地址
     * @param string $param['tel_phone']  电话号码 
     * @param string $param['mob_phone']  手机号码
     * @param string $param['is_default'] 是否默认地址（选填） 默认地址为1
     * @param string $param['post'] 邮编
     */
    public function address_editOp() {
        $address_id = intval($_REQUEST['address_id']);

        $model_address = Model('address');

        //验证地址是否为本人
        $address_info = $model_address->getOneAddress($address_id);
        if ($address_info['member_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        $address_info = $this->_address_valid();

        /*---------将已默认地址设置为非默认 START--------*/
        if (isset($_REQUEST['is_default']) && $_REQUEST['is_default'] == 1) {
            $default_cond = array(
                'member_id'   => $address_info['member_id'],
                'address_id'  => array('neq', $address_id),
                'is_default'  => 1,
            );
            $model_address->editAddress(array('is_default' => 0), $default_cond);
        }
       /*---------将已默认地址设置为非默认 END--------*/
        
        $result = $model_address->editAddress($address_info, array('address_id' => $address_id));
        if ($result) {
            output_data('1');
        } else {
            output_error('保存失败');
        }
    }
    

    /**
     * 验证地址数据
     */
    private function _address_valid() {
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input" => $_REQUEST["true_name"], "require" => "true", "message" => '姓名不能为空'),
            array("input" => $_REQUEST["area_info"], "require" => "true", "message" => '地区不能为空'),
            array("input" => $_REQUEST["address"], "require" => "true", "message" => '地址不能为空'),
//            array("input" => $_REQUEST["post"], "require" => "true", "message" => '邮编不能为空'),
            array("input" => $_REQUEST['mob_phone'], 'require' => 'true', 'message' => '联系方式不能为空')
        );
        $error = $obj_validate->validate();
        if ($error != '') {
            output_error($error);
        }

        $data = array();
        $data['member_id'] = $this->member_info['member_id'];
        $data['true_name'] = $_REQUEST['true_name'];
        $data['area_id'] = intval($_REQUEST['area_id']);
        $data['city_id'] = intval($_REQUEST['city_id']);
        $data['area_info'] = $_REQUEST['area_info'];
        $data['address'] = $_REQUEST['address'];
        $data['tel_phone'] = $_REQUEST['tel_phone'];
        $data['mob_phone'] = $_REQUEST['mob_phone'];
        $data['post'] = isset($_REQUEST['post'])?$_REQUEST['post']:'';
        $data['is_default'] = isset($_REQUEST['is_default']) && $_REQUEST['is_default'] == 1 ? 1 : 0;
        return $data;
    }

    /**
     * 地区列表
     */
    public function area_listOp() {
        $area_id = intval($_REQUEST['area_id']);

        $model_area = Model('area');

        $condition = array();
        if ($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }

}
