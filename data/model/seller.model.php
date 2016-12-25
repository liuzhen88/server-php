<?php
/**
 * 卖家账号模型
 * 
 *

 */
defined('emall') or exit('Access Invalid!');
class sellerModel extends Model{

    public function __construct(){
        parent::__construct('seller');
    }

	/**
	 * 读取列表 
	 * @param array $condition
	 *
	 */
	public function getSellerList($condition, $page='', $order='', $field='*') {
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
	}

    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getSellerInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

	/*
	 *  判断是否存在 
	 *  @param array $condition
     *
	 */
	public function isSellerExist($condition) {
        $result = $this->getSellerInfo($condition);
        if(empty($result)) {
            return FALSE;
        } else {
            return TRUE;
        }
	}

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function addSeller($param){
        return $this->insert($param);	
    }
	
	/*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
	 */
    public function editSeller($update, $condition){
        return $this->where($condition)->update($update);
    }
	
	/*
	 * 删除
	 * @param array $condition
	 * @return bool
	 */
    public function delSeller($condition){
        return $this->where($condition)->delete();
    }
    /**
     * 检查该手机号是否可以添加商户
     * 0：不可已，1可以，但是已存在，不需要设置密码，2可以，需要设置密码
     */
    public function adt_check_mobile($mobile,&$member_info=array()){
        $seller_check=$this->table('seller')->where(array('seller_name'=>$mobile))->find();
        if(!empty($seller_check)){
            return 0 ;
        }
        $member_check=Model('member')->getMemberInfo(array('member_name'=>$mobile),'*',true);
        if(!empty($member_check)){
            $member_info=$member_check;
            wcache($member_check['member_id'], $member_check, 'member');
           return 1;
        }
        return 2 ;
    }

}
