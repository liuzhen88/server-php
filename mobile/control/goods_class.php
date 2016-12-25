<?php
/**
 * 商品分类
 */

use Tpl;

defined('emall') or exit('Access Invalid!');
class goods_classControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

	public function indexOp() {
        $class_type=(isset($_REQUEST['class_type']) && 'local'==$_REQUEST['class_type'])?1:0;
        if(!empty($_REQUEST['gc_id']) && intval($_REQUEST['gc_id']) > 0) {
            $this->_get_class_list($_REQUEST['gc_id'],$class_type);
        } else {
            $this->_get_root_class($class_type);
        }
	}

    /**
     * 返回一级分类列表
     */
    private function _get_root_class($class_type=0) {
		$model_goods_class = Model('goods_class');
        $model_mb_category = Model('mb_category');

        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel($class_type);

		$class_list = $model_goods_class->getGoodsClassListByParentId(0,$class_type);
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach ($class_list as $key => $value) {
            if(!empty($mb_categroy[$value['gc_id']])) {
                //$class_list[$key]['image'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$value['gc_id']]['gc_thumb'];
                $temp_image=getQiniuyunimg(ATTACH_MOBILE.DS.'category',$mb_categroy[$value['gc_id']]['gc_thumb']);
                $class_list[$key]['image'] = qnyResizeHD($temp_image,200);
                
            } else {
                $class_list[$key]['image'] = '';
            }

            $class_list[$key]['text'] = '';
            $child_class_string = $goods_class_array[$value['gc_id']]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_list[$key]['text'] .= $goods_class_array[$child_class]['gc_name'] . '/';
            }
            $class_list[$key]['text'] = rtrim($class_list[$key]['text'], '/');
        }

        output_data(array('class_list' => $class_list));
    }

    /**
     * 根据分类编号返回下级分类列表
     */
    private function _get_class_list($gc_id,$class_type=0) {
        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $goods_class = $goods_class_array[$gc_id];

        if(empty($goods_class['child'])) {
            //无下级分类返回0
            output_data(array('class_list' => '0'));
        } else {
            //查找分类对应的图片

            $model_mb_category = Model('mb_category');
            $mb_categroy = $model_mb_category->getLinkList(array());
            $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
            //返回下级分类列表
            $class_list = array();
            $child_class_string = $goods_class_array[$gc_id]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_item = array();
                $class_id=$goods_class_array[$child_class]['gc_id'];
                if(!empty($mb_categroy[$class_id])) {
                   // $class_item['image'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$class_id]['gc_thumb'];
                    $temp_image=getQiniuyunimg(ATTACH_MOBILE.DS.'category',$mb_categroy[$class_id]['gc_thumb']);
                    $class_item['image'] = qnyResizeHD($temp_image,200);
                } else {
                    $class_item['image'] = '';
                }
                $class_item['gc_id'] .= $class_id;
                $class_item['gc_name'] .= $goods_class_array[$child_class]['gc_name'];
                $class_list[] = $class_item;
            }
            output_data(array('class_list' => $class_list));
        }
    }
}
