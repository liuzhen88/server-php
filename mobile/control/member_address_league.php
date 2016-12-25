<?php

/**
 * 爱大腿的地址
 *
 */

defined('emall') or exit('Access Invalid!');

class member_address_leagueControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 地址列表
     */
    public function adt_address_listOp() {
        $model_address = Model('address_league');
        $address_list = $model_address->where(array('member_id' => $this->member_info['member_id']))->select();
        output_data(array('address_list' => $address_list));
    }

    /**
     * 地址详细信息
     * 
     * @param string $param['key']	  唯一校验码
     * @param int $param['address_id']   地址ID
     */
    public function adt_address_infoOp() {
        $address_id = intval($_REQUEST['address_id']);

        $model_address = Model('address_league');

        $condition = array();
        $condition['address_id'] = $address_id;
        $address_info = $model_address->where($condition)->find();
        if (!empty($address_info) && $address_info['member_id'] == $this->member_info['member_id']) {
            $store_info=Model('store')->adt_get_store_by_lication(floatval($address_info['lat']),floatval($address_info['lng']));
            output_data(array('address_info' => $address_info,'store_info'=>$store_info));
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
    public function adt_address_delOp() {
        $address_id = intval($_REQUEST['address_id']);


        // 验证地址是否为本人
        $address_info = Model('address_league')->where('address_id='.$address_id)->find();
        if ($address_info['member_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }
        
        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['member_id'] = $this->member_info['member_id'];
        $address_info = Model('address_league')->where($condition)->delete();
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
    public function adt_address_addOp() {
       // $_REQUEST['lat']=11;
       // $_REQUEST['lng']=11;

        $address_info = $this->_address_valid();

        /*---------将已默认地址设置为非默认 START--------*/
        if (isset($_REQUEST['is_default']) && $_REQUEST['is_default'] == 1) {
            $default_cond = array(
                'member_id'   => $address_info['member_id'],
                'is_default'  => 1,
            );
            Model('address')->adt_editAddress(array('is_default' => 0), $default_cond);
        }
       /*---------将已默认地址设置为非默认 END--------*/

        $model_address = Model('address_league');
        $result = $model_address->insert($address_info);
        $store_info=Model('store')->adt_get_store_by_lication(floatval($address_info['lat']),floatval($address_info['lng']));
        if ($result) {
            output_data(array('address_id' => $result,'store_info'=>$store_info));
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
     */
    public function adt_address_editOp() {
       // $_REQUEST['lat']=11;
       // $_REQUEST['lng']=11;
        $address_id = intval($_REQUEST['address_id']);

        $model_address = Model('address');

        //验证地址是否为本人
        $address_info = Model('address_league')->where('address_id='.$address_id)->find();
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
            $model_address->adt_editAddress(array('is_default' => 0), $default_cond);
        }
       /*---------将已默认地址设置为非默认 END--------*/
        
        $result = $model_address->adt_editAddress($address_info, array('address_id' => $address_id));
        $store_info=Model('store')->adt_get_store_by_lication(floatval($address_info['lat']),floatval($address_info['lng']));
        if ($result) {
            output_data(array('store_info'=>$store_info));
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
//            array("input" => $_REQUEST["area_info"], "require" => "true", "message" => '地区不能为空'),
            array("input" => $_REQUEST["address"], "require" => "true", "message" => '地址不能为空'),
            array("input" => $_REQUEST['mob_phone'], 'require' => 'true', 'message' => '联系方式不能为空'),
            array("input" => $_REQUEST['lat'], 'require' => 'true', 'message' => '经纬度不能为空'),
            array("input" => $_REQUEST['lng'], 'require' => 'true', 'message' => '经纬度不能为空'),
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
        $data['area_info'] = isset($_REQUEST['area_info'])?$_REQUEST['area_info']:'';
        $data['address'] = $_REQUEST['address'];
        $data['tel_phone'] = isset($_REQUEST['tel_phone'])?$_REQUEST['tel_phone']:'';
        $data['mob_phone'] = $_REQUEST['mob_phone'];
        $data['is_default'] = isset($_REQUEST['is_default']) && $_REQUEST['is_default'] == 1 ? 1 : 0;
        $data['lat'] = floatval($_REQUEST['lat']);
        $data['lng'] = floatval($_REQUEST['lng']);
        $data['door_number'] = isset($_REQUEST['door_number'])?$_REQUEST['door_number']:'';
        return $data;
    }



}
