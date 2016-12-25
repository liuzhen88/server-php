<?php
/**
 * 本土接口，无需登录的接口
 * 李熙宇
 */

defined('emall') or exit('Access Invalid!');
class unlimited_invitationControl extends mobileHomeControl{
    public function __construct() {
        parent::__construct();
        if(isset($_REQUEST['city_name']) && $_REQUEST['city_name']=='毕节市'){
            $_REQUEST['city_name']='毕节地区';
        }
        if(isset($_REQUEST['city_name']) && $_REQUEST['city_name']=='铜仁地区'){
            $_REQUEST['city_name']='铜仁市';
        }
    }

    /**
     * 获取热门搜索
     * @return [type] [description]
     */
    public function get_hot_wordOp(){
        $cache  =   get_file_cache('hot_search_local');
        if($cache){
            $data_array =   $cache;
        }else{
            $data   =   Model('setting')->where('name in ("hot_search")')->find();
            $data_array =   explode(',',$data['value']);
            set_file_cache('hot_search_local',$data_array);
        }
        output_data($data_array);
    }

    /**
     * 下拉提示
     * @param string $city_name [<description>]
     * @param string $query 搜索关键字
     * @return [type] [description]
     */
    public function open_search_auto_down_localOp(){
        if( ! isset($_REQUEST['city_name']) || $_REQUEST['city_name'] == ''){
            output_error('城市名称关键字必须');
        }

        if(C('open_search.open')){
            $data=Model('open_search_auto_down_local')->index();
            output_data($data);
            exit;
        }
        $where = 'store_name like "%'.$_REQUEST['query'].'%" AND area_info like "%'.$_REQUEST['city_name'].'%"';
        $list_array =   Model('store')->where($where)->limit('10')->select();
        foreach ($list_array as $key => $value) {
            $item[$key]['label'] = $value['store_name'];
        }
        output_data($item);
    }

    //本土，全部分类
    public function get_class_allOp(){
        $class_type=(isset($_REQUEST['class_type']) && 'online'==$_REQUEST['class_type'])?0:1;
        $data=Model('goods_class')->getGoodsClassList('class_type='.$class_type.' and gc_parent_id=0','gc_name,gc_parent_id,gc_id');
        //查找图片
        $model_mb_category = Model('mb_category');
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach($data as $key=>$value){
            $class_id=$value['gc_id'];
            if(isset($mb_categroy[$class_id]) && !empty($mb_categroy[$class_id])) {
                //$data[$key]['pic_path'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$class_id]['gc_thumb'];
                $temp_image=getQiniuyunimg(ATTACH_MOBILE.DS.'category', $mb_categroy[$class_id]['gc_thumb']);
                $data[$key]['pic_path'] = qnyResizeHD($temp_image,200);
            } else {
                $data[$key]['pic_path'] = '';
            }
            //查找子类
            $sublist=Model('goods_class')->getGoodsClassList('class_type='.$class_type.' and gc_parent_id='.$value['gc_id'],'gc_name,gc_parent_id,gc_id');
            $all=array(
                "gc_name"=> "全部",
                "gc_parent_id"=> $value['gc_id'],
                "gc_id"=> $value['gc_id'],
            );
            //查找第三级分类
            foreach($sublist as $key2=>$value2){
                $sub_sub_list=Model('goods_class')->getGoodsClassList('class_type='.$class_type.' and gc_parent_id='.$value2['gc_id'],'gc_name,gc_parent_id,gc_id');
                $sublist[$key2]['subList']=$sub_sub_list;
            }

            //纯粹的分类数据，和添加全部的分类数据都用这个接口
            if(!isset($_REQUEST['pure'])) {
                $data[$key]['subList'] = array_merge(array($all), $sublist);
            }else{
                $data[$key]['subList']=$sublist;
            }
        }
        output_data($data);

    }

    //本土分类，更具父类查子类
    public function get_class_by_parentOp(){
        $check_param=array('parent_id');
        check_request_parameter($check_param);
        $class_type=(isset($_REQUEST['class_type']) && 'online'==$_REQUEST['class_type'])?0:1;
        $parent_id=intval($_REQUEST['parent_id']);
        $data=Model('goods_class')->getGoodsClassList('class_type='.$class_type.' and gc_parent_id='.$parent_id,'gc_name,gc_parent_id,gc_id');
        if($parent_id==0) {
            //查找图片
            $model_mb_category = Model('mb_category');
            $mb_categroy = $model_mb_category->getLinkList(array());
            $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
            foreach($data as $key=>$value){
                $class_id=$value['gc_id'];
                if(isset($mb_categroy[$class_id]) && !empty($mb_categroy[$class_id])) {
                   // $data[$key]['pic_path'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$class_id]['gc_thumb'];
                  $temp_image =  getQiniuyunimg(ATTACH_MOBILE.DS.'category', $mb_categroy[$class_id]['gc_thumb']);
                  $data[$key]['pic_path'] = qnyResizeHD($temp_image,200);
                } else {
                    $data[$key]['pic_path'] = '';
                }
            }
        }

        if(!isset($_REQUEST['pure']) && $parent_id!=0) {
            $all=array(
                "gc_name"=> "全部",
                "gc_parent_id"=> $parent_id,
                "gc_id"=> $parent_id,
            );
            $data = array_merge(array($all), $data);
        }
        output_data($data);
    }

    public function get_class_in_homeOp(){

        $limit=7;
//        if(isset($_REQUEST['version'])&&$_REQUEST['version']==2&&($_REQUEST['client_type']=='iOS'||$_REQUEST['client_type']=='ios')){
//            $limit=8;
//        }
//        $data=Model('goods_class')->getGoodsClassList('class_type=1 and gc_id in (1447,1475,1478,1479,1486,1489,1490)','gc_name,gc_parent_id,gc_id');
        $data=Model('goods_class')->field('gc_name,gc_parent_id,gc_id')->where('class_type=1 and gc_parent_id=0')->order('gc_parent_id asc,gc_sort asc,gc_id asc')->limit($limit)->select();

        //查找图片
        $model_mb_category = Model('mb_category');
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach($data as $key=>$value){
            $class_id=$value['gc_id'];
            if(isset($mb_categroy[$class_id]) && !empty($mb_categroy[$class_id])) {
               // $data[$key]['pic_path'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$class_id]['gc_thumb'];
                $temp_image =  getQiniuyunimg(ATTACH_MOBILE.DS.'category', $mb_categroy[$class_id]['gc_thumb']);
                $data[$key]['pic_path'] = qnyResizeHD($temp_image,200);
            } else {
                $data[$key]['pic_path'] = '';
            }
        }
        if($limit==7) {
            $addition['pic_path'] = UPLOAD_SITE_URL . DS . 'class_all.png';
            $addition['gc_name'] = '全部';
            $addition['gc_id']=0;
//            $addition['pic_path'] = qnyResizeHD(UPLOAD_SITE_URL . DS . 'shop_s.png',200);
//            $addition['gc_name'] = '商城';
            $data[] = $addition;
        }
        output_data($data);
    }


    //名店抢购,随机获取三条指定地区的商品，今日推荐
    public function get_random_productsOp(){
        if(!isset($_REQUEST['city_name'])){
            output_error('参数错误');
        }

//        file_put_contents("/temp/log/log.xiyu",date('Y-m-d H:i:s').json_encode($_REQUEST).PHP_EOL,FILE_APPEND);


        $limit=isset($_REQUEST['limit'])?intval($_REQUEST['limit']):3;
        //先查出满足条件的店铺（所在城市、区域）
        $option['where']=' city_name like "'.$_REQUEST['city_name'].'" and store_type=1  and store_state=1 ';
        if(isset($_REQUEST['district_name']) && !empty($_REQUEST['district_name'])){
            $option['where'].=' and district_name like "'.$_REQUEST['district_name'].'" ';
        }
        $stores=Model('store')->field('store_id')->where($option['where'])->select();
        if(empty($stores)){//找不到店铺，直接返回空数据
            output_data(array());exit;
        }
        $store_ids =  array();
        foreach ($stores as $value) {
            $store_ids[] = $value['store_id'];
        }
        $store_ids = implode(',', $store_ids);

        $arr_info=array(
                'gc.goods_name',
               'gc.goods_image',
               'gc.goods_price',
               'gc.goods_marketprice',
               'gc.goods_id as id',
           );
        $goods_where=' good_type=1 and goods_state=1 and store_id in (' . $store_ids . ') ';
        $sql='select '.implode(',',$arr_info).' from agg_goods gc where '.$goods_where.' ORDER by rand() limit '.$limit;
        $goods=Model()->query($sql);
        if(!empty($goods)) {
            foreach ($goods as $key_2 => $value) {
//                $goods[$key_2]['goods_image'] = cthumb($goods[$key_2]['goods_image'], '480,360', $goods[$key_2]['store_id']);
                $goods[$key_2]['goods_image'] = cthumb($goods[$key_2]['goods_image'], '', $goods[$key_2]['store_id']);
                $goods[$key_2]['goods_image']=$goods[$key_2]['goods_image'].'-recommend';
                $goods[$key_2]['goods_price']=del0($goods[$key_2]['goods_price']);
                $goods[$key_2]['goods_marketprice']=del0($goods[$key_2]['goods_marketprice']);
            }
        }
        output_data($goods);
    }

    //猜你喜欢，查找附近的店铺
    //is_agg用于区分真是数据和假数据
    public function guess_your_likeOp(){
        $check_parm=array('lat','lng','city_name');
        check_request_parameter($check_parm);
        if(empty($_REQUEST['lat']) || empty($_REQUEST['lng'])){
            output_error('缺少参数', array(), ERROR_CODE_ARG);
        }
        $option['field']='1 as is_agg,store_id,store_name,lng,lat,concat(area_info," ",store_address) as area,store_label as store_avatar,store_phone,round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $option['order']='distance';
        $option['where']=' city_name like "'.$_REQUEST['city_name'].'" and store_type=1 ';
        $option['limit']=6;
        $res=Model('store')->select($option);
        if(!empty($res)){
            foreach ($res as $key=>$value) {//$res[$key]['store_avatar']=getStoreLogo($res[$key]['store_avatar'],'store_avatar');
                $res[$key]['store_avatar']=getStoreLogo($res[$key]['store_avatar'],'store_avatar');
                $res[$key]['store_avatar']=qnyResizeHD($res[$key]['store_avatar'],60);
            }

        }
        $this->before_output($res);
    }

    //猜你喜欢新接口，商品，无假数据
    public function guess_your_like_goodsOp(){
        $check_parm=array('lat','lng','city_name');
        check_request_parameter($check_parm);
        if(empty($_REQUEST['lat']) || empty($_REQUEST['lng'])){
            output_error('缺少参数', array(), ERROR_CODE_ARG);
        }
        $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $order='distance asc,goods_addtime desc';
        $where='city_name like "'.$_REQUEST['city_name'].'" and store_type=1 and goods_state=1 and goods_verify=1 and store_state=1';
        if(!isset($_REQUEST['page'])){
            $this->page=30;
        }
        $goods=Model()->table('store,goods')->page($this->page,10000)->join('inner')->on('goods.store_id=store.store_id')->field('goods.*,'.$distance_sql)->where($where)->order($order)->group('goods.store_id')->select();

        foreach($goods as $key=>$value){
            $goods[$key]['goods_image']=cthumb($goods[$key]['goods_image'],'',$goods[$key]['store_id']);
            $goods[$key]['goods_image']=$goods[$key]['goods_image'].'-like1';
            $goods[$key]['goods_price']=del0($goods[$key]['goods_price']);
            $goods[$key]['goods_marketprice']=del0($goods[$key]['goods_marketprice']);
        }
        output_data($goods);
    }



    /*
     * 附近列表，按照分类、距离等搜索商品，分页参数:curpage
     * 逻辑说明：
     * 1.指定城市的店铺，如，苏州的所有店铺
     * 2.本土的店铺
     * 参考：
     * http://51aigegou.cn:8087/ageg-web-business/sys/appGetCompanies?lat=120&lng=30&city_name=%E8%8B%8F%E5%B7%9E%E5%B8%82&page=1&category_id=33
     * http://51aigegou.cn:8087/ageg-web-business/sys/appGetCompanies?lat=120&lng=30&city_name=%E8%8B%8F%E5%B7%9E%E5%B8%82&district_name=&category_id=33&proName=1&page=1&distance=&order=0&price1=100&price2=200
     */
    public function goods_listOp(){
//        $request=json_encode($_REQUEST).PHP_EOL;
//        file_put_contents('log.xiyu',$request);
        $check_param=array('city_name','lat','lng');
        check_request_parameter($check_param);
        if(empty($_REQUEST['lat']) || empty($_REQUEST['lng'])){
            output_error('缺少参数', array(), ERROR_CODE_ARG);
        }
        if(isset($_POST['curpage'])) $_GET['curpage']=$_POST['curpage'];
        $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $distance_condition=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000)';
        //查询店铺的条件
        $option['field']='1 as is_agg,store_id,area_info,store_name,store_avatar,lng,lat,concat(area_info," ",store_address) as area,store_phone,'.$distance_sql;
        $option['where']=' 1=1  ';
        $option['order']='distance asc';
        //1.分类查询
        if(isset($_REQUEST['class_id']) && 0!=intval($_REQUEST['class_id'])){//class_id:分类id
            $class_id=intval($_REQUEST['class_id']);
            //$temp='class_1='.$class_id.' or class_2='.$class_id.' or class_3='.$class_id;
            $temp = '';
            $class_data = Model('goods_class')->getGoodsClassInfoById($class_id,1);
            if ($class_data['gc_parent_id']==0)
            {
                $temp = 'class_1='.$class_id;
            }
            elseif ($class_data['gc_parent_id'] > 0)
            {
                $class_data = Model('goods_class')->getGoodsClassInfoById($class_data['gc_parent_id'],1);
                if ($class_data['gc_parent_id']==0)
                {
                    $temp = 'class_2='.$class_id;
                }
                else 
                {
                    $temp = 'class_3='.$class_id;
                }
            }
            $store_ids = array();
            if (!empty($temp))
            {
                $temp=$temp." and state=1";
                $store_ids=Model('store_bind_class')->field('distinct store_id')->where($temp)->limit(3000)->select();
            }
            
            if(empty($store_ids)){//指定分类无店铺，直接返回空数据(new,不返回空数据了，已店铺为主输出时要做假数据，)
//                $this->before_output(array('type'=>0,'data'=>array()));exit;
                $option['where'].=' and 1!=1 ';
            }else{
                foreach($store_ids as $value){
                    $store_ids_imploded[]=$value['store_id'];
                }
                $store_ids_imploded=implode(',',$store_ids_imploded);
                $option['where'].=' and store_id in ('.$store_ids_imploded.') ';
            }
        }
        $option['where'] .=' and city_name = "'.$_REQUEST['city_name'].'" and store_type=1 and store_state=1';
        

        $return_goods=false;//返回铺列表还是商品列表
        $goods_condition=' good_type=1 and goods_state=1';
        $goods_order='goods_id desc';
        //2.距离查询，不限距离时不需要加查询条件
        if(isset($_REQUEST['distance']) && 0!=intval($_REQUEST['distance'])){
            $option['where'].=' and '.$distance_condition.'<='.intval($_REQUEST['distance']);
        }

        //3.排序功能。order  0:默认，1：距离，2：发布时间，3：价格升序，4：价格降序
        /**
         * 默认就是距离由近到远，暂不需要处理
         */
        if(isset($_REQUEST['order'])){
            $order=intval($_REQUEST['order']);
            if(!in_array($order,array(0,1,2,3,4))) output_error('参数异常',array(),ERROR_CODE_ARG);
            switch($order){
                //下列三种查询条件，需要按商品返回数据
                case 2:
                    $return_goods=true;
                    $goods_order='goods_addtime desc';
                    break;
                case 3:
                    $return_goods=true;
                    $goods_order='goods_price asc';
                    break;
                case 4:
                    $return_goods=true;
                    $goods_order='goods_price desc';
                    break;
            }
        }

        //4.价格筛选
        if(isset($_REQUEST['price_min']) && isset($_REQUEST['price_max'])){
            $price_min=intval($_REQUEST['price_min']);
            $price_max=intval($_REQUEST['price_max']);
            if(!(0==$price_min && 0==$price_max)){//不限价格时不按商品查（按店铺）
                $return_goods=true;
                if($price_max>0) $goods_condition.=' and '.$price_max.'>=goods_price ';
                if($price_min>0) $goods_condition.=' and '.$price_min.'<=goods_price ';
            }
        }

        //5.商品名称搜索
        if(isset($_REQUEST['good_name'])){
            $return_goods=true;
            $goods_condition.=' and goods_name like "%'.$_REQUEST['good_name'].'%"';
        }

        //6.按区查找
        if(isset($_REQUEST['district_name'])){
            $option['where'].=' and district_name like "'.$_REQUEST['district_name'].'" ';
        }

        //a.按店铺返回数据
        if(!$return_goods) {
            $stores=Model('store')->page($this->page,800)->select($option);

            if (empty($stores))
            {
                $return_data=array('type'=>0,'data'=>$stores);
                $this->before_output($return_data);exit;
            }
            $stores_ids = array();
            foreach ($stores as $key=>$row)
            {
                $stores_ids[] = $row['store_id'];
            }
            $option_goods = array();
            $option_goods['where']=' store_id in('. implode(',', $stores_ids) . ') and goods_state=1 '  ;
            $option_goods['field'] = array('goods_id as id', 'goods_name', 'goods_price', 'goods_marketprice', 'goods_image','store_id','goods_salenum','goods_commonid');
            $goods = Model('goods')->select($option_goods);
           
            $store_goods = array();
            foreach ($goods as $key=>$row)
            {
                if (isset($store_goods[$row['store_id']]) && count($store_goods[$row['store_id']]) >= 2 )
                {
                    continue;
                }
                else 
                {
                    $row['goods_image']=cthumb($row['goods_image'],'100',$row['store_id']).'/format/png';
                    $row['goods_price']=del0($row['goods_price']);
                    $row['goods_marketprice']=del0($row['goods_marketprice']);
                    $store_goods[$row['store_id']][] = $row;
                }
            }
            
            foreach ($stores as $key => $value) {
                //店铺图片
                $stores[$key]['image']= getStoreLogo($value['store_avatar'],'store_avatar') ;
                $stores[$key]['store_avatar']= getStoreLogo($value['store_avatar'],'store_avatar') ;
                $stores[$key]['store_avatar']=qnycthumbType($stores[$key]['store_avatar'],60).'/format/png';
                $stores[$key]['image']=qnycthumbType($stores[$key]['image'],60).'/format/png';
                $stores[$key]['goods'] = isset($store_goods[$value['store_id']]) ? $store_goods[$value['store_id']] : array();
//                $stores[$key]['image']=(empty($value['store_avatar']))?'':viewFileQny($value['store_avatar']);
               /*  //查出每个店铺的两个商品
                $option_goods = array();
                $option_goods['where']=' store_id='.$value['store_id'] . ' and goods_state=1 '  ;
                $option_goods['limit'] = 2;
                $option_goods['field'] = array('goods_id as id', 'goods_name', 'goods_price', 'goods_marketprice', 'goods_image','store_id','goods_salenum','goods_commonid');
                
                if(isset($_REQUEST['class_id']) && 0!=intval($_REQUEST['class_id'])) {//class_id:分类id
                    $class_id = intval($_REQUEST['class_id']);
                    $temp = 'gc_id_1=' . $class_id . ' or gc_id_2=' . $class_id . ' or gc_id_3=' . $class_id;
                    $option_goods['where'].=' and ('.$temp.')';
                }
                $goods = Model('goods')->select($option_goods);
                //转换商品图片
                foreach($goods as $key_2=>$value){
                    //goods表存的是不是主图，飞哥要求不能改添加商品，必须从common表查主图
                    $temp_goods_common=Model('goods_common')->where('goods_commonid='.$goods[$key_2]['goods_commonid'])->find();
                    $img_temp=(isset($temp_goods_common['goods_image']))?$temp_goods_common['goods_image']:$goods[$key_2]['goods_image'];
                    $goods[$key_2]['goods_image']=cthumb($img_temp,'100',$goods[$key_2]['store_id']);
//                    $goods[$key_2]['goods_image']=viewFileQny($goods[$key_2]['goods_image']);
                }
                $stores[$key]['goods'] = $goods; */
            }
           
            $return_data=array('type'=>0,'data'=>$stores);
            $this->before_output($return_data);
           
        }
        //b.按商品返回数据
        if($return_goods){
            $option['field']=array('store_id',$distance_sql);
            $stores=Model('store')->field($option['field'])->where($option['where'])->select();
            if(empty($stores)){//按条件找不到店铺(如距离条件、城市条件)，直接返回空数据
                $this->before_output(array('type'=>1,'data'=>array()));exit;
            }else{
                $store_ids=$store_distance=array();
                foreach($stores as $value){
                    $store_ids[]=$value['store_id'];
                    $store_distance[$value['store_id']]=$value['distance'];
                }
                $store_ids=implode(',',$store_ids);
                $goods_condition.=' and store_id in ('.$store_ids.') ';
                $field = array('goods_id as id', 'goods_name', 'goods_price', 'goods_marketprice', 'goods_image','store_id','goods_salenum','goods_commonid');
                if(isset($_REQUEST['class_id']) && 0!=intval($_REQUEST['class_id'])) {//class_id:分类id
                    $class_id = intval($_REQUEST['class_id']);
                    $temp = 'gc_id_1=' . $class_id . ' or gc_id_2=' . $class_id . ' or gc_id_3=' . $class_id;
                    $goods_condition.=' and ('.$temp.')';
                }
                $goods=Model('goods')->table('goods')->page($this->page)->field($field)->select(array('where'=>$goods_condition,'order'=>$goods_order));
                //转换商品图片,并添加距离数据
                foreach($goods as $key=>$value){
                    //goods表存的是不是主图，飞哥要求不能改添加商品，必须从common表查主图
                    $temp_goods_common=Model('goods_common')->where('goods_commonid='.$goods[$key]['goods_commonid'])->find();
                    $img_temp=(isset($temp_goods_common['goods_image']))?$temp_goods_common['goods_image']:$goods[$key]['goods_image'];
                    $goods[$key]['goods_image']=cthumb($img_temp,'100',$goods[$key]['store_id']).'/format/png';
                    $goods[$key]['goods_price']=del0($goods[$key]['goods_price']);
                    $goods[$key]['goods_marketprice']=del0($goods[$key]['goods_marketprice']);
                    $goods[$key]['distance']=$store_distance[$goods[$key]['store_id']];
                }
                $return_data=array('type'=>1,'data'=>$goods);
                $this->before_output($return_data);
            }
        }

    }


    /*
     * 附近列表,第二版，按照分类、距离等搜索店铺，分页参数:curpage
     * @param city_name 城市名称 必填
     * @param lat 经度 必填
     * @param lng 纬度 必填
     * @param order  0:默认，1：距离，2:新店优先，3：好评，4：人均消费降序，5：人均消费升序 非必填
     * @param district_name 区域名称 非必填
     * @param store_name 店铺名称 非必填
     * @param class_id 分类id 非必填
     * @param distance 距离 非必填
     * @param curpage 当前页 非必填 默认第一页
     * @param page 每页显示数量 非必填
     * 逻辑说明：
     * 1.指定城市的店铺，如，苏州的所有店铺
     * 2.本土的店铺
     */
    public function goods_list_v2Op(){
//        $request=json_encode($_REQUEST).PHP_EOL;
//        file_put_contents('log.xiyu',$request);
        $check_param=array('city_name','lat','lng');
        check_request_parameter($check_param);
        if(empty($_REQUEST['lat']) || empty($_REQUEST['lng'])){
            output_error('缺少参数', array(), ERROR_CODE_ARG);
        }
        $class_names=Model('goods_class')->getGoodsClassListByParentId(0,1);
        $class_names=array_under_reset($class_names,'gc_id');
        if(!isset($_REQUEST['page'])){
            $_REQUEST['page']=30;
        }

        if(C('open_search.open')){
            $data=Model('open_search')->get_store_local();
            $store_list=$data['result']['items'];
            foreach($store_list as & $value){
                $value['is_agg']=1;
                $value['distance']=get_distance($_REQUEST['lng'].','.$_REQUEST['lat'],$value['lng'].','.$value['lat']);
                $value['store_avatar']= getStoreLogo($value['store_avatar'],'store_avatar').'-shopicon' ;
                $value['per_consumption']=del0($value['per_consumption']);
                //分销商品数量，android用
                $dis_count=Model('distribution_goods')->where(array('store_id'=>$value['store_id'],'goods_state'=>1))->count();
                $value['dis_goods_count']=$dis_count;
                $value['class_name']=isset($class_names[$value['gc_parent_id']])?$class_names[$value['gc_parent_id']]['gc_name']:'';
                unset($value['gc_parent_id']);
            }
//            file_put_contents('F:log.xiyu',date('Y-m-d H:i:s').PHP_EOL.json_encode($store_list));
            output_data($store_list);
            exit;
        }

        if(isset($_POST['curpage'])) {
            $_GET['curpage'] = $_POST['curpage'];
        }
        $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $distance_condition=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000)';
        //查询店铺的条件
//        $option['field']='1 as is_agg,store_id,area_info,store_name,store_avatar,lng,lat,concat(area_info," ",store_address) as area,store_phone，whole_discount,per_consumption,per_consumption+(per_consumption=0)*9999999 asc_per_consumption,'.$distance_sql;
        $option['field']='store_id,store_name,store_avatar,city_name,district_name,lng,lat,per_consumption,store_credit,1 as is_agg,per_consumption+(per_consumption=0)*9999999 asc_per_consumption,'.$distance_sql;
        $option['where']=' city_name like "'.$_REQUEST['city_name'].'" and store_type=1 and store_state=1 ';
        $option['order']='distance asc';
        //1.分类查询
        if(isset($_REQUEST['class_id']) && 0!=intval($_REQUEST['class_id'])){//class_id:分类id
            $class_id=intval($_REQUEST['class_id']);
            $temp='(class_1='.$class_id.' or class_2='.$class_id.' or class_3='.$class_id.') and state=1';
            $store_ids=Model('store_bind_class')->field('distinct store_id')->where($temp)->limit(10000)->select();
            if(empty($store_ids)){//指定分类无店铺
                $option['where'].=' and 1!=1 ';
            }else{
                foreach($store_ids as $value){
                    $store_ids_imploded[]=$value['store_id'];
                }
                $store_ids_imploded=implode(',',$store_ids_imploded);
                $option['where'].=' and store_id in ('.$store_ids_imploded.') ';
            }
        }

        //2.距离查询，不限距离时不需要加查询条件
        if(isset($_REQUEST['distance']) && 0!=intval($_REQUEST['distance'])){
            $option['where'].=' and '.$distance_condition.'<='.intval($_REQUEST['distance']);
        }

        //3.排序功能。 0:默认，1：距离，2:新店优先，3：好评，4：人均消费降序，5：人均消费升序
        /**
         * 默认就是距离由近到远，暂不需要处理
         */
        if(isset($_REQUEST['order'])){
            $order=intval($_REQUEST['order']);
            if(!in_array($order,array(0,1,2,3,4,5))) output_error('参数异常',array(),ERROR_CODE_ARG);
            switch($order){
                case 2:
                    $option['order']='store_time desc';
                    break;
                case 3:
                    $option['order']='store_credit desc';
                    break;
                case 4:
                    $option['order']='per_consumption desc';
                    break;
                case 5:
                    $option['order']='asc_per_consumption asc';
                    break;
            }
        }

        //4.店铺名
        if(isset($_REQUEST['store_name'])){
            $option['where'].=' and store_name like "%'.trim($_REQUEST['store_name']).'%"';
        }

        //5.按区查找
        if(isset($_REQUEST['district_name'])){
            $option['where'].=' and district_name like "'.$_REQUEST['district_name'].'" ';
        }
        $this->page=30;
        $stores=Model('store')->page($this->page)->select($option);
        $store_ids_finded=array();
        foreach ($stores as $key => $value) {
            //分销商品数量
            $dis_count=Model('distribution_goods')->where(array('store_id'=>$value['store_id'],'goods_state'=>1))->count();
            $stores[$key]['dis_goods_count']=$dis_count;
            if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                $stores[$key]['store_name']=htmlspecialchars_decode($value['store_name']);
            }
            //店铺图片
            $stores[$key]['store_avatar']= getStoreLogo($value['store_avatar'],'store_avatar') ;
            $stores[$key]['per_consumption']=del0($stores[$key]['per_consumption']);
            $stores[$key]['store_avatar']=$stores[$key]['store_avatar'].'-shopicon';
            unset($stores[$key]['asc_per_consumption']);
            $store_ids_finded[]=$value['store_id'];
        }

        //假数据分页
        $max_item=Model('store')->where($option['where'])->count();
        if(isset($_REQUEST['curpage'])&&intval($_REQUEST['curpage'])>1){
            if($this->page==0){
                $this->page=30;
            }
            $curpage=intval($_REQUEST['curpage']);
            $last_page=ceil($max_item/$this->page);
            if($curpage>$last_page){
                $_GET['fake_page']=$curpage-$last_page;
            }
        }

        //列表要显示店铺的一个经营类目的一级分类名字
        if(isset($class_id)){
            $class_info=Model('goods_class')->where('gc_id='.$class_id)->find();
            while(!empty($class_info) && $class_info['gc_parent_id']!=0){
                $class_info=Model('goods_class')->where('gc_id='.$class_info['gc_parent_id'])->find();
            }
            foreach($stores as $key=>$value){
                $stores[$key]['class_name']=(empty($class_info))?'':$class_info['gc_name'];
            }
        }else{
            $class_with_store=Model('store_bind_class')->where(array('store_id'=>array('in',$store_ids_finded),'state'=>1))->group('store_id')->select();
            $class_names=Model('goods_class')->getGoodsClassListByParentId(0,1);
            $class_with_store_sorted=array();
            foreach($class_with_store as $key=>$value){
                $class_with_store_sorted[$value['store_id']]=intval($value['class_1']);
            }
            $class_names_sorted=array();
            foreach($class_names as $key=>$value){
                $class_names_sorted[$value['gc_id']]=$value;
            }
            foreach($stores as $key=>$value){
                if(isset($class_with_store_sorted[$value['store_id']])&&isset($class_names_sorted[$class_with_store_sorted[$value['store_id']]])){
                    $stores[$key]['class_name']=$class_names_sorted[$class_with_store_sorted[$value['store_id']]]['gc_name'];
                }else{
                    $stores[$key]['class_name']='';
                }
            }
        }
        $this->before_output($stores);
    }


    /**
     * 添加假数据。猜你喜欢，附近输出之前处理，如果没有找到店铺，就找假数据
     */
    public function before_output($data){
        //查假数据的前提
        $fake_condition=(!isset($_REQUEST['class_id']) || 0==intval($_REQUEST['class_id'])) && (empty($data) || (isset($data['type']) && 0==$data['type'] && empty($data['data'])));
        $fake_condition=(isset($_REQUEST['store_name']))?false:$fake_condition;
        if($fake_condition){
            //查找假数据
            $_GET['curpage']=isset($_REQUEST['fake_page'])?intval($_REQUEST['fake_page']):1;
            $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-Lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(Lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-Lng*pi()/180)/2),2)))*1000) as distance';
            $where=' city like "'.rtrim($_REQUEST['city_name'],'市').'"';
            //根据距离条件查假数据
            if(isset($_REQUEST['distance']) && 0!=intval($_REQUEST['distance'])){
                $distance_condition='  round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-Lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(Lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-Lng*pi()/180)/2),2)))*1000)';
                $where.=' and '.$distance_condition.'<='.intval($_REQUEST['distance']);
            }
            //区域条件
            if(isset($_REQUEST['district_name'])){
                $where.=' and area like "'.$_REQUEST['district_name'].'" ';
            }
            //查询店铺
            $field='0 as is_agg,meituan_shop_id as store_id,address as area_info,name as store_name,  " " as store_avatar,Lng as lng,Lat as lat,concat(city,area,address) as area,tel as store_phone,'.$distance_sql;
            if(empty($data)) $this->page=6;//猜你喜欢只显示六条
            $fake_data=Model('spider_company')->page($this->page)->field($field)->where($where)->order('distance asc')->select();
            if(isset($data['data'])){
                $data['data']=$fake_data;
                output_data($data);
            }else{
                output_data($fake_data);
            }
        }else {
            output_data($data);
        }
    }

    //本土商品详情页
    public function good_detailOp(){
        $check_param=array('good_id','lat','lng');
        check_request_parameter($check_param);
        if(empty($_REQUEST['lat']) || empty($_REQUEST['lng'])){
            output_error('缺少参数', array(), ERROR_CODE_ARG);
        }
        $goods_info=Model('goods')->getGoodsInfoByID(intval($_REQUEST['good_id'])); //必须有这个缓存处理机制，否则后面的更新点击次数会出问题
//        $goods_info=Model('goods')->where(array('goods_id'=>intval($_REQUEST['good_id']),'good_type'=>1))->find();
        if(!$goods_info || $goods_info['good_type']!=1) output_error('商品不存在'); //线上不可以展示
        $goods_common_info=Model('goods_common')->field(array('mobile_body','goods_body','buy_notes','activity_notes','using_time','package_note','try_area'))->where(array('goods_commonid'=>$goods_info['goods_commonid']))->find();

        $goods_common_info['wap_buy_notes']=json_decode($goods_common_info['buy_notes'],true);
        $goods_common_info['wap_activity_notes']=json_decode($goods_common_info['activity_notes'],true);
        if(!$goods_common_info) output_error('商品不存在');

        //计算评论
        $average=Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsCommonID($goods_info['goods_commonid']);
        $goods_info['evaluation_good_star']=$average['star_average'];
        $goods_info['evaluation_count']=$average['all'];

        $detail_simple='';  //截取到第一张图片的图文详情
        $mobile_body_pre=array();
        $add_detail_simple=true;
        // 解析手机端商品详情信息
        if ($goods_common_info['mobile_body'] != '') {
            $mobile_body_array = mb_unserialize($goods_common_info['mobile_body']);
            $mobile_body = '';
            if (is_array($mobile_body_array)) {
                
                foreach ($mobile_body_array as $val) {
                    switch ($val['type']) {
                        case 'text':
                            $mobile_body .= '<pre  style="word-wrap: break-word;">' . $val['value'] . '</pre>';
                            if($add_detail_simple) {
                                $detail_simple .= '<pre  style="word-wrap: break-word;">' . $val['value'] . '</pre>';
                            }
                            break;
                        case 'title':
                            $mobile_body .= '<div  class="activeDetailHTitle">' . $val['value'] . '</div>';
                            break;
                        case 'image':
                            //七牛云压缩图片处理
                            $image_url=$val['value'];
                            $tail=stristr($image_url,'?imageView');
                            if($tail!==false){
                                $image_url=str_ireplace($tail,'',$image_url);
                                $image_url=$image_url.'-w480wm';
//                                $image_url=qnycthumbType($image_url,360,true);
                            }else{
                                $image_url=$image_url.'-w480wm';
//                                $image_url=qnycthumbType($image_url,360,true);
                            }
                            if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                                $mobile_body .= '<img src="' . $image_url . '" class="lazyLoad">';
                            }else {
                                $mobile_body .= '<img data-echo="' . $image_url . '" class="lazyLoad">';
                            }
                            if($add_detail_simple) {
                                if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                                    $detail_simple .= '<img  class="lazyLoad" style="width:100%" src="' . $image_url . '">';
                                }else {
                                    $detail_simple .= '<img  class="lazyLoad" style="width:100%" data-echo="' . $image_url . '">';
                                }
                                $add_detail_simple=false;
                            }
                            $val['value']=$image_url;
                            break;
                    }
                    $mobile_body_pre[]=$val;
                }  
            }
            $goods_common_info['mobile_body'] = $mobile_body;
        }else{
//            $temp_body=preg_replace('/(.jpg|.png|.bmp|.jpeg|.gif)"/iU', '$1?'.qny_watermark_tail().'|imageView2/1/w/360/h/360"', $goods_common_info['goods_body']);
//            $temp_body=preg_replace('/(.jpg|.png|.bmp|.jpeg|.gif)"/iU', '$1?imageView2/1/w/360/h/360|'.qny_watermark_tail().'"', $goods_common_info['goods_body']);
            $temp_body=preg_replace('/(.jpg|.png|.bmp|.jpeg|.gif)"/iU', '$1-w480wm"', $goods_common_info['goods_body']);
            $temp_body=str_ireplace('?imageView2/1/w/1280/h/1280"','-w480wm"',$temp_body);
            $temp_body=str_ireplace('http://115.29.113.36:8033/appImages',UPLOAD_SITE_URL,$temp_body);
            $goods_common_info['mobile_body']=$temp_body;
            $detail_simple.='<div>' .$goods_common_info['goods_name']. '</div>';
            $temp_image=thumb($goods_info, '360');//获取图片路径
            $temp_image = str_ireplace("?imageView2/1/w/360/h/360", '-w480wm', $temp_image);
            if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                $detail_simple .= '<img  class="lazyLoad" style="width:100%"  src="' . $temp_image . '">';
            }else {
                $detail_simple .= '<img  class="lazyLoad" style="width:100%"  data-echo="' . $temp_image . '">';
            }
        }
        $goods_info['detail_simple']=$detail_simple;
        $goods_info['goods_price']=del0($goods_info['goods_price']);
        $goods_info['goods_marketprice']=del0($goods_info['goods_marketprice']);
        $goods_info['goods_image']=cthumb($goods_info['goods_image'],'',$goods_info['store_id']);
        $goods_info['goods_image']=qnyResizeHD($goods_info['goods_image'],240);
        $out_data['good_info']=array_merge($goods_common_info,$goods_info);
        $out_data['good_info']['mobile_body_pre']=$mobile_body_pre;
        //计算评论
        $average=Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsCommonID($goods_info['goods_commonid']);
        $out_data['good_info']['evaluation_good_star']=$average['star_average'];
        //解决旧版本android 闪退问题
        if('android'==strtolower($_REQUEST['client_type'])&&(!isset($_REQUEST['ver_code']) || $_REQUEST['ver_code']<=26)){
            $out_data['good_info']['evaluation_good_star']=intval($out_data['good_info']['evaluation_good_star']);
        }

        $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $store_info=Model('store')->field('*,'.$distance_sql)->find($goods_info['store_id']);

        if(!$store_info) output_error('店铺不存在');
        $store_info['per_consumption']=del0($store_info['per_consumption']);
        //change chenyifei
        /*
        $store_info['image']=(empty($store_info['store_label']))?'':UPLOAD_SITE_URL.'/shop/store/'.$store_info['store_label'];//原接口，顶部展示图
        $store_info['store_avatar']=(empty($store_info['store_avatar']))?'':UPLOAD_SITE_URL.'/shop/store/'.$store_info['store_avatar'];//头像
        $store_info['store_label']=(empty($store_info['store_label']))?'':UPLOAD_SITE_URL.'/shop/store/'.$store_info['store_label'];//logo
        $store_info['store_banner']=(empty($store_info['store_banner']))?'':UPLOAD_SITE_URL.'/shop/store/'.$store_info['store_banner'];//横幅
        */
       
        $store_str              =   '';
        $store_info_sort        =   Model('store')->field('store_id')
                                                ->where(array('city_id' =>$store_info['city_id']))
                                                ->select();
    
        foreach ($store_info_sort as $key => $value) {
            $store_str .= $value['store_id'].",";
        }

        $sort['store_id']       =   array('in',rtrim($store_str,','));
        $sort['gc_id_1']        =   $goods_info['gc_id_1'];
        $sort['goods_salenum']  =   $goods_info['goods_salenum'];
        $sort['good_type']      =   1;
        $goods_info_sort        =   Model('goods')->getGoodsInfoSort($sort,'*',3600); 
        foreach ($goods_info_sort as $key => $value) {
           $goods_info_sort[$key]['goods_image'] = cthumb($goods_info_sort[$key]['goods_image'], 60, $goods_info_sort[$key]['store_id']);
        }
        $out_data['goods_info_sort']    =   $goods_info_sort;


        $store_info['image']= getStoreLogo($store_info['store_label'],'store_logo'); //原接口，顶部展示图
        $store_info['store_avatar']= getStoreLogo($store_info['store_avatar'],'store_avatar') ;//头像
        $store_info['store_label']= getStoreLogo($store_info['store_label'],'store_logo'); //logo
        $store_info['store_banner']= getStoreLogo($store_info['store_banner'],'store_logo') ;//横幅
        //图片压缩
        $store_info['image']=qnycthumbType($store_info['image'],60);
        $store_info['store_avatar']=qnycthumbType($store_info['store_avatar'],60);
        $store_info['store_label']=qnycthumbType($store_info['store_label'],60);
        $store_info['store_banner']=qnycthumbType($store_info['store_banner'],60);

        $member_info=Model('member')->where(array('member_id'=>$store_info['member_id']))->find();
        if(!$member_info) output_error('店铺异常');
        $store_info['invitation']=$member_info['invitation'];
        $array=json_decode($store_info['note_info'],TRUE);

        $store_info['is_wifi']=(string)$array['is_wifi'];
        $store_info['is_stopcart']=(string)$array['is_stopcart'];
        $store_info['is_cash']=(string)$array['is_cash'];
        
        $out_data['store_info']=$store_info;
        // 商品图片
        $image_more = Model('goods')->getGoodsImageByKey($goods_info['goods_commonid'] . '|' . $goods_info['color_id']);
        $goods_image_mobile = array();
        if (!empty($image_more)) {
            foreach ($image_more as $val) {
                $temp_image= cthumb($val['goods_image'], '', $goods_info['store_id']);
                $goods_image_mobile[]=$temp_image.'-toppic';
//                if($goods_info['goods_id']>8161){
//                    $goods_image_mobile[] =$temp_image.'-w480h480wmfill';
//                }else{
//                    $goods_image_mobile[]=$temp_image.'-wm480x200fill';
//                }
            }
        } else {
            $temp_image=thumb($goods_info, '360');//获取图片路径
            //处理大小
//            if($goods_info['goods_id']>8161) {
//                $temp_image = str_ireplace("?imageView2/1/w/360/h/360", '-w480h480wmfill', $temp_image);
//            }else{
//                $temp_image = str_ireplace("?imageView2/1/w/360/h/360", '-wm480x200fill', $temp_image);
//            }
            $temp_image = str_ireplace("?imageView2/1/w/360/h/360", '-toppic', $temp_image);
            $goods_image_mobile[] = $temp_image;
        }
        // 商品受关注次数加1
        $goods_info['goods_click'] = intval($goods_info['goods_click']) + 1;
        if (C('cache_open')) {
            $goods_info_cache = rcache($goods_info['goods_id'], 'goods', 'goods_name');
            if (!empty($goods_info_cache) && isset($goods_info_cache['goods_name']) )
            {
                wcache($goods_info['goods_id'], array('goods_click' => $goods_info['goods_click']), 'goods');
            }
            wcache('updateRedisDate', array($goods_info['goods_id'] => $goods_info['goods_click']), 'goodsClick');
        } else {
            Model('goods')->editGoodsById(array('goods_click' => array('exp', 'goods_click + 1')), $goods_info['goods_id']);
        }
        //店铺&商品访问流量统计
        Model('statistics')->flowstat_record($goods_info['store_id'],$goods_info['goods_id']);
        $out_data['good_image']=$goods_image_mobile;
        unset($out_data['good_info']['goods_body']);
        output_data($out_data);
    }

    //店铺详情,连带商品
    public function store_detailOp(){
        $check_param=array('store_id');
        check_request_parameter($check_param);
        $store_id=intval($_REQUEST['store_id']);
        if(isset($_REQUEST['is_agg']) && $_REQUEST['is_agg']==0){
            $field=array('0 as is_agg','id as store_id','name as store_name','"" as store_label','"" as store_avatar','"" as store_banner','tel as store_phone','city as area_info','address as store_address','Lng as lng','Lat as lat','0 as member_id','"" as store_describe','10 as whole_discount','0 as per_consumption','0 as store_credit');
            $store_info=Model('spider_company')->field($field)->where('store_id='.$store_id)->find();
            if(!$store_info) output_error('店铺不存在');
            $out_data['store_info']=$store_info;
            $out_data['goods_info']=array();
            output_data($out_data);
            exit;
        }

        $field=array('1 as is_agg','store_id','store_name','store_label','store_avatar','store_banner','store_phone','area_info','store_address','lng','lat','member_id','store_describe','whole_discount','per_consumption','store_credit');

        $store_info=Model('store')->field($field)->where('store_type=1 and store_id='.$store_id)->find();
        if(!$store_info) output_error('店铺不存在');
        if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
            $store_info['store_name']=htmlspecialchars_decode($store_info['store_name']);
        }

        //评价数
        $evaluate=Model('evaluate_goods')->where(array('geval_storeid'=>$store_id))->count();
        $store_info['evaluate_count']=$evaluate;

        //分销商品数量
        $dis_count=Model('distribution_goods')->where(array('store_id'=>$store_id,'goods_state'=>1))->count();
        $store_info['dis_goods_count']=$dis_count;

        //邀请码
        $member_info=Model('member')->where(array('member_id'=>$store_info['member_id']))->find();
        if(!$member_info) output_error('店铺异常');
        $store_info['invitation']=$member_info['invitation'];
        
        $store_info['image']= getStoreLogo($store_info['store_label'],'store_logo'); //原接口，顶部展示图
        $store_info['store_avatar']= getStoreLogo($store_info['store_avatar'],'store_avatar') ;//头像
        $store_info['store_label']= getStoreLogo($store_info['store_label'],'store_logo'); //logo
        $store_info['store_banner']= getStoreLogo($store_info['store_banner'],'store_logo') ;//横幅(前端显示的)
        $store_info['store_banner']=$store_info['store_banner'].'-toppic';
        $store_info['per_consumption']=del0($store_info['per_consumption']);
        $store_info['area']=$store_info['area_info'];
		$store_info['store_describe']=($store_info['store_describe']===null)?'':$store_info['store_describe'];
        $store_info['store_describe_pre']=array();
        //店铺图文描述
        if(!empty($store_info['store_describe'])){
            $store_describe=json_decode($store_info['store_describe'],true);
            $store_info['store_describe_pre']=json_decode($store_info['store_describe'],true);
            $store_info['store_describe']='';
            foreach($store_describe as $key=>$val){
                    switch ($val['type']) {
                        case 'text':
                            $store_info['store_describe'] .= '<pre style="word-wrap: break-word;">' . $val['data'] . '</pre>';
                            break;
                        case 'img':
                            //七牛云压缩图片处理
                            $image_url=$val['data'].'-w480wm';
                            if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                                $store_info['store_describe'] .= '<img class="lazyLoad" style="width:100%" src="' . $image_url . '">';
                            }else {
                                $store_info['store_describe'] .= '<img class="lazyLoad" style="width:100%" data-echo="' . $image_url . '">';
                            }
                            break;
                    }
            }
            $store_info['store_describe']='<div  style="width:100%">'.$store_info['store_describe'].'</div>';
        }

        $out_data['store_info']=$store_info;


        $field=array('goods_id as id','goods_image','goods_name','goods_price','goods_marketprice','goods_salenum','store_name','limit_count','limit_storage','goods_storage');

        if(isset($_REQUEST['curpage'])) {
            $goods_info = Model('goods')->where(array('store_id' => $store_id, 'goods_state' => 1))->order('sort desc,goods_id asc')->field($field)->page($this->page)->select();
        }else{
            $goods_info = Model('goods')->where(array('store_id' => $store_id, 'goods_state' => 1))->order('sort desc,goods_id asc')->field($field)->select();
        }

        foreach($goods_info as $key=>$value){
            $goods_info[$key]['goods_image']=cthumb($goods_info[$key]['goods_image'],'',$goods_info[$key]['store_id']);
            $goods_info[$key]['goods_image']=qnyResizeHD($goods_info[$key]['goods_image'],240);
            $goods_info[$key]['goods_price']=del0($goods_info[$key]['goods_price']);
            $goods_info[$key]['goods_marketprice']=del0($goods_info[$key]['goods_marketprice']);
            if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                $goods_info[$key]['goods_name']=htmlspecialchars_decode($goods_info[$key]['goods_name']);
            }
        }
        $out_data['goods_info']=$goods_info;
        //店铺访问流量统计
        Model('statistics')->flowstat_record($store_id);

        output_data($out_data);
    }

    //店铺商品列表
    public function get_goods_by_storeOp(){
        $check_param=array('store_id');
        check_request_parameter($check_param);
        $store_id=intval($_REQUEST['store_id']);
        $field=array('goods_image','goods_name','goods_price','goods_id as id', 'goods_marketprice','goods_salenum');
        if(isset($_POST['curpage'])) $_GET['curpage']=intval($_POST['curpage']);
        $goods_info=Model('goods')->where(array('store_id'=>$store_id,'goods_state'=>1))->order('sort desc')->page($this->page)->field($field)->select();
        foreach($goods_info as $key=>$value){
            $goods_info[$key]['goods_image']=cthumb($goods_info[$key]['goods_image'],'60',$goods_info[$key]['store_id']);
            $goods_info[$key]['goods_price']=del0($goods_info[$key]['goods_price']);
            $goods_info[$key]['goods_marketprice']=del0($goods_info[$key]['goods_marketprice']);
            if('android'==strtolower($_REQUEST['client_type'])||'ios'==strtolower($_REQUEST['client_type'])){
                $goods_info[$key]['goods_name']=htmlspecialchars_decode($goods_info[$key]['goods_name']);
            }
        }
        $out_data['goods_info']=$goods_info;
        output_data($out_data);

    }

    //店铺入驻申请接口
    public function joininOp(){
        $check_param=array('store_name','contacts_name','contacts_phone','address_detail','class1_id','city_id','province_id','district_id');
        check_request_parameter($check_param);

        $arr_to_data=array('store_name','contacts_name','contacts_phone','address_detail');
        foreach($arr_to_data as $value){
            $data[$value]=$_REQUEST[$value];
        }

       $class=array($_REQUEST['class1_id'],$_REQUEST['class2_id'],$_REQUEST['class3_id']);
        $data['store_class_ids']=serialize(array(implode(',',$class)));
        $region=array('province'=>$_REQUEST['province_id'],'city'=>$_REQUEST['city_id'],'area'=>$_REQUEST['district_id']);
        $data['region']=json_encode($region);

        $class_name='';
        $temp=Model('goods_class')->field('gc_name')->where('gc_id='.intval($_REQUEST['class1_id']))->find();
        if(empty($temp)) output_error('参数错误，找不到分类');
        $class_name.=trim($temp['gc_name']).',';
        if(isset($_REQUEST['class2_id']) && intval($_REQUEST['class2_id'])!=0) {
            $temp = Model('goods_class')->field('gc_name')->where('gc_id=' . intval($_REQUEST['class2_id']))->find();
            if (!empty($temp)) {
                $class_name .= trim($temp['gc_name']) . ',';
                if(isset($_REQUEST['class3_id']) && intval($_REQUEST['class3_id'])!=0) {
                    $temp = Model('goods_class')->field('gc_name')->where('gc_id=' . intval($_REQUEST['class3_id']))->find();
                    if (!empty($temp)) $class_name .= trim($temp['gc_name']);
                }
            }
        }
        $data['store_class_names']=serialize(array($class_name));

        $region_name='';
        $temp=Model('area')->field('area_name')->where('area_id='.intval($_REQUEST['province_id']))->find();
        if(empty($temp)) output_error('参数错误，找不到区域');
        $region_name.=trim($temp['area_name']).' ';
        $temp=Model('area')->field('area_name')->where('area_id='.intval($_REQUEST['city_id']))->find();
        if(empty($temp)) output_error('参数错误，找不到区域');
        $region_name.=trim($temp['area_name']).' ';
        if(isset($_REQUEST['district_id']) && intval($_REQUEST['district_id'])!=0) {
            $temp = Model('area')->field('area_name')->where('area_id=' . intval($_REQUEST['district_id']))->find();
            if (empty($temp)) output_error('参数错误，找不到区域');
            $region_name .= trim($temp['area_name']);
        }
        $data['address']=$region_name;
        $data['apply_time']=time();

        $m=Model('store_o2o_joinin')->insert($data);
        if($m) output_data('申请成功');
        else output_data('申请失败');
    }



    /**
     * 地区列表(根据父区域找子区域)
     */
    public function area_listOp() {
        $area_id = intval($_REQUEST['area_id']);

        $model_area = Model('area');

        $condition = array();
        if($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }

    //地区接口，全部区域
    public function area_allOp(){
        $data=Model('area')->getAreaList(array('area_parent_id'=>0),'area_id,area_name');
        foreach($data as $key1=>$value1){
            $sub_area=Model('area')->getAreaList(array('area_parent_id'=>$value1['area_id']),'area_id,area_name');
            foreach($sub_area as $key2=>$value2){
                $sub_sub_area=Model('area')->getAreaList(array('area_parent_id'=>$value2['area_id']),'area_id,area_name');
                $sub_area[$key2]['sub_area']=$sub_sub_area;
            }
            $data[$key1]['sub_area']=$sub_area;
        }
        output_data($data);
    }

    //获取app版本
    public function get_app_versionOp(){
        $data=Model('setting')->where('name in ("mobile_apk","mobile_apk_version","mobile_ios", "mobile_ios_apk_force_version","mobile_apk_force_version")')->select();
        foreach($data as $value){
            $return_data[$value['name']]=$value['value'];
        }
        $force_update=0;
        if(isset($_REQUEST['version']) && $_REQUEST['client_type'] == 'android' && floatval($_REQUEST['version'])<$return_data['mobile_apk_force_version']){
            $force_update=1;
        }
        $return_data['force_update']=$force_update;
        //客服电话
        $return_data['service_telephone'] = '4006277745';
        //获取图片更新信息
        $picArray = array();
        //启动图
        $picArray['loading']['version'] = 1;
        $picArray['loading']['pic'] = "http://img.aigegou.com/shop/store/goods/23201/2016/02/02/23201_05077249376359774.png";
        //引导图
        $picArray['guide']['version'] = 1;
        $picList = array();
        $picList[0] = 'http://img.aigegou.com/shop/store/goods/23201/2016/02/02/23201_05077249376359774.png';
        $picList[1] = 'http://img.aigegou.com/shop/store/goods/23201/2016/02/02/23201_05077249376359774.png';
        $picArray['guide']['picList'] = $picList;
        //底部菜单
        $picArray['menu']['version'] = 1;
        $menuList_0 = $menuList_1 = array();
        //默认
        $menuList_0[0] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $menuList_0[1] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $menuList_0[2] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $menuList_0[3] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        //按下
        $menuList_1[0] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $menuList_1[1] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $menuList_1[2] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $menuList_1[3] = 'http://7xl2n7.com2.z0.glb.qiniucdn.com/bottom_menu00.png';
        $picArray['menu']['up_pic_List'] = $menuList_0;
        $picArray['menu']['down_pic_List'] = $menuList_1;
        $return_data['picture'] = $picArray;
        output_data($return_data);
    }

    //爱大腿获取app版本
    public function get_adt_app_versionOp(){
        $data=Model('setting')->where('name in ("adt_mobile_apk","adt_mobile_apk_version","adt_mobile_ios", "adt_mobile_ios_apk_force_version","adt_mobile_apk_force_version")')->select();
        foreach($data as $value){
            $return_data[$value['name']]=$value['value'];
        }
        $force_update=0;
        if(isset($_REQUEST['version']) && $_REQUEST['client_type'] == 'android' && floatval($_REQUEST['version'])<$return_data['adt_mobile_apk_force_version']){
            $force_update=1;
        }
        $return_data['force_update']=$force_update;
        output_data($return_data);
    }

    //推荐商品,（新商城接口）
    public function get_recommend_goodsOp(){
        $goods_condition='goods_id in (select goods_id from agg_recommend_goods) and good_type=2';
        $goods_order='goods_id desc';
        $field = array('goods_id as id', 'goods_name', 'goods_price', 'goods_marketprice', 'goods_image','store_id','evaluation_good_star','evaluation_count');
        $goods=Model('goods')->page($this->page)->field($field)->select(array('where'=>$goods_condition,'order'=>$goods_order));
        //转换商品图片
        foreach($goods as $key=>$value){
            $goods[$key]['goods_image']=cthumb($goods[$key]['goods_image'],'',$goods[$key]['store_id']);
            $goods[$key]['goods_image']=qnyResizeHD($goods[$key]['goods_image'],240);
            $goods[$key]['goods_price']=del0($goods[$key]['goods_price']);
            $goods[$key]['goods_marketprice']=del0($goods[$key]['goods_marketprice']);
        }
        output_data($goods);
    }


    /**
     * 商品评价列表
     * page 一页显示商品数，可以不传
     * curpage 当前页数
     * geval_goodsid 商品id
     * type 0:全部1:好评，2:中评，3:差评
     * @notice 匿名评价功能先不要
     */
    public function get_evaluate_goods_listOp(){
        $check_param=array('geval_goodsid');
        check_request_parameter($check_param);
        $type=isset($_REQUEST['type'])?intval($_REQUEST['type']):0;
        $goods_id=intval($_REQUEST['geval_goodsid']);
        $goods_model=Model('goods');
        $goods_info=$goods_model->getGoodsInfoByID($goods_id);
        if(empty($goods_info)){
            output_error('商品不存在');exit;
        }
        $goods_list=Model('goods')->getGoodsList(array('goods_commonid'=>$goods_info['goods_commonid']),'goods_id');
        $goods_ids=array();
        foreach($goods_list as $value){
            $goods_ids[]=$value['goods_id'];
        }
        $condition = array();
        $condition['geval_goodsid'] = array('in',implode(',',$goods_ids));
        $condition['geval_state'] = 0;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                break;
        }
        $evaluate=Model('evaluate_goods')->where($condition)->page($this->page)->order('geval_addtime desc')->select();
        if(!empty($evaluate)){
            $member_ids=array();
            foreach($evaluate as $key=>$value){
                $evaluate[$key]['geval_addtime']=date('Y-m-d H:i:s',$evaluate[$key]['geval_addtime']);
                $member_ids[]=$value['geval_frommemberid'];
                //评价图片
                $geval_images=array();
                if(!empty($value['geval_image'])){
                    foreach(explode(',',$value['geval_image']) as $value){
                        $geval_images[]=snsThumb($value);
                    }
                }
                $evaluate[$key]['images']=$geval_images;
            }
            //处理会员头像
            $member_info=Model('member')->getMemberList(array('member_id',array('in',implode(',',$member_ids))));
            $member_avata=array();
            if(!empty($member_info)){
                foreach($member_info as $key=>$value){
                    $member_avata[$value['member_id']]=$value['member_avatar'];
                }
            }
            foreach($evaluate as $key=>$value){
                if(isset($member_avata[$value['geval_frommemberid']])){
                    $evaluate[$key]['geval_frommemberavara']=getMemberAvatar($member_avata[$value['geval_frommemberid']]);
                }else{
                    $evaluate[$key]['geval_frommemberavara']=getMemberAvatar('');
                }
            }
        }
        output_data($evaluate);
    }


    /**
     * 商品评价列表 v2 带评价总数，店铺平均评分
     * page 一页显示商品数，可以不传
     * curpage 当前页数
     * geval_goodsid 商品id
     * type 0:全部1:好评，2:中评，3:差评
     * @notice 匿名评价功能先不要
     */
    public function get_evaluate_goods_list_v2Op(){
        $check_param=array('geval_goodsid');
        check_request_parameter($check_param);
        $type=isset($_REQUEST['type'])?intval($_REQUEST['type']):0;
        $goods_id=intval($_REQUEST['geval_goodsid']);
        $goods_model=Model('goods');
        $goods_info=$goods_model->getGoodsInfoByID($goods_id);
        if(empty($goods_info)){
            output_error('商品不存在');exit;
        }
        $goods_list=$goods_model->getGoodsList(array('goods_commonid'=>$goods_info['goods_commonid']),'goods_id');
        $goods_ids=array();
        foreach($goods_list as $value){
            $goods_ids[]=$value['goods_id'];
        }
        $condition = array();
        $condition['geval_goodsid'] = array('in',implode(',',$goods_ids));
        $condition['geval_state'] = 0;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                break;
        }
        $evaluate=Model('evaluate_goods')->where($condition)->page($this->page)->order('geval_addtime desc')->select();
        if(!empty($evaluate)){
            $member_ids=array();
            foreach($evaluate as $key=>$value){
                $evaluate[$key]['geval_addtime']=date('Y-m-d H:i:s',$evaluate[$key]['geval_addtime']);
                $member_ids[]=$value['geval_frommemberid'];
                //评价图片
                $geval_images=array();
                if(!empty($value['geval_image'])){
                    foreach(explode(',',$value['geval_image']) as $value){
                        $geval_images[]=snsThumb($value);
                    }
                }
                $evaluate[$key]['images']=$geval_images;
            }
            //处理会员头像
            $member_info=Model('member')->getMemberList(array('member_id'=>array('in',implode(',',$member_ids))));
            $member_avata=array();
            if(!empty($member_info)){
                foreach($member_info as $key=>$value){
                    $member_avata[$value['member_id']]=$value['member_avatar'];
                }
            }
            foreach($evaluate as $key=>$value){
                if(isset($member_avata[$value['geval_frommemberid']])){
                    $evaluate[$key]['geval_frommemberavara']=getMemberAvatar($member_avata[$value['geval_frommemberid']]);
                }else{
                    $evaluate[$key]['geval_frommemberavara']=getMemberAvatar('');
                }
                $evaluate[$key]['geval_frommemberavara']=qnyResizeHD($evaluate[$key]['geval_frommemberavara'],240);
            }
        }
        $average=Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsCommonID($goods_info['goods_commonid']);
        $simple_goods_info=array(
            'evaluation_good_star'=>$average['star_average'],
            'evaluation_count'=>$average['all'],
            'good_count'=>$average['good'],
            'normal_count'=>$average['normal'],
            'bad_count'=>$average['bad'],
        );
        $data=array(
            'goods_info'=>$simple_goods_info,
            'evaluate_list'=>$evaluate
        );
        output_data($data);
    }




    /**
     * 商品评价列表 v2 带评价总数，店铺平均评分
     * page 一页显示商品数，可以不传
     * curpage 当前页数
     * geval_goodsid 商品id
     * type 0:全部1:好评，2:中评，3:差评
     * @notice 匿名评价功能先不要
     */
    public function get_evaluate_store_listOP(){
        $type=isset($_REQUEST['type']) ? intval($_REQUEST['type']):0;
        $store_id=intval($_REQUEST['store_id']);
        $store_model=Model('store');
        $store_info=$store_model->getStoreInfoByID($store_id);
        if(empty($store_info)){
            output_error('店铺不存在');
        }

        $condition = array();
        $condition['geval_storeid'] = $store_id;
        $condition['geval_state'] = 0;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                break;
        }
        $evaluate=Model('evaluate_goods')->where($condition)->page($this->page)->order('geval_addtime desc')->select();

        if(!empty($evaluate)){
            $member_ids=array();
            foreach($evaluate as $key=>$value){
                $evaluate[$key]['geval_addtime']=date('Y-m-d H:i:s',$evaluate[$key]['geval_addtime']);
                $member_ids[]=$value['geval_frommemberid'];
                //评价图片
                $geval_images=array();
                if(!empty($value['geval_image'])){
                    foreach(explode(',',$value['geval_image']) as $value){
                        $geval_images[]=snsThumb($value);
                    }
                }
                $evaluate[$key]['images']=$geval_images;
            }
            //处理会员头像
            $member_info=Model('member')->getMemberList(array('member_id'=>array('in',implode(',',$member_ids))));
            $member_avata=array();
            if(!empty($member_info)){
                foreach($member_info as $key=>$value){
                    $member_avata[$value['member_id']]=$value['member_avatar'];
                }
            }
            foreach($evaluate as $key=>$value){
                if(isset($member_avata[$value['geval_frommemberid']])){
                    $evaluate[$key]['geval_frommemberavara']=getMemberAvatar($member_avata[$value['geval_frommemberid']]);
                }else{
                    $evaluate[$key]['geval_frommemberavara']=getMemberAvatar('');
                }
                $evaluate[$key]['geval_frommemberavara']=qnyResizeHD($evaluate[$key]['geval_frommemberavara'],240);
            }
        }

        $average=Model('evaluate_goods')->getEvaluateGoodsInfoByStoresCommonID($store_id);
        $evaluate_info=array(
            'evaluation_store_star'=>$average['star_average'],
            'evaluation_count'=>$average['all'],
        );
        $data=array(
            'evaluate_info'=>$evaluate_info,
            'evaluate_list'=>$evaluate
        );
        output_data($data);
    }
    //物流 接受订阅推送接口
    function  get_delivery_resultOp(){
        //订阅成功后，收到首次推送信息是在5~10分钟之间，在能被5分钟整除的时间点上，0分..5分..10分..15分....
        $param = $_REQUEST['param'];
        try{
            //$param包含了文档指定的信息，...这里保存您的快递信息,$param的格式与订阅时指定的格式一致
            $infoArray = json_decode(htmlspecialchars_decode($param),true);
            if(!empty($infoArray['lastResult'])){
                if(!empty($infoArray['lastResult']['data'])){
                    //根据物流单号获取订单id
                    $where['shipping_code'] = $infoArray['lastResult']['nu'];
                    $update['delivery_result'] = serialize($infoArray['lastResult']);
                    Model()->table('order')->where($where)->update($update);
                }
            }
            echo  '{"result":"true","returnCode":"200","message":"成功"}';
            //要返回成功（格式与订阅时指定的格式一致），不返回成功就代表失败，没有这个30分钟以后会重推
        } catch(Exception $e)
        {
            echo  '{"result":"false","returnCode":"500","message":"失败"}';
            //保存失败，返回失败信息，30分钟以后会重推
        }
    }
    //根据member_id 获取用户信息
    function  get_member_info_by_idOp(){
        $where['member_id'] = $_REQUEST['user_id'];
        $member_info = Model('member')->where($where)->field('member_avatar,member_truename,invitation')->find();
        if(!empty($member_info)){
            $member_info['member_avatar'] = getMemberAvatar($member_info['member_avatar']);
            output_data($member_info);
        }
        else{
            output_error('提交参数有误');
        }
    }
}