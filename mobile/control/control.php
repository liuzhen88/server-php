<?php
/**
 * mobile父类
 */
defined('emall') or exit('Access Invalid!');

/******* 前台control父类 *******/

class mobileControl{

    //客户端类型
    protected $client_type_array = array('android', 'wap', 'wechat', 'ios');
    //列表默认分页数
    protected $page = 8;


	public function __construct() {
        Language::read('mobile');
        
        // 内部需要GET传递curpage，这里做下兼容 by lijunhua 2015-08-18
        if (isset($_REQUEST['curpage'])) {
            $_GET['curpage'] = $_REQUEST['curpage'];
        }
        //分页数处理
        $page = intval($_REQUEST['page']);
        if($page > 0) {
            $this->page = $page;
        }
    }
}

class mobileHomeControl extends mobileControl{
	public function __construct() {
        parent::__construct();
    }
    /**
     * @name 为了防止又被覆盖模型,先增加一个方法
     * @param $code是状态码,$value是给定的值
     * @author yujia 15.7.13 add json_encode
     * 登录
     */
    public function jsoncode($code,$value=''){
      $data=array();
      $data['code']=$code;
      $data['data']=$value;
      return json_encode($data);
    }
}

class mobileMemberControl extends mobileControl{

    protected $member_info = array();
    /**
     * 2.1.5 新增token_member_id 参数  用于判断返回token失效信息
     */
	public function __construct() {
        parent::__construct();
        $model_mb_user_token = Model('mb_user_token');
        $key = $_POST['key'];
        if(empty($key)) {
            $key = $_GET['key'];
        }
        $token_member_id = $_REQUEST['token_member_id'];
        if(!empty($token_member_id)) {
            $member_where = array();
            $member_where['member_id'] = $token_member_id;
            $member_where['token_type'] = array('in',array(0,2));
            if(!empty($_REQUEST['client_type'])) {
                $member_where['client_type'] = $_REQUEST['client_type'];
                $member_info = $model_mb_user_token->getMbUserTokenList($member_where);
                if(!empty($member_info)) {
                    $i = 0;
                    for($i=0;$i<count($member_info);$i++) {
                        if($member_info[$i]['token']==$key) {
                            break;
                        }
                    }
                    if($i>=count($member_info)) {
                        if($_REQUEST['payment_code']=='alipay'){
                            echo '<script>window.location.href=\"http://shop.aigegou.com/agg/wap/tmpl/member/login.html\"</script>';
                            exit;
                        }
                        else {
                            $login_time = date('Y-m-d H:i:s',$member_info[0]['login_time']);
                            output_error('您的账号于'.$login_time.'在另一设备登录，如果不是您本人的操作，您的账号已经被盗号者登录，请尽快修改密码。', array('login' => '0'),ERROR_CODE_AUTH);
                        }
                    }
                }
                else {
                    if($_REQUEST['payment_code']=='alipay'){
                        echo '<script>window.location.href=\"http://shop.aigegou.com/agg/wap/tmpl/member/login.html\"</script>';
                        exit;
                    }
                    else {
                        $act = (isset($_REQUEST['act']) && !empty($_REQUEST['act'])) ? strtolower($_REQUEST['act']) : 'index';
                        $op = (isset($_REQUEST['op']) && !empty($_REQUEST['op'])) ? strtolower($_REQUEST['op']) : 'index';
                        if ($act == 'member_payment' && in_array($op, array('paycharge', 'userrecharge', 'apprecharge' ))){
                            output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
                        }else{
                            output_error('登录超时,请重新登录', array('login' => '0'),ERROR_CODE_AUTH);
                        }
                    }
                }
            }
            else {
                $member_where['token'] = $key;
                $member_info = $model_mb_user_token->getMbUserTokenInfo($member_where);
                if(empty($member_info)) {
                    if($_REQUEST['payment_code']=='alipay'){
                        echo '<script>window.location.href=\"http://shop.aigegou.com/agg/wap/tmpl/member/login.html\"</script>';
                        exit;
                    }
                    else {
                        $act = (isset($_REQUEST['act']) && !empty($_REQUEST['act'])) ? strtolower($_REQUEST['act']) : 'index';
                        $op = (isset($_REQUEST['op']) && !empty($_REQUEST['op'])) ? strtolower($_REQUEST['op']) : 'index';
                        if ($act == 'member_payment' && in_array($op, array('paycharge', 'userrecharge', 'apprecharge' ))){
                            output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
                        }else{
                            output_error('登录超时,请重新登录', array('login' => '0'),ERROR_CODE_AUTH);
                        }
                    }
                }
            }
        }
        else {
            //原有逻辑
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);

            if($_REQUEST['payment_code']=='alipay'  && empty($mb_user_token_info) ){

                echo '<script>window.location.href=\"http://shop.aigegou.com/agg/wap/tmpl/member/login.html\"</script>';
                exit;
            }
            elseif(empty($mb_user_token_info)) {
                $act = (isset($_REQUEST['act']) && !empty($_REQUEST['act'])) ? strtolower($_REQUEST['act']) : 'index';
                $op = (isset($_REQUEST['op']) && !empty($_REQUEST['op'])) ? strtolower($_REQUEST['op']) : 'index';
                if ($act == 'member_payment' && in_array($op, array('paycharge', 'userrecharge', 'apprecharge' ))){
                    output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
                }else{
                    output_error('登录超时,请重新登录', array('login' => '0'),ERROR_CODE_AUTH);
                }
            }
            $token_member_id = $mb_user_token_info['member_id'];
        }
        $model_member = Model('member');
        $this->member_info = $model_member->getMemberInfoByID($token_member_id);
        $this->member_info['client_type'] = $mb_user_token_info['client_type'];
        if(empty($this->member_info)) {
            output_error('请登录', array('login' => '0'),ERROR_CODE_AUTH);
        } else {
            //读取卖家信息
            $seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
            $this->member_info['store_id'] = $seller_info['store_id'];
        }
    }

    protected function _check_null($value='') {
        return empty($value) ? '' : $value;
    }
}



/**
 * 店铺 control新父类   api
 *
 */
class BaseSellerControl extends mobileControl {
    /*
      @author :xuping
      @date:2015年8月3日14:36:05
      @param : token
      @ to 验证商户是否 已经登录过
     */
    protected $member_info = array();
    protected $store_info = array();    //商户信息
    protected $seller_info = array();   //店员信息
    protected $admin_member_info = array(); //店铺管理员信息
    public function __construct(){
        parent::__construct();
        $model_mb_user_token = Model('mb_user_token');
        $key = $_REQUEST['key'];

        $token_member_id = $_REQUEST['token_member_id'];
        if(!empty($token_member_id)) {
            $member_where = array();
            $member_where['member_id'] = $token_member_id;
            $member_where['token_type'] = 1;
            if(!empty($_REQUEST['client_type'])) {
                $member_where['client_type'] = $_REQUEST['client_type'];
                $member_info = $model_mb_user_token->getMbUserTokenInfo($member_where);
                if(!empty($member_info)) {
                    if(!empty($member_info['token'])) {
                        if($member_info['token']!=$key) {
                            $login_time = date('Y-m-d H:i:s',$member_info['login_time']);
                            output_error('您的账号于'.$login_time.'在另一设备登录，如果不是您本人的操作，您的账号已经被盗号者登录，请尽快修改密码。', array(),ERROR_CODE_SELLER_AUTH);
                        }
                    }
                    else {
                        output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
                    }
                }
                else {
                    output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
                }
            }
            else {
                $member_where['token'] = $key;
                $member_info = $model_mb_user_token->getMbUserTokenInfo($member_where);
                if(empty($member_info)) {
                    output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
                }
            }
        }
        else {
            //原有逻辑
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
            if(empty($mb_user_token_info)) {
                output_error('登录超时,请重新登录',array(), ERROR_CODE_SELLER_AUTH);
            }
            $token_member_id = $mb_user_token_info['member_id'];
        }
        $model_member = Model('member');

        $this->member_info = $model_member->getMemberInfoByID($token_member_id);
        $this->member_info['client_type'] = $mb_user_token_info['client_type'];
        
        if(empty($this->member_info)) {
             output_error('请重试',array(), ERROR_CODE_AUTH);
        }


        $this->seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
        if(empty($this->seller_info)) {
            output_error('请重试',array(), ERROR_CODE_AUTH);
        }

        $this->store_info = Model('store')->where(array('store_id'=>$this->seller_info['store_id']))->find();
        if($this->store_info['store_state'] !=1){
           output_error('店铺已经被封，请与管理员联系',array(),ERROR_CODE_OPERATE);
        }

        if(empty($this->store_info)) {
            output_error('请重试',array(), ERROR_CODE_AUTH);
        }
        //店铺管理员信息
        $this->admin_member_info =  $model_member->getMemberInfoByID($this->store_info['member_id']);
    }
}

/**
 * 爱大腿店铺基类
 *
 */
class BaseStoreLeagueControl extends mobileControl {
    /*
     @author :chenyifei
     @date:2015年12月5日14:36:05
     @param : token
     @ to 验证商户是否 已经登录过
     */
    protected $seller_member_info = array();   //店员用户信息（非店铺和店铺管理员信息，使用时注意区分）
    protected $seller_info = array();   //店员信息（非店铺和店铺管理员信息，使用时注意区分）
    protected $store_info = array();    //商户信息
    
    public function __construct(){
        parent::__construct();

        $model_mb_user_token = Model('mb_seller_token');
        $key = $_REQUEST['key'];

        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);

        if(empty($mb_user_token_info)) {
            output_error('请重新登录哦',array(), ERROR_CODE_AUTH);
        }

        $model_member = Model('member');

        $this->seller_member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
        if(empty($this->seller_member_info)) {
            output_error('此员工不存在',array(), ERROR_CODE_AUTH);
        }
        $this->seller_member_info['client_type'] = $mb_user_token_info['client_type'];        

        $this->seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->seller_member_info['member_id']));
        if(empty($this->seller_info)) {
            output_error('此员工不存在',array(), ERROR_CODE_AUTH);
        }

        $this->store_info = Model('store')->getStoreInfoByID($this->seller_info['store_id']);
        if(empty($this->store_info)) {
            output_error('店铺不存在',array(), ERROR_CODE_AUTH);
        }
        if ($this->store_info['store_type'] != 4)
        {
            output_error('店铺不存在',array(), ERROR_CODE_AUTH);
        }
        if($this->store_info['store_state'] !=1){
            output_error('店铺已经被封，请与管理员联系',array(),ERROR_CODE_OPERATE);
        }

        
    }
}



