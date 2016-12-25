<?php
/**
 * Created by PhpStorm.
 * User: 李熙宇
 * Date: 2016/1/29
 * Time: 9:16
 */

defined('emall') or exit('Access Invalid!');

class hot_search_leagueModel extends Model{
    public function __construct(){
        parent::__construct('hot_search_league');
    }

    /**
     * 记录搜索次数
     * @param $key 搜索关键字
     */
    public function counting($key){
        if(empty(trim($key))) return ;
        $token=$this->table('hot_search_league')->where(array('search_key'=>trim($key)))->find();
        if($token){
            $this->table('hot_search_league')->where(array('search_key'=>trim($key)))->update(array('count'=>array('exp','count+1')));
        }else{
            $this->table('hot_search_league')->insert(array('search_key'=>trim($key),'count'=>1));
        }
    }
}
