<?php

/**
 * @name 商户管理
 * @author yujia
 */
use Tpl;

defined('emall') or exit('Access Invalid!');

class sellerControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @name 商户登录模块
     * @param post,username用户名,password密码,client终端类型
     * @author xuping 
     * @return 200为成功返回,其他的是失败
     */
    public function indexOp() {
        //$type=array('android', 'wap', 'wechat', 'ios');
        if (empty($_REQUEST['username']) || empty($_REQUEST['password'])) {
           output_error('账号或密码为空', array(), ERROR_CODE_OPERATE);
            exit;
        } else {
            $model_store = Model('store');
            $model_seller= Model('seller');
            $res=$model_seller->where(array('seller_name'=> $_REQUEST['username']))->find();
            if (!empty($res)) {
                //验证商户信息
                $model_member = Model('member');
                $array['member_id']     = $res['member_id'];
                $member_info = $model_member->getMemberInfo($array);
                
                // 用户名未查到，进一步查询手机号码
                if (empty($member_info)) {
                    output_error('商户不存在', array(), ERROR_CODE_OPERATE);
                }

                if ($member_info['member_state'] == 0) {
                    output_error('账户被停用', array(), ERROR_CODE_OPERATE);
                }

                if ($member_info['member_passwd'] !=  md6($_REQUEST['password'], $member_info['member_salt'])) {
                    output_error('密码错误', array(), ERROR_CODE_OPERATE);
                }

                // 登录成功后，处理下registration_id
                if (isset($_REQUEST['registration_id']) && !empty($_REQUEST['registration_id'])) {
                    $model_member->set_registration_id($member_info['member_id'], $_REQUEST['registration_id']); 
                }
                
                // $array = array();
                $client=isset($_REQUEST['client_type']) ? $_REQUEST['client_type']:'';

                $result=$model_store->where(array('store_id'=>$res['store_id'],'store_state'=>1))->find();
                if(empty($result)){
                    output_error('店铺未开通', array(), ERROR_CODE_OPERATE);
                    exit;
                }
                if($result['store_type']!=1){
                    output_error('不允许商城用户登录', array(), ERROR_CODE_OPERATE);
                }

                $store_name = $result['store_name'];
                $store_id   = $result['store_id'];
                $is_agree   = $result['is_agree'];
                $tag        =$result['store_type'];
                $admin_id   = $result['member_id']; //店铺管理员的id 返回该用户id的会员信息给用户

                //管理员的账号信息
                $member_admin =$model_member->where(array('member_id'=>$admin_id))->field('member_name,member_points,member_id')->find();

                $token = $this->_get_token($member_info['member_id'] ,$member_info['member_name'], $client);
                if ($token) {
                    $arr = array(
                        'shop_id'   => $store_id,
                        'username'  => $member_admin['member_name'],
                        'points'    => $member_admin['member_points'],
                        'shop_admin_id'  => $admin_id,                  // 管理用户id
                        'member_id' => $member_info['member_id'],  //当前登录者的id
                        'storeName' => $store_name,
                        'tag'       => $tag,
                        'is_agree'  => $is_agree,
                        'key'       => $token
                    );
                    output_data($arr);
                    exit;
                } else {
                    output_error('生成token失败', array(), ERROR_CODE_OPERATE);
                    exit;
                }
            } else {
               output_error('账号或密码不正确', array(), ERROR_CODE_OPERATE);
                exit;
            }

        }
    }

    /* 商户登录 点击协议正确更改 */

    public function isAgreeOP() {
        $seller_id = $_REQUEST['store_id'];
        if (empty($seller_id)) {
            output_error('seller_id参数为空');
            exit;
        }
        $result = Model()->table('store')->where(array('store_id' => $seller_id))->find();
        if ($result['is_agree'] == 0) {
            $data = array(
                'is_agree' => 1
            );
            $res = Model()->table('store')->where(array('store_id' => $seller_id))->update($data);
            if ($res) {
                output_data('ok');
            } else {
                output_error('操作失败', array(), ERROR_CODE_OPERATE);
            }
        } else {
            output_error('操作失败', array(), ERROR_CODE_OPERATE);
        }
    }


    /*
     移动端 qq互联等以后模，绑定用户的qq的openid
     xuping 

     */
    public function bandMemberOP(){
        $openid=$_REQUEST['openid'];
        $model_member   = Model('member');
        //验证QQ账号用户是否已经存在
        $array  = array();
        $array['member_qqopenid']   = $openid;
        $member_info = $model_member->getMemberInfo($array);
        if (is_array($member_info) && count($member_info)>0){
            output_data('ok');//'该QQ账号已经绑定其他商城账号,请使用其他QQ账号与本账号绑定'
        }
        //获取qq账号信息
        require_once (BASE_PATH.'/api/qq/user/get_user_info.php');
        $qquser_info = get_user_info($_SESSION["appid"], $_SESSION["appkey"], $_SESSION["token"], $_SESSION["secret"], $_SESSION["openid"]);
        $edit_state     = $model_member->editMember(array('member_id'=>$_SESSION['member_id']), array('member_qqopenid'=>$_SESSION['openid'], 'member_qqinfo'=>serialize($qquser_info)));
        if ($edit_state){
            showMessage(Language::get('home_qqconnect_binding_success'),'index.php?act=member_connect&op=qqbind');
        }else {
            showMessage(Language::get('home_qqconnect_binding_fail'),'index.php?act=member_connect&op=qqbind','html','error');//'绑定QQ失败'
        }
    }
    /**
     * @name 商户列表模块
     * @author yujia 15.6.18
     * @return json数据
     */
    public function storeListOp() {
        $store_model = Model('store');
        $result = $store_model->select();
        if ($result) {
            json_encode($result);
        } else {
            json_encode('不存在用户');
        }
    }

    /**
     * @name 商户信息
     * @author yujia 15.6.23
     * @return json数据
     */
    public function storeInfoOp() {
        $store_model = Model('store');
        $result = $store_model->select();
        if ($result) {
            output_data($result);
        } else {
            output_error('不存在商户');
        }
    }

    /**
     * @name 登录生成token
     */
    private function _get_token($member_id, $member_name, $client) {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['client_type'] = $client;
        $condition['token_type'] = 1;
        $model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
        $mb_user_token_info['token_type'] = 1;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if ($result) {
            return $token;
        } else {
            return null;
        }
    }

}
