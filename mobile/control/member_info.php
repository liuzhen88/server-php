   <?php
   //use Tpl;
    //error_reporting(E_ALL);
    defined('emall') or exit('Access Invalid!');
    /**
     * 此文件接口早期开发，作废 后期要删除
     * 
     */
    class member_infoControl extends mobileHomeControl
    {

        public function __construct()
        {

            parent::__construct();

        }


        /*手机端可 直接获取用户信息*/
        public function getUserInfoOP()
        {

            $id = intval($_REQUEST['user_id']);
            if (!empty($id)) {
                $member = Model('member');
                $info = $member->find($id);
                if ($info) {
                    $arr = array('code' => 200, 'message' => 'OK', 'data'=>$info);
                    echo json_encode($arr);
                    exit;
                } else {
                    $arr = array('code' => ERROR_CODE_AUTH, 'message' => '对不起，没有此人信息', 'data'=>'');
                    echo json_encode($arr);
                }

            } else {
                $arr = array('code' => ERROR_CODE_OPERATE, 'message' => '对不起，参数有误！', 'data'=>'');
                echo json_encode($arr);
                exit;
            }
        }


        /*修改昵称  http://121.40.151.125:8086/ageg-web-business/ws/updateHumanByApp?id=@& human_name=@  修改昵称  */
        public function updataNicknameOP()
        {

            $id = intval($_REQUEST['user_id']);
            $nickname = trim($_REQUEST['nickname']);
            if (!empty($id) && !empty($nickname)) {
                $data = array('member_name' => $nickname);
                $where = array('member_id' => $id);
                $member = Model('member');
                $res = $member->find($id);
                if (empty($res)) {
                    echo json_encode(array('code' => ERROR_CODE_AUTH, 'message' => '对不起，该用户不存在！','data'=>''));
                    exit;
                }
                $result = $member->where($where)->update($data);

                /*更新成功以后返回*/
                if ($result) {
                    echo json_encode(array('code' => 200, 'message' => '更新昵称成功！','data'=>''));
                    exit;
                } else {
                    echo json_encode(array('code' => ERROR_CODE_OPERATE, 'message' => '对不起，更改失败','data'=>''));
                    exit;
                }
                /*$model->table('link')->where(array('link_id'=>37))->update($data);*/
            } else {
                $arr = array('code' =>80003, 'message' => '参数不完整','data'=>'');
                echo json_encode($arr);
                exit;
            }
        }


        /*查找商户 接口  http://121.40.151.125:8086/ageg-web-business/sys/getCompanies?name=@&page=1 查找商户  模糊查询*/
        public function getCompaniesOP()
        {

            $name = trim($_REQUEST['store_name']);
            if (!empty($name)) {
                //进行模糊查找商户
                $store = Model('store');
                $condition['store_name'] = array('like', '%' . $name . '%');
                $result = $store->where($condition)->select();
                if ($result) {
                    echo json_encode(array('code' => 200, 'message'=>'OK' ,'data'=> $result));
                    exit;
                } else {
                    echo json_encode(array('code' => ERROR_CODE_ARG, 'message' => '没有查询到结果！','data'=>''));
                    exit;
                }
            } else {
                echo json_encode(array('code' => ERROR_CODE_AUTH, 'message' => '参数不完整！','data'=>''));
                exit;
            }
        }


        /* 删除订单 接口  http://121.40.151.125:8086/ageg-web-business/ws/indent/deleteOrder?number=@ 删除订单*/

        public function deleOderOP()
        {
            $number = intval($_REQUEST['number']);
            $order = Model('order');


            $good_order = Model('order_goods');

            $res = $good_order->where(array('order_id' => $number))->delete();
           $res1=Model('order')->where(array('order_id'=> $number ))->delete();

            if ($res & $res1) {
                echo json_encode(array('code' => 200, 'message' => '删除订单成功','data'=>array()));
                exit;
            } else {
                echo json_encode(array('code' => ERROR_CODE_AUTH, 'message' => '操作失败','data'=>array()));
                exit;
            }

        }

        /*http://121.40.151.125:8086/ageg-web-business/ws/deleteReserve?id=@  删除预约 */
        // public function deleteReserveOP()
        // {
        //     // $id = intval($_REQUEST['id']);
        //     // $model = Model('');
        // }


        /*http://121.40.151.125:8086/ageg-web-business/ws/addMessages?title=@&content=@&humanId=@意见反馈*/
        public function addMessagesOP()
        {

            $title = trim($_REQUEST['title']);
            $content = trim($_REQUEST['content']);
            $user_id = intval($_REQUEST['user_id']);
            if (!empty($title) && !empty($content) && !empty($user_id)) {
                $cms_comment = Model('cms_comment');
                $data = array(
                    'comment_type' => 1,
                    'comment_member_id' => $user_id,
                    'comment_time' => time(),
                    'comment_message' => $content
                );
                $res = $cms_comment->insert($data);
                if ($res) {
                    echo json_encode(array('code' => 200, 'message' => '添加成功！','data'=>''));
                    exit;
                } else {
                    echo json_encode(array('code' => ERROR_CODE_AUTH, 'message' => '添加失败，请重试！','data'=>''));
                    exit;
                }
            } else {
                echo json_encode(array('code' => ERROR_CODE_OPERATE, 'message' => '参数不完整！','data'=>''));
                exit;
            }
        }


        /*http://121.40.151.125:8086/ageg-web-business/ws/updateHumanByApp?id=@&images=@  上传头像*/
        public function uploadPicOP()
        {
            if (empty($_FILES)) {
                output_error('请先上传');
            }
            

            /*开始上传图片文件*/
            if ($_FILES['user_avatar']['error'] > 0) {
                echo 'Problem: ';
                switch ($_FILES['user_avatar']['error']) {
                    case 1:
                        echo 'File exceeded upload_max_filesize';
                        break;
                    case 2:
                        echo 'File exceeded max_file_size';
                        break;
                    case 3:
                        echo 'File only partially uploaded';
                        break;
                    case 4:
                        echo 'No file uploaded';
                        break;
                    case 6:
                        echo 'Cannot upload file: No temp directory specified.';
                        break;
                    case 7:
                        echo 'Upload failed: Cannot write to disk.';
                        break;
                }
                exit;
            }

            if ($_FILES['user_avatar']['type'] != 'image/jpeg' && $_FILES['user_avatar']['type'] != 'image/gif'
                && $_FILES['user_avatar']['type'] != 'image/png') {
                echo json_encode(array('status' => ERROR_CODE_ARG, 'message' => '对不起，文件格式有问题'));
                exit;
            }


            // if (!file_exists('upload')){
            //     mkdir ("upload",0777);
            //  }

            // put the file where we'd like it  avatar_94.jpg

            $file = 'avatar_' . $user_id . 'jpg';
            $upfile = "../../data/upload/shop/avatar/" . $file;

            if (is_uploaded_file($_FILES['user_avatar']['tmp_name'])) {
                if (!move_uploaded_file($_FILES['user_avatar']['tmp_name'], $upfile)) {
                    echo '对不起，移动文件失败';
                    exit;
                }
            } else {
                echo '上传失败！';
                exit;
            }
            $data = array(
                'member_avatar' => $file,
                'member_id' => $user_id
            );
            $result = Model('member')->update($data);
            if ($result) {
                echo json_encode(array('code' => 200, 'message'=>'OK', 'data' => $upfile));
                exit;
            }
            


        }

        /*http://121.40.151.125:8086/ageg-web-business/ws/getProducts? city_name=@& product_name=@  产品查找*/
        public function  getProductsOP(){
            $cityname    =$_REQUEST['city_name'];  //只传二级地点 id  $condition['uid'] = array('gt',5);
            $product_name=$_REQUEST['product_name'];
            if(!empty($product_name) & !empty($cityname)){
                $goods=Model('goods');
                $condition['goods_name'] = array('like', '%' . $product_name . '%');
                $condition['areaid_2']   = array('gt',$cityname);
                $result=$goods->where($condition)->select();
                if($result){
                        echo json_encode(array('code'=>200,'message'=>'OK','data'=>$result));
                        exit;
                }else{
                        echo json_encode(array('code'=>ERROR_CODE_AUTH,'message'=>'暂无查询结果！','data'=>''));
                        exit;
                }
            }else{
                echo json_encode(array('code'=>ERROR_CODE_OPERATE,'message'=>'对不起，参数不正确！','data'=>''));
                exit;
            }
        }

        /*//http://121.40.151.125:8086/ageg-web-business/ws/getCompanyInfo?user_id=parent_id  得到商户信息*/
        public function  getCompanyInfoOP(){
            if(empty($_POST)){
               echo json_encode(array('code'=>ERROR_CODE_DATABASE,'message'=>'参数不对','data'=>array()));
               exit;
            }
            $token=$_POST['token'];
            $user_token=Model('mb_user_token');
            $re=$user_token->where(array('token'=>$token))->find();
            if($re['login_time'] > time()- 60*5){
               echo json_encode(array('code'=>80003,'message'=>'请重新登录','data'=>array()));
               exit;
            }
            $shop_id=intval($_POST['shop_id']);
            $store=Model('store');
            $result=$store->find($shop_id);
            if($result){
                echo json_encode(array('code'=>200,'message'=>'OK','data'=>$result));
                exit;
            }else{
                echo json_encode(array('code'=>ERROR_CODE_ARG,'message'=>'对不起，暂无信息','data'=>''));
                exit;
            }
        }
    
         /*未确认订单与已确认订单 http://121.40.151.125:8086/ageg-web-business/ws/indent/getOrders?status=%@&human_id=%@&page=%@ */
          
          /* 用户未确认订单 或者用户已确认订单  根据status 的状态值不同*/
        public function getOrdersOP(){
            $status =$_REQUEST['status'];
            $user_id=$_REQUEST['user_id'];

            if(empty($status) || empty($user_id)){
               echo json_encode(array('code'=>ERROR_CODE_AUTH,'message'=>'参数不完整！','data'=>''));
               exit;
            }else{
                $order=Model('order');
                $condition['buyer_id']=$user_id;
                $condition['order_state']=$status;
                $result=$order->where($condition)->select();
                if(!empty($result)){
                    echo json_encode(array('code'=>200,'message'=>'OK','data'=>$result));
                    exit;
                }else{
                    echo json_encode(array('code'=>ERROR_CODE_OPERATE,'message'=>'对不起，暂时信息！','data'=>''));
                    exit;
                }
            }
        }

      
       //http://121.40.151.125:8086/ageg-web-business/ws/follow?humanId=%@&productId=%@ 关注商品
        public function followOP(){

             $user_id=$_REQUEST['user_id'];
             $product_id=$_REQUEST['product_id'];
             $store_id=$_REQUEST['store_id'];

             if(empty($user_id) || empty($product_id) || empty($store_id)){
                echo  json_encode(array('code'=>ERROR_CODE_AUTH,'message'=>'对不起，参数不正确','data'=>array()));
                exit;
             }else{
                  $favorites=Model('favorites');
                 $arr=array(
                        'member_id'=>$user_id,
                        'fav_id' =>$product_id,
                        'fav_type'=>'goods',
                        'fav_time'=>time(),
                        'store_member_id'=>$store_id,
                    );
                  $res=$favorites->insert($arr);
                 if($res){
                    echo json_encode(array('code'=>200, 'message'=>'关注商品成功','data'=>array()));
                    exit;
                 }else{
                    echo json_encode(array('code'=>ERROR_CODE_OPERATE,'message'=>'稍后重试','data'=>array()));
                    exit;
                 }
             }
        
        }
  
       //  //http://121.40.151.125:8086/ageg-web-business/ws/followed?humanId=23&productId=6 产品是否被关注
        public function isFollowOP(){
            $user_id=$_REQUEST['user_id'];
            $product_id=$_REQUEST['product_id'];
            if(empty($user_id) || empty($product_id)){
                echo json_encode(array('code'=>ERROR_CODE_OPERATE ,'message'=>'参数不完整！','data'=> array()));
                exit;
            }

            $favorites=Model('favorites');
            $result=$favorites->where(array('member_id'=>$user_id,'fav_id'=>$product_id))->find();
            if($result){
                echo json_encode(array('code' =>200 , 'message'=>'已关注','data'=>array()));
                exit;
            }else{
                echo json_encode(array('code'=>ERROR_CODE_AUTH, 'message'=>'没有被关注','data'=>array()));
                exit;
            }
        }

        //http://121.40.151.125:8086/ageg-web-business/ws/deleteFollow?humanId=%@&productId=%@  取消关注
        public function deleteFollowOP(){
            $user_id=$_REQUEST['user_id'];
            $product_id=$_REQUEST['product_id'];
            if(empty($user_id) || empty($product_id)){
                echo json_encode(array('code'=>ERROR_CODE_OPERATE ,'message'=>'参数不完整！','data'=>array()));
                exit;
            }
            $favorites=Model('favorites');
            $result=$favorites->where(array('member_id'=>$user_id, 'fav_id'=>$product_id))->delete();

            if($result){
                 echo json_encode(array('code'=>200, 'data'=>array(),'message'=>'取消关注成功'));
                 exit;
            }else{
                echo json_encode(array('code'=>ERROR_CODE_AUTH, 'data'=>array(),'message'=>'对不起，取消关注失败'));
                exit;
            }
        }



        //http://121.40.151.125:8086/ageg-web-business/ws/ getFollowedProducts?humanId=%@&page=%@ 关注列表
        public function getFollowedProductsOP(){
            $user_id=$_REQUEST['user_id'];
            $favorites=Model('favorites');
            $result=$favorites->where('member_id='.$user_id)->select();
            if($result){
                echo json_encode(array('code'=>200, 'message'=>'OK','data'=>$result));
                exit;
            }else{
                echo json_encode(array('code'=>ERROR_CODE_AUTH, 'message'=>'对不起，暂无信息','data'=>''));
                exit;
            }   
        }

        //http://121.40.151.125:8086/ageg-web-business/ws 、/getHumans?humanId=%@&page=%@&type=%@  我的粉丝
        //http://121.40.151.125:8086/ageg-web-business/ws/getFollowedUsers?userId=%@&page=%@  商户粉丝
        public function getHumansOP(){
            $id=$_REQUEST['humanId'];
            $type=$_REQUEST['type'];

            if($type=='goods'){
                $condition['fav_type']=$type;
                $condition['fav_id']=$id; 
            }
            if($type=='store'){
                $condition['fav_type']=$type;
                $condition['fav_id']=$id; 

            }
              
            $result=Model('favorites');
            $res=$result->where($condition)->select();
            if($res){
                $member=Model('member');
                foreach ($res as $key => $value) {
                    $res_member[]=$member->where('member_id='.$value['member_id'])->find();
                }
                echo json_encode(array('code'=>200 ,'message'=>'OK','data'=>$res_member));
                exit;
            }else{
                echo json_encode(array('code'=>ERROR_CODE_AUTH,'message'=>'暂无信息','data'=>''));
                exit;
            }
        }

       //  //http://121.40.151.125:8086/ageg-web-business/ws/indent/checkOrder?status=@& number=@  订单确认

        public function checkOrderOP(){
            $order_id=$_REQUEST['order_id'];
            $res=Model('order')->where(array('order_id'=>$order_id))->select();

            if(is_array($res)){
                if($res['order_state']==40){
                    echo json_encode(array('code'=>ERROR_CODE_AUTH,'message'=>'订单已被确认','data'=>array()));
                    exit;
                }else
                {
                    $data = array('order_state' => 40);
                    $res=Model('order')->where(array('order_sn'=>$order_sn))->update($data);
                    if($res){
                        echo json_encode(array('code'=>200,'message'=>'订单成功确认','data'=>array()));
                        exit;
                    }else{
                        echo json_encode(array('code'=>ERROR_CODE_OPERATE,'message'=>'请稍后重试！','data'=>array()));
                        exit;
                    }
                }


            }else{
                    echo json_encode(array('code'=>80003,'message'=>'请稍后重试！','data'=>array()));
                    exit;
            }
        }

      
        //http://121.40.151.125:8086/ageg-web-business/ws/getFollowedUsers?humanId=%@&userId=%@&page=%@   商户看见粉丝关注的产品
        public function getFollowedUsersOP(){
            $store_member_id=$_REQUEST['shop_id'];
            $result=Model('favorites')->where(array('fav_type' => 'store' ,'store_member_id'=>$store_member_id))->select();
            if($result){
                $favorites=Model('favorites');
                foreach ($result as $key => $value) {
                    $res[]=$favorites->where(array('fav_type'=>'goods','member_id'=>$value['member_id']))->find();
                }
                $goods=Model('goods');
                foreach ($res as $k => $v) {
                  $follow_good[]=$res_goods=$goods->where(array('goods_id'=>$v['fav_id']))->find();
                }
                echo json_encode(array('code'=>200, 'message'=>'OK', 'data'=>$follow_good ));
                exit;
            }else{
                echo json_encode(array('code'=>ERROR_CODE_AUTH,'message'=>'对不起，还没有粉丝'));
                exit;
            }
        }


         //http://121.40.151.125:8086/ageg-web-business/ws/getAllCategories  产品分类 （建议加个参数：父级分类id，不传或为0情况下显示一级分类，可以和产品一级分类接口合成一个）

       public function getAllCategoriesOP(){
        $good_class=Model('goods_class');
        $res=$good_class->where(array('gc_parent_id'=>0 ))->select();
        if(empty($_REQUEST['id'])){
            echo json_encode(array('code'=>200, 'message'=>'OK','data'=>$res));
            exit;
        }else{
        $id=$_REQUEST['id'];
        $res=$good_class->where(array('gc_parent_id'=>$id ))->select();
        foreach ($res as $key => $value) {
            $res[$key]=$good_class->where(array('gc_parent_id' => $value['gc_id'] ))->select();
        }

        if($res){
            echo json_encode(array('code'=>200, 'message'=>'OK','data'=>$res));
            exit;
        }else{
            echo json_encode(array('code'=>ERROR_CODE_AUTH ,'message'=>'暂无信息' ,'data'=>array()));
            exit;
         }
        }


       }


}