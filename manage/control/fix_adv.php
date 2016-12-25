<?php
/**
 * 广告管理
 *
 ***/

defined('emall') or exit('Access Invalid!');

class fix_advControl extends SystemControl{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 广告管理列表
	 *  
	 */
	public function indexOp(){
            $adv = Model('fix_adv');
            $channel = array(
                "0"=>"全部",
                "1"=>"IOS",
                "2"=>"安卓",
                "3"=>"微信",
                "4"=>"PC端"
            );
            $model_area = Model('area');
            $citylist = $model_area->field('area_id,area_name')->getCityInfo(array('area_deep'=>'2'));
            Tpl::output('citylist', $citylist);
            $where = array();
            if ($_POST['cityids'] != ''&&$_POST['cityids'] != -1) {
                $where['adv_cityids'] = array('like', '%,' . $_POST['cityids'] . ',%');
            }
             Tpl::output('cityids', $_POST['cityids']);
             if ($_POST['adv_channel'] != '') {
                $where['adv_channel'] = array('like', '%' .$_POST['adv_channel'] . '%');
                Tpl::output('adv_channel', $_POST['adv_channel']);
            }
            if ($_POST['adv_limit_area'] != '') {
                $where['adv_limit_area'] = $_POST['adv_limit_area'];
                Tpl::output('adv_limit_area', $_POST['adv_limit_area']);
            }
            $page	 = new Page();
	    $page->setEachNum(10);
	    $page->setStyle('admin');
            $result=$adv->getAdvList($where,$page);
            Tpl::output('page',$adv->showpage());
            Tpl::output('fix_adv', $result);  
            Tpl::output('channel', $channel);  
            Tpl::showpage('fix_adv.index');
	}
        /**
         * 广告预添加页面
         */
    public function preAddOp(){
        if(!chksubmit()){
            Tpl::showpage('fix_adv.index.add');
        }else{
            $adv_param	= array();
            $_POST['adv_title'] = trim($_POST['adv_title']);
            if (empty($_POST['adv_title'])||empty($_POST['adv_channel'])||empty($_POST['adv_type'])
                ||empty($_POST['adv_start_date'])||empty($_POST['adv_end_date'])||empty($_FILES['adv_image_upload']['name'])) {
                showMessage('参数输入不全');
            }
            $adv_param['adv_title'] = $_POST['adv_title'];
            $adv_param['adv_order'] = $_POST['adv_order']==0?1000:$_POST['adv_order'];
            $adv_param['adv_channel'] = implode(',',$_POST['adv_channel']);
            $adv_param['adv_type'] = $_POST['adv_type'];
            $adv_param['adv_start_date'] = strtotime($_POST['adv_start_date']);
            $adv_param['adv_end_date'] = strtotime($_POST['adv_end_date']);
            $adv_param['adv_link'] = $_POST['adv_link'];
            $adv_limit_area = $_POST['adv_limit_area'];
            if($adv_limit_area==1){
                if (empty($_POST['adv_areainfo'])) {
                    showMessage('参数输入不全');
                }else{
                    $adv_param['adv_areainfo'] = str_replace('&quot;','"',$_POST['adv_areainfo']);
                    $city = str_replace('&quot;','',$_POST['adv_cityids']);
                    $city = str_replace('[',',',$city);
                    $city = str_replace(']',',',$city);
                    $adv_param['adv_cityids'] = $city;
                    $area_ids = str_replace('&quot;','',$_POST['adv_areaids']);
                    $area_ids = str_replace('[',',',$area_ids);
                    $area_ids = str_replace(']',',',$area_ids);
                    $adv_param['adv_areaids'] = $area_ids;
                    $adv_cityids_search = str_replace('&quot;','',$_POST['adv_cityids_search']);
                    $adv_cityids_search = str_replace('[',',',$adv_cityids_search);
                    $adv_cityids_search = str_replace(']',',',$adv_cityids_search);
                    $adv_param['adv_cityids_search'] = $adv_cityids_search;
                }
            }
            $adv_param['adv_areainfo'] =  $adv_param['adv_areainfo']==null?'""': $adv_param['adv_areainfo'];
            $adv_param['adv_status'] = 1;
            $adv_param['adv_limit_area'] = $adv_limit_area;
            $adv_param['adv_link'] = $_POST['adv_link'];
            $adv_param['adv_add_date'] = TIMESTAMP;
            if (!empty($_FILES['adv_image_upload']['name'])) {
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_ADV);
                $result = $upload->upfile('adv_image_upload');
                if ($result) {
                    $adv_param['adv_pic_path'] = $upload->file_name;
//                        $adv_param['adv_pic_path'] = $this->attachment_path;
                    $adv = Model('fix_adv');
                    $adv->addAdv($adv_param);
                    showMessage('广告添加成功','index.php?act=fix_adv&op=index');
                }else {
                    showMessage('图片上传失败');
                }
            }
            showMessage('广告添加成功','index.php?act=fix_adv&op=index');
        }

    }
        /**
         * 广告修改页面
         */
        public function preEditOp(){
                Tpl::output('fix_adv', Model('fix_adv')->select($_POST['key_id']));  
                Tpl::showpage('fix_adv.index.edit'); 
        }
        /**
         * 修改
         */
        public function editOp(){
               $adv_param = array(); 
               $adv_param['id'] = $_POST['id'];
               if (empty($_POST['adv_title'])||empty($_POST['adv_channel'])||empty($_POST['adv_type'])
                      ||empty($_POST['id'])||empty($_POST['adv_start_date'])||empty($_POST['adv_end_date'])) {
                        showMessage('参数输入不全');
                     }
                    $adv_param['adv_title'] = $_POST['adv_title'];
                    $adv_param['adv_order'] = $_POST['adv_order']==0?1000:$_POST['adv_order'];
                    $adv_param['adv_channel'] = implode(',',$_POST['adv_channel']);
                    $adv_param['adv_type'] = $_POST['adv_type'];
                    $adv_param['adv_start_date'] = strtotime($_POST['adv_start_date']);
                    $adv_param['adv_end_date'] = strtotime($_POST['adv_end_date']);
                    $adv_param['adv_link'] = $_POST['adv_link'];
                    $adv_limit_area = $_POST['adv_limit_area'];

                      if($adv_limit_area==1){
                            if (empty($_POST['adv_areainfo'])) {
                               showMessage('参数输入不全');
                            }else{
                                  $adv_param['adv_areainfo'] = str_replace('&quot;','"',$_POST['adv_areainfo']);
                                  $city = str_replace('&quot;','',$_POST['adv_cityids']);
                                  $city = str_replace('[',',',$city);
                                  $city = str_replace(']',',',$city);
                                  $adv_param['adv_cityids'] = $city;
                                  $area_ids = str_replace('&quot;','',$_POST['adv_areaids']);
                                  $area_ids = str_replace('[',',',$area_ids);
                                  $area_ids = str_replace(']',',',$area_ids);
                                  $adv_param['adv_areaids'] = $area_ids;
                                  $adv_cityids_search = str_replace('&quot;','',$_POST['adv_cityids_search']);
                                  $adv_cityids_search = str_replace('[',',',$adv_cityids_search);
                                  $adv_cityids_search = str_replace(']',',',$adv_cityids_search);
                                  $adv_param['adv_cityids_search'] = $adv_cityids_search;
                            }
                      }
                    $adv_param['adv_areainfo'] =  $adv_param['adv_areainfo']==null?'"" ': $adv_param['adv_areainfo'];
                    $adv_param['adv_limit_area'] = $adv_limit_area;
                    $adv_param['adv_status'] = $_POST['adv_status'];
                    $adv_param['adv_link'] = $_POST['adv_link'];
                    $adv_param['adv_add_date'] = TIMESTAMP;
                    if (!empty($_FILES['adv_image_upload']['name'])) {
                        $upload = new UploadFile();
                        $upload->set('default_dir',ATTACH_ADV);
                        $result = $upload->upfile('adv_image_upload');
//                        file_put_contents('log.log', json_encode($result));
                        if ($result) {
                            $adv_param['adv_pic_path'] = $upload->file_name;
                            $adv = Model('fix_adv');
                            $adv->update_($adv_param);
                            showMessage('广告修改成功','index.php?act=fix_adv&op=index');
                        }else {
                            showMessage('图片上传失败');
                        }
                    }
                     $adv = Model('fix_adv');
                     $adv->update_($adv_param);
                     showMessage('广告修改成功','index.php?act=fix_adv&op=index');
        }
        /**
         * 删除广告
         *  $oper_type  1 启用 2 停用 3 删除
         */
        public function oper_advOP(){
            $oper_type = intval(trim($_POST['oper_type']));
            $ids=trim($_POST['advIds']);
            if(empty($oper_type)||empty($ids)){
                showMessage('未选中任何项！');
            }
            $resl = "";
            $adv = Model('fix_adv');
//            $ids_str = implode("','",$ids);
            switch ($oper_type) {
                case 1: 
                    $result = $adv->updateAdv(array('adv_status'=>1),array('adv_id_in'=>$ids));
                    $result = "启用";
                    break;
                case 2:
                    $result = $adv->updateAdv(array('adv_status'=>2),array('adv_id_in'=>$ids));
                    $result = "停用";
                    break;
                case 3:
                    $result = $adv->delAdv(array('adv_id_in'=>$ids));
                    $result = "删除";
                    break;
                default:
                    break;
            }
            if($result){
                showMessage('广告'.$result.'成功','index.php?act=fix_adv&op=index');
            }else{
                showMessage('广告'.$result.'失败');
            }
        }
                
}
