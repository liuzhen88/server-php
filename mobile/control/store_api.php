<?php
//error_reporting(E_ALL);
defined('emall') or exit('Access Invalid!');
class store_apiControl extends BaseSellerControl{
  /*
  在父类baseSeller的构造方法中，已经对token 进行了一次验证
   */
  public function __construct() {
      parent::__construct();
    }
    /*
     获取店铺的店铺粉丝信息
     @param store_id
     @return array
     @author :xuping
     @time :2015年8月3日19:53:04
     */
     public function getstoreFansOP(){
       $store_id=intval($_REQUEST['store_id']);
      if(empty($store_id)){
        output_error('参数为空',array(), ERROR_CODE_OPERATE);
      }
      $favorites_model = Model('favorites');
      $size=10;
      $page =isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1 ;
      $respage=($page-1)*$size;
      $favorites_info_count=$favorites_model->where(array('fav_id'=>$store_id , 'fav_type'=>'store'))->select();
      $favorites_info=$favorites_model->where(array('fav_id'=>$store_id , 'fav_type'=>'store'))->limit("$respage , $size ")->select();
      $favorites_conut=count($favorites_info_count);
      $endtime=strtotime(date('Y-m-d'));
      $starttime=$endtime-24*60*60;
      if(!empty($favorites_info)){
        $member=Model('member');
        $res=array();
        $i=0;
        foreach ($favorites_info as $key => $value) {
          $res[$i]=$member->where(array('member_id'=> $value['member_id']))->field('member_id , member_truename , member_mobile ,member_avatar ')->find();
          $favorites_info[$key]['member_name']=empty($res[$i]['member_truename']) ? '' : $res[$i]['member_truename'];
          $favorites_info[$key]['member_mobile']=empty($res[$i]['member_mobile']) ? '' : $res[$i]['member_mobile'];
          $favorites_info[$key]['member_avatar']=getMemberAvatarForID($res[$i]['member_id']);  //getMemberAvatarForID($_SESSION['member_id']);
          $favorites_info[$key]['add_time']=$favorites_info[$key]['fav_time'];
        }
       $sql="SELECT * FROM agg_favorites WHERE  `fav_id`=$store_id and `fav_type`='store' and `fav_time` between '$starttime'  and '$endtime' ";
        $info=Model()->query($sql);
        $count=count($info);
         echo json_encode(array('code'=>200 , 'message'=>'ok' , 'count'=>$favorites_conut,'newCount'=>$count,'data'=>$favorites_info ));
         exit;
       }else{
         output_error('没有查询到数据',array(), ERROR_CODE_OPERATE);
       }
     }
  /*
   to: 获取商户的积分明细
   @param store_id
   @return array
   @author xuping
   @time 2015年8月4日09:19:26
   */
   public function getInterdetailOP(){
    $store_id=intval($_REQUEST['store_id']);
    $page=isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
    $size=10;
    $res=($page-1)*$size;
     if(empty($store_id)){
       output_error('参数为空',array(), ERROR_CODE_OPERATE);
    }
    $inter=Model()->table('member')->where(array('member_id'=>$this->store_info['member_id']))->field('available_predeposit')->find();
    $result=Model()->table('rebate_records')->where(array('user_type'=>4 , 'store_id'=>$store_id))->order('add_time desc')->limit("$res , $size ")->select();
     if(!empty($result)){
      echo json_encode(array('code'=>200 , 'message'=> 'ok', 'data'=>$result,'available_predeposit'=>$inter['available_predeposit']));
      exit;
     }else{
      echo json_encode(array('code'=>200 , 'message'=> 'ok', 'data'=>array(),'available_predeposit'=>$inter['available_predeposit']));
      exit;
    }
   }
  /*
   to:获取预约商户的记录
   @param  store_id
   @return array
   @author xuping
   @time 2015年8月4日14:06:28
   */
   public function getReserveOP(){
     $store_id=$_REQUEST['store_id'];
   /*对用户的身份验证， token 验证已经通过, 根据在token中的的用户信息，得到token的用户id，根据用户id 在卖家中这个这个店铺id，与用户所传递的store_id所对比，达到token验证作用 */
     if(empty($store_id)){
       output_error('参数为空',array(), ERROR_CODE_OPERATE);
     }
     /*
      $store_info['store_avatar']= getStoreLogo($store_info['store_avatar'],'store_avatar') ;//头像
      $store_info['store_label']= getStoreLogo($store_info['store_label'],'store_logo');//logo
      $store_info['store_banner']= getStoreLogo($store_info['store_banner'],'store_logo');//横幅
      */
     $page=isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
     $size=10;
     $res=($page-1)*$size;
     $res=Model()->table('good_reserves')->where(array('store_id'=>$store_id ,'status'=>0))->order('create_time desc')->limit(" $res , $size ")->select();
     //获取用户用户图像 和商品图像
     $member=Model('member');
     $goods=Model('goods');
     foreach ($res as $key => $value) {
         $result=$member->where(array('member_id'=>$value['member_id']))->field('member_avatar , member_name')->find();
         $res[$key]['member_avatar']=getMemberAvatarForID($value['member_id']);
         $res[$key]['store_avatar']=getStoreLogo($this->store_info['store_avatar'],'store_avatar');
         $res[$key]['user_name']=$result['member_name'];
         if(!empty($value['good_image'])){
          $res[$key]['goods_image']=cthumb($value['good_image'],$value['store_id']);
         }
     }
     if(!empty($res)){
      output_data($res);
     }else{
       output_error('没有查询到数据哦',array(), ERROR_CODE_OPERATE);
     }
   }
  /*
   to : 确实订单
   @parameter： 订单号, 商户在登录时候的token
   @author：徐萍
   @retuan code
   */
    public function confirmOrderOP(){
     $order_sn=intval($_REQUEST['order_sn']);
     $store_id=intval($_REQUEST['store_id']);
       if(empty($order_sn) || empty($store_id)){
        output_error('订单号或者店铺号参数为空',array(), ERROR_CODE_OPERATE);
     }
      /*对用户的身份验证， token 验证已经通过, 根据在token中的的用户信息，得到token的用户id，根据用户id 在卖家中这个这个店铺id，与用户所传递的store_id所对比，达到token验证作用 */
       $sellerinfo=Model()->table('seller')->where(array('member_id'=>$this->member_info['member_id']))->find();
       if($sellerinfo['store_id'] !=$store_id){
          output_error('key不合法',array(), ERROR_CODE_OPERATE);
       }
       $result=Model()->table('order')->where(array('order_sn'=>$order_sn , 'store_id'=>$store_id))->find();
       if(!empty($result) &&  $result['order_state'] != 20 ){
         $arr=array('order_state'=>40);
         $res=Model()->table('order')->where(array('order_sn'=>$order_sn))->update($arr);
         if(!empty($res)){
            output_data('');
         }else{
           output_error('订单确认失败',array(), ERROR_CODE_OPERATE);
         }
       }else{
           output_error('订单信息有误',array(), ERROR_CODE_OPERATE);
       }
   }
  /*
  返回商户的积分，id，商户名称，和商户的邀请码   商家为确认预约的数量
  */
   public function getStoreInfoOP(){
     $store_id=intval($_REQUEST['store_id']);
      if(empty($store_id)){
      output_error('store_id参数为空',array(), ERROR_CODE_OPERATE);
    }

   $arr=array();
   $arr['member_points'] =$this->admin_member_info['available_predeposit']; //商户积分
   $arr['invitation']    =$this->admin_member_info['invitation'];  //商户邀请码
   $arr['member_avatar'] =getMemberAvatarForID($this->member_info['member_id']);
   $arr['store_avatar']  =getStoreLogo($this->store_info['store_avatar'],'store_avatar');
   $count=Model()->table('good_reserves')->where(array('store_id'=>$this->store_info['store_id'],'status'=>0))->select();
   $num=count($count);
   $result=Model()->table('seller')->where(array('member_id'=>$this->member_info['member_id'] ))->find();
   if(!empty($result)){
   $arr['store_name']=$result['seller_name'];
   $arr['reserve_num']=$num;
     output_data($arr);
  }else{
    output_error('操作有误！',array(), ERROR_CODE_OPERATE);
  }
 }
    /*
     新增积分apii
     author:xuping
     data:2015年8月6日12:08:20
     tool:sublime
     */
     public function getinterlogOP(){
       $store_id=intval($_REQUEST['store_id']);
       if(empty($store_id)){
       	 output_error('参数为空',array(), ERROR_CODE_OPERATE);
       }
       $page=isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
       $size=10;
       $res=intval(($page-1)*$size);
       $member_id=$this->admin_member_info['member_id'];
       $inter=Model()->table('member')->where(array('member_id'=>$this->admin_member_info['member_id']))->field('available_predeposit')->find();
       $result=Model()->table('pd_recharge')->where(array('pdr_member_id'=>$member_id , 'pdr_payment_state'=>'1'))->order('pdr_payment_time desc')->limit("$res,$size")->select();
       if(!empty($result)){
            foreach ((array)$result as $key => $value) {
            	 if (floatval($value['pdr_amount']) != 0 ) {
                    $result[$key]['pdr_amount'] = '+'.$value['pdr_amount'];
                }
            }
         echo json_encode(array('code'=>200 , 'message'=>'ok' , 'data'=>$result, 'available_predeposit'=>$inter['available_predeposit']));
         exit;
      }else{
         echo json_encode(array('code'=>200 , 'message'=>'没有数据' , 'data'=>array(), 'available_predeposit'=>$inter['available_predeposit']));
         exit;
       }
    }


    public function new_getinterlogOP(){
        $store_id=intval($_REQUEST['store_id']);
        if(empty($store_id)){
            output_error('参数为空',array(), ERROR_CODE_OPERATE);
        }
        $page=isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
        $size=10;
        $res=intval(($page-1)*$size);
        $member_id=$this->admin_member_info['member_id'];

        $inter=Model()->table('member')->where(array('member_id'=>$this->admin_member_info['member_id']))->field('available_predeposit')->find();

        $result=Model()->table('pd_log')->where(array('lg_member_id'=>$member_id ,'lg_type'=>array('in','recharge,settle_account')))->field('lg_member_id,lg_member_name,lg_type,lg_av_amount,lg_add_time,lg_desc')->order('lg_add_time desc')->limit("$res,$size")->select();
        if(!empty($result)){
            foreach ((array)$result as $key => $value) {
                if ($value['lg_av_amount'] !=0 ) {
                    $result[$key]['lg_av_amount'] = '+'.($value['lg_av_amount']);
                }
                $result[$key]['lg_desc']=str_replace('结算','消费',$value['lg_desc']);
            }
            echo json_encode(array('code'=>200 , 'message'=>'ok' , 'data'=>$result, 'available_predeposit'=>$inter['available_predeposit']));
            exit;
        }else{
            echo json_encode(array('code'=>200 , 'message'=>'没有数据' , 'data'=>array(), 'available_predeposit'=>$inter['available_predeposit']));
            exit;
        }
    }


    /* 确认预约 api*/
    public function confirmReserveOP(){
      $good_id     =intval($_REQUEST['goods_id']);
      $reserve_id  =intval($_REQUEST['reserve_id']);
      if(empty($good_id ) || empty($reserve_id)){
        output_error('参数为空',array(), ERROR_CODE_OPERATE);
       }
     $res=Model()->table('good_reserves')->where(array('goods_id'=>$good_id  , 'id'=>$reserve_id))->find();
       if(!empty($res) && $res['status']==0)   //确实存在，同时 是未确认状态
        {
          $data=array(
          'status'=>1 ,
          'id'=>$reserve_id
         );
         $info=Model()->table('good_reserves')->update($data);
         if(!empty($info)){
          echo json_encode(array('code'=>200,'message'=>'确认成功','data'=>array()));
             exit;
         }else{
           output_error('操作失败',array(), ERROR_CODE_OPERATE);
           }
       }else{
           output_error('没有查询到数据哦',array(), ERROR_CODE_OPERATE);
       }
  }
   /**
   * @author chenyifei
   * 商户端本土订单确认
   */
  public function local_order_sureOp()
  {
      $order_sn = isset($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : ''; //订单编号
      if (empty($order_sn))
      {
          output_error('参数错误');
      }
      $model_order = Model('order');
      $order_info = $model_order->getOrderInfo(array('order_sn'=>$order_sn, 'order_type'=>1), array('order_goods'));
      if (empty($order_info))
          output_error('订单不存在');
      if ($order_info['order_state'] != ORDER_STATE_NEW)
      {
          if ($order_info['order_state'] == ORDER_STATE_CANCEL)
          {
              output_error('订单已取消');
          }
          elseif ($order_info['order_state'] == ORDER_STATE_SUCCESS)
          {
              output_error('订单已确认');
          }
          elseif ($order_info['order_state'] == ORDER_AUTO_CANCEL_DAY)
          {
              output_error('订单已关闭');
          }
          else
          {
              output_error('订单不存在');
          }
      }
      if ($order_info['store_id'] !=0 && $order_info['store_id'] != $this->store_info['store_id'])
      {
          output_error('此订单非本商户订单');
      }
      $member_info = Model('member')->getMemberInfo(array('member_id'=>$order_info['buyer_id']));
      if (empty($member_info))
          output_error('买家不存在');
      $buy_log = Logic('buy');
      $result = $buy_log->localOrderSure($order_info, $this->store_info, $this->seller_info, $member_info);
      if ($result['state'] == true)
      {
          //极光消息推送
          $buy_log->sellerLocalSureJgPush($order_info, $this->store_info);
          output_data('ok');
      }
      else
      {
          output_error($result['msg']);
      }
  }
  
  /**
   * 商户消费码确认订单
   */
    public function local_order_consume_code_sureOp(){
        $order_sn = isset($_REQUEST['consume_code']) ? trim($_REQUEST['consume_code']) : ''; //消费码，本土预售订单确认码
        if (empty($order_sn))
        {
            output_error('参数错误');
        }
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('consume_code'=>$order_sn, 'order_type'=>1), array('order_goods'));
        if (empty($order_info))
            output_error('订单不存在');
        $buy_log = Logic('buy');
        $result = $buy_log->localOrderConsumeCodeSure($order_sn, $this->store_info['store_id'], $this->seller_info['seller_name']);
        if ($result['state'] == true)
        {
            //极光消息推送
            $buy_log->sellerLocalSureJgPush($order_info, $this->store_info);
            output_data('订单确认成功');
        }
        else
        {
            output_error($result['msg']);
        }
    }  
  
     public function getsureReserveOP(){
     $store_id=$_REQUEST['store_id'];
     if(empty($store_id)){
        output_error('参数为空',array(), ERROR_CODE_OPERATE);
     }
     $page=isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
     $size=10;
     $res=($page-1)*$size;
     $res=Model()->table('good_reserves')->where(array('store_id'=>$store_id, 'status'=>1))->order('create_time desc')->limit(" $res , $size ")->select();
      //获取用户用户图像 和商品图像
     $member=Model('member');
     $goods=Model('goods');
     foreach ($res as $key => $value) {
         $result=$member->where(array('member_id'=>$value['member_id']))->field('member_avatar , member_name')->find();
           $res[$key]['member_avatar']=getMemberAvatarForID($value['member_id']);
           $res[$key]['store_avatar']=getStoreLogo($this->store_info['store_avatar'],'store_avatar');
           $res[$key]['user_name']=$result['member_name'];
          if(!empty($value['good_image'])){
             $res[$key]['goods_image']=cthumb($value['good_image'],$value['store_id']);
         }
     }
     if(!empty($res)){
        output_data($res);
     }else{
        output_error('没有查询到数据哦',array(), ERROR_CODE_OPERATE);
     }
   }
   /*商户登录 点击协议正确更改*/
   public function isAgreeOP(){
      $store_id=$_REQUEST['store_id'];
      if(empty($store_id)){
         output_error('store_id参数为空');
      }
      $result=Model()->table('store')->where(array('store_id'=>$store_id))->find();
      if($result['is_agree'] ==0){
        $data=array(
          'is_agree'=>1,
          'store_id'=>$store_id
          );
         $res=Model()->table('store')->update($data);
        if($res){
          output_data('ok');
        }else{
          output_error('操作失败',array(),ERROR_CODE_OPERATE);
        }
      }else{
        output_error('操作失败',array(),ERROR_CODE_OPERATE);
      }
   }
  /*app 商户里面的订单记录
  已完成，待付款，已取消
   xuping*/
  public function getorderinfoOP(){
      $store_id=$this->store_info['store_id'];
      $order_type=isset($_REQUEST['order_type']) ? $_REQUEST['order_type'] : 'finished';
      $page=isset($_REQUEST['curpage']) ?  $_REQUEST['curpage'] :1;
      $size=10;
      $rsize=(intval($page)-1)*$size;
      if($order_type=='finished'){
           $condition['order_state']=40;
      }elseif($order_type=='wait_pay'){
           $condition['order_state']=10;
      }elseif($order_type=='canceled'){
           $condition['order_state']=0;
      }elseif($order_type=='unused'){
          $condition['order_state']=20;
      }
      $condition['order_type']=1;
      $condition['store_id']=$store_id;
      $fields='order_sn,add_time,order_amount,evaluation_state,goods_type';

      $order=Model()->table('order')->where($condition)->field($fields)->limit("$rsize , $size")->order('add_time desc')->select();
      // $order=Model('order')->getOrderList($condition,$pagesize=10,$fields);
      foreach($order as $key=>$value){
            if($value['goods_type']==0){
                $order[$key]['evaluation_state']='2';
            }
      }
      if($order){
          output_data($order);
      }else{
          output_data(array());
      }
  }


/**
     * 订单详情(支付之后返回)
     * 
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     * @param  float  $param['location']          地理位置 格式如117.281456,31.868228
     */
    public function order_detailOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        //} else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
           // $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        
        //if (!preg_match('/^\d{1,3}\.\d+,\d{1,3}.\d+$/', $_REQUEST['location'])) {
         //   output_error('地理位置格式错误', array(), ERROR_CODE_ARG);
        //}

        //$condition['buyer_id']     = $this->member_info['member_id'];
        //$condition['order_sn']  = $_REQUEST['order_sn'];
        $order['invitation']   = $this->admin_member_info['invitation'];
        // 微信支付、支付宝支付，需要做个校验 (client_type)
        //if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
        //    $condition['order_state']  = array('gt', ORDER_STATE_NEW); //ORDER_STATE_NEW已付款的都可以查询 大于0未付款
       // }
        
        $model_order = Model('order');
        $model_goods = Model('goods');
        $model_goods_class = Model('goods_class');
        $order_info = $model_order->getOrderInfo($condition, array('store'));

        $rt = Model('member')->getMemberInfo(array('member_id'=>$order_info['extend_store']['member_id']));
        $store['store_invitation'] = $rt['invitation'];
        if (empty($order_info)) {
            output_error('订单信息不存在');
        }
        // 订单信息
        
        $order['order_id']       = $order_info['order_id']; // 订单ID
        $order['order_sn']       = $order_info['order_sn']; // 订单号
        $order['pay_sn']         = $order_info['pay_sn']; // 付款单号
        $order['payment_code']   = $order_info['payment_code']; // 付款方式
        $order['payment_time']   = date('Y-m-d H:i', $order_info['add_time']); // 消费时间
        $order['order_amount']   = price_format($order_info['order_amount']);  // 消费总额
        $order['goods_amount']   = price_format($order_info['goods_amount']);  // 商品总额
        $order['pd_amount']      = price_format($order_info['pd_amount']);  // 抵用预存款(积分)
        $order['realpay_amount'] = price_format($order_info['order_amount'] - $order_info['pd_amount']); //实际支付
        //$order['order_state']    = $order_info['order_state']; //0(已取消)10(默认):未付款认;20:已付款;30:已发货;40:已收货/o2o已完成;
        //$order['evaluation_state'] = $order_info['evaluation_state']; //0未评价，1已评价，2已过期未评价
        //已完成已评价42, 已完成未评价 41
        if($order_info['order_state']==40 && $order_info['goods_type'] == 0) {
            if(isset($_REQUEST['ver_code']) && $_REQUEST['ver_code']<32) { 
                $order['order_state'] = 42;
            }else{
                if($order_info['evaluation_state'] == 0)
                    $order['order_state'] = 41;
                else
                    $order['order_state'] = 42;
            }
        }
        elseif($order_info['order_state']==40 && $order_info['evaluation_state'] == 0 && $order_info['goods_type'] == 1)
            $order['order_state'] = 41;
        elseif($order_info['order_state']==40 && $order_info['evaluation_state'] == 1 && $order_info['goods_type'] == 1)
            $order['order_state'] = 42;
        else
            $order['order_state']    = $order_info['order_state'];
        //本土 无商品 小于1元返回成功 
        if($order_info['order_amount'] < 1 && $order_info['order_type'] == 1 && $order_info['goods_type'] == 0 && $order_info['order_state']==40) {
            $order['order_state'] = 42;
        }
        $order['goods_type']     = $order_info['goods_type'];   
        $order['consume_code']   = $order_info['consume_code'];

        // 买家信息
        $order['buyer_id']       = $order_info['buyer_id']; // 买家ID
        $order['buyer_name']     = $order_info['buyer_name']; // 买家姓名

        // 本土商家要显示的东西
        if ($order_info['order_type'] == 1) {

            if($order_info['order_state']== 0 && $order_info['refund_state'] == 2)
                $order['order_state']    =  51;
            // 商品ID
            $order_goods_detail = $model_order->getOrderGoodsInfo(array('order_id'=>$order_info['order_id']), '*'); //获取订单商品表
            $order['goods_id'] = $order_goods_detail['goods_id'];
            $goods = $model_goods->getGoodsInfo(array('goods_id'=>$order_goods_detail['goods_id']),'goods_id,goods_price,goods_marketprice,validate_time,goods_image,gc_id,evaluation_good_star,goods_salenum');

            //本土预售
            if ($order_info['goods_type'] == 1) {
                $order['goods_image']       =   cthumb($goods['goods_image'], 60, $goods['store_id']);
                $order['goods_name']        =   $order_goods_detail['goods_name'];
                $order['goods_num']         =   $order_goods_detail['goods_num'];
                $order['validate_time']     =   ($goods['validate_time']?$goods['validate_time']:0);
                $order['goods_price']       =   ($goods['goods_price']?$goods['goods_price']:$order_goods_detail['goods_pay_price']);  
                $order['goods_marketprice'] =   ($goods['goods_marketprice']?$goods['goods_marketprice']:$order_goods_detail['goods_price']);
                $order['goods_count']       =   (int)$goods['goods_salenum'];//$model_order->getOrderGoodsCount(array('goods_id'=>$order['goods_id']));
                //$order['goods_credit']      =   ($goods['evaluation_good_star']?(string)$goods['evaluation_good_star']:"0");

                $eva_where                  =   array('geval_ordergoodsid' =>$order_goods_detail['rec_id'] , 'geval_frommemberid'=>$order_info['buyer_id']);
                $o                          =   Model('evaluate_goods')->getEvaluateGoodsInfo($eva_where);
                $order['goods_credit']      =   ($o['geval_scores']?(float)$o['geval_scores']:"0");
            }

            // 返利金额
            $rebate_condition = array(
                'order_id'    => $order_info['order_id'],
                'member_id'  => $order_info['buyer_id'],
                'user_type'   => 1, 
            );
            
            if($order_info['goods_type'] == 0){
                $commis_rate = round(($order_info['commis_rate']*$order_info['order_amount'])/100,2);
                $order['rebate'] = price_format(round(($commis_rate*REBATE_BUY_USER)/100,2));
            }else{
                //$rebate_records_detail = Model('rebate_records')->getRebateRecordsInfo($rebate_condition);
                //$order['rebate'] = empty($rebate_records_detail) ? '0.00' : price_format($rebate_records_detail['rebate']); //返利金额
                $goods_class_rebate = $model_order->getOrderGoodsInfo(array('order_id'=>$order_info['order_id']));
                $commis_rate = round(($goods_class_rebate['commis_rate']*$goods_class_rebate['goods_pay_price'])/100,2);
                $order['rebate'] = price_format(round(($commis_rate*REBATE_BUY_USER)/100,2));
            }
            
        } 
        
        // 商家信息
        $store['store_id']      = $order_info['store_id'];
        $store['store_name']    = $order_info['extend_store']['store_name'];
        $store['area_info']     = $order_info['extend_store']['area_info'] . $order_info['extend_store']['store_address'];
        $store['mobile']        = $order_info['extend_store']['store_phone'];
        $store['store_credit']  = $order_info['extend_store']['store_credit_old'];//$order_info['extend_store']['store_credit_average'];
        $store['store_avatar']  = getStoreLogo($order_info['extend_store']['store_avatar']);
        $store['district_name'] = $order_info['extend_store']['district_name'];
        $store['evaluate_count']= Model('evaluate_goods')->getStoreCredit(array('geval_storeid' =>$order_info['store_id']));
        //$store['goods_count']   = $order_info['extend_store']['goods_count'];
        $storec  = Model('store_bind_class')->getStoreBindClassInfo(array('store_id'=>$store['store_id']));
        $storet   = $model_goods_class->getGoodsClassName(array('gc_id'=>$storec['class_1']));
        $store['store_type_name']   =   $storet['gc_name'];

        //$order_info_goods['extend_order_goods'][0]['goods_image'] = cthumb($order_info_goods['extend_order_goods'][0]['goods_image'], 60, $order_info_goods['extend_order_goods'][0]['store_id']);
        //$store_location         = $order_info['extend_store']['lng'] . ',' .  $order_info['extend_store']['lat'];
        //$location               = $_REQUEST['location'];
       // $store['distance']      = get_distance($location, $store_location) ;
        //$store['distance']      = $store['distance'] > 1000 ? round($store['distance']/1000) . 'km' : $store['distance'] . 'm';
        
        $recipient = Model('order_log')->where(array('order_id' => $order_info['order_id'],'log_orderstate'=>40 ))->find();
        $order['recipient'] = (string)$recipient['log_user'];
        //$order['goods_salenum']  = (int)$goods['goods_salenum'];
        output_data(array('order' => $order, 'store' => $store));
    }

/*
 *  商户中心， xuping
 * 一级分销商 获取饼状图接口
 *
 * */

public  function  store_sale_statisOp()
{
    $time_type = isset($_REQUEST['time_type']) ? $_REQUEST['time_type'] : 'week';
    //$time_type='week';
    $arr = Array();
    //销售业绩  日 月 年 统计
    switch ($time_type) {
        case 'week':
            $temp = array_fill(0, 7, '0.00');
            $field = ' di_totals as money ,di_other_totals,di_self_totals';
            $where = ' di_day>=' . date("Ymd", strtotime('now -6 day')) . ' AND di_day<=' . date("Ymd", strtotime('now'));
            $group = ' di_day ';
            $start_day = date("m月d号", strtotime('d -6 day'));
            $end_day = date('m月d号');
            break;
        case 'month':
            $temp = array_fill(0, 15, '0.00');
            $field = ' di_totals as money ,di_other_totals,di_self_totals ';
            $where = ' di_day>=' . date("Ymd", strtotime('now -29 day')) . ' AND di_day<=' . date("Ymd", strtotime('now'));
            $group = ' di_day ';
            $start_day = date("m月d号", strtotime('d -29 day'));
            $end_day = date('m月d号');
            break;
        case 'year':
            $temp = array_fill(0, 6, '0.00');
            $field = ' di_totals as money ,di_other_totals,di_self_totals ';
            $where = '  di_day>=' . date("Ymd", strtotime('now -5 month')) . ' AND di_day<=' . date("Ymd", strtotime('now'));
            $group = '  di_month ';
            $start_day = date("Y年m月", strtotime('now -5 month'));
            $end_day = date('Y年m月');
            break;
    }

    $store_id = $this->store_info['store_id'];
    $whereAdd = " and store_id =$store_id";
    $order = ' di_day';
    $data_list = Model('distribution_statis')->field($field)->where($where . $whereAdd)->group($group)->order($order)->select();
//二位数组编程一维数组
    $total_money = 0;
    $other_money = 0;
    $self_money = 0;
    foreach ($data_list as $key => $value) {
        $total_money += $value['money'];
        $other_money += $value['di_other_totals'];
        $self_money += $value['di_self_totals'];
    }
    $data['total_money'] = $total_money;
    $data['self_money'] = sprintf("%.2f", $self_money / $total_money);
    $data['other_money'] = sprintf("%.2f", $other_money / $total_money);
//$data['per_money']= array_reverse($temp);
    if ($data) {
        output_data($data);
    } else {
        output_data(array());
    }
 }

    //验证消费码接口
    //xuping
    public function checkoutConsume_codeOP(){
        $consume_code=intval($_REQUEST['consume_code']);
        if(!$consume_code){
            output_error('参数不完整');
        }
        $order=Model('order');
        $condition['consume_code']=$consume_code;
        $condition['store_id']    =$this->store_info['store_id'];
        $result=$order->getOrderInfo($condition);
        if($result && $result['order_state']==20){
            output_data('确认码验证成功！');
        }else{
            output_error('验证失败');
        }
    }
    // 验证订单号接口
    // xuping
    public function checkout_ordersnOP(){
        $order_sn=intval($_REQUEST['order_sn']);
        if(!$order_sn){
            output_error('参数不完整');
        }
        $order=Model('order');
        $condition['order_sn']=$order_sn;
        $condition['store_id']    =$this->store_info['store_id'];
        $result=$order->getOrderInfo($condition);
        if($result && $result['order_state']==ORDER_STATE_NEW){
            output_data('确认码验证成功！');
        }else{
            output_error('验证失败');
        }
    }
/**
 * 2.1.6
 * 商户积分变更列表
 */
    public function get_seller_predeposit_listOP(){
        $member_id = $this->store_info['member_id'];
        $curpage = 1;
        if(isset($_REQUEST['curpage'])&&!empty($_REQUEST['curpage'])) {
            $curpage = $_REQUEST['curpage'];
        }
        $per_page = 20;//每页20条记录
        $start = ($curpage-1)*$per_page;
        $condition_arr = array();
        $model_pd      = Model('predeposit');
        $condition_arr['lg_member_id'] = $member_id;
        //获取总页数
        $count = $model_pd->getPdLogCount($condition_arr);
        $pd_list = $model_pd->getPdLogList($condition_arr, '', '*', 'lg_add_time desc',$start.',20');
        if (!empty($pd_list)) {
            for($i=0;$i<count($pd_list);$i++) {
                $pd_list[$i]['lg_type_tip']      = $this->_get_order_pay_type($pd_list[$i]['lg_type']);
                // 积分明细金额
                if (floatval($pd_list[$i]['lg_av_amount']) != 0 ) {
                    $pd_list[$i]['lg_av_amount'] = $pd_list[$i]['lg_av_amount'] > 0 ? '+' . $pd_list[$i]['lg_av_amount'] : $pd_list[$i]['lg_av_amount'];
                } else {
                    $pd_list[$i]['lg_av_amount'] = $pd_list[$i]['lg_freeze_amount'] > 0 ? '+' . $pd_list[$i]['lg_freeze_amount'] : $pd_list[$i]['lg_freeze_amount'];
                }
                //流水号生成
                $code_ori = "90000000000";
                $serial_number = substr($code_ori,0,11-strlen($pd_list[$i]['lg_id'])).$pd_list[$i]['lg_id'];
                $pd_list[$i]['serial_number'] = $serial_number;
                $pd_list[$i]['order_sn'] = '';
                $pd_list[$i]['details'] = array();
                //详情
                if(!empty($pd_list[$i]['lg_mark'])) {
                    $lg_mark = json_decode($pd_list[$i]['lg_mark'],true);
                    if(!empty($lg_mark['order_sn'])) {
                        $pd_list[$i]['order_sn'] = $lg_mark['order_sn'];
                    }
                    if(isset($lg_mark['pd_amount'])) {
                        $j = 0;
                        if(!empty($lg_mark['pd_amount'])) {
                            $pd_list[$i]['details'][$j]['pay_type'] = '积分支付';
                            $pd_list[$i]['details'][$j]['money'] = '+'.$lg_mark['pd_amount'];
                            $j++;
                            if($lg_mark['order_amount']-$lg_mark['pd_amount']!=0) {
                                $pd_list[$i]['details'][$j]['pay_type'] = orderPaymentName($lg_mark['payment_code']);
                                $pd_list[$i]['details'][$j]['money'] = '+'.($lg_mark['order_amount']-$lg_mark['pd_amount']);
                                $j++;
                            }
                        }
                        else {
                            $pd_list[$i]['details'][$j]['pay_type'] = orderPaymentName($lg_mark['payment_code']);
                            $pd_list[$i]['details'][$j]['money'] = '+'.$lg_mark['order_amount'];
                            $j++;
                        }
                        if(!empty($lg_mark['rebate_money'])) {
                            $pd_list[$i]['details'][$j]['pay_type'] = '消费返利';
                            $pd_list[$i]['details'][$j]['money'] = '-'.$lg_mark['rebate_money'];
                            $j++;
                        }
                        if(!empty($lg_mark['distribution_money'])) {
                            $pd_list[$i]['details'][$j]['pay_type'] = '分销返利';
                            $pd_list[$i]['details'][$j]['money'] = '-'.$lg_mark['distribution_money'];
                        }
                    }
                }
                unset($pd_list[$i]['lg_mark']);
            }
        }
        output_data($pd_list, mobile_page(ceil($count/$per_page)));
    }
    private function _get_order_pay_type($key) {

        $type_arr =  array(
            'order_pay'       => '购买支出',
            'order_freeze'    => '购买支出',
            'order_cancel'    => '订单退款',
            'order_comb_pay'  => '购买支出',
            'recharge'        => '账户充值',
            'cash_apply'      => '提现',
            'cash_pay'        => '提现',
            'cash_del'        => '提现失败',
            'refund'          => '订单退款',
            'rebate_pay'      => '商品销售',
            'rebate_get'      => '人脉返佣',
            'settle_account'  => '商品销售',
            'distribution_get'=> '分销返佣'
        );

        return $type_arr[$key];
    }
}
