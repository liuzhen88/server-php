<?php
/**
 * 圈子主题模型
 * 
 * @author lijunhua
 * @since 2015-09-18
 */
defined('emall') or exit('Access Invalid!');

class circle_themeModel extends Model {

    public function __construct(){
        parent::__construct('circle_theme');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
    public function getCircleThemeList($condition = array(), $pagesize = '', $limit = '', $order = 'lastspeak_time desc,id desc') {
        return $this->where($condition)->order($order)->page($pagesize)->limit($limit)->select();
    }
    
    /**
     * 取数量
     * @param unknown $condition
     */
    public function getCircleThemeCount($condition = array()) {
        return $this->where($condition)->count();
    }
      
    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getCircleThemeInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delCircleTheme($condition = array()) {
        return $this->where($condition)->delete();
    }

  
    /**
     * 更新
     * @param unknown $data
     * @param unknown $condition
     */
    public function editCircleTheme($data = array(),$condition = array()) {
        return $this->where($condition)->update($data);
    }
    
    /*获取帖子
     2015年9月22日11:07:56
     xuping */
    public function getmemberAlltheme($condition=array(),$field='',$order = 'theme_addtime DESC'){
        return $this->where($condition)->page(10)->field($field)->order($order)->select();
    }
    
    /*修改帖子状态
     * xuping
     * 2015年10月16日13:52:53*/
    public function updateTheme($condition=array(),$data=array()){
           $this->where($condition)->update($data);
    }
    
    public function delTheme($condtion=array()){
        $this->where($condtion)->delete();
    }
    
    
}