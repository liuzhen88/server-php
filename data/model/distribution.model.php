<?php
/**
 * 分销相关功能
 */
defined('emall') or exit('Access Invalid!');

class distributionModel extends Model{
    public function __construct()
    {
        parent::__construct('goods_common_distribution');
    }

    /**
     * 分销商品列表
     * @param $condition
     * @param $page
     * @param string $field
     * @return mixed
     */
    public function get_goods_list($condition,$page=10,$order='goods_common.goods_commonid desc',$field='*,goods_common.goods_name as goods_official_name,goods_common.goods_image as goods_official_image'){
        $on='goods_common_distribution.goods_commonid=goods_common.goods_commonid,goods_common.goods_commonid=goods.goods_commonid,store.store_id=goods_common.store_id';
        return $this->table('goods_common_distribution,goods_common,goods,store')->join('inner,inner,inner')->on($on)->order($order)->field($field)->page($page)->where($condition)->select(array('group'=>'goods_common.goods_commonid'));
    }

    /**
     * 计算总页数,(上面方法关联查询goods，导致数量不对，分页总数不对)
     */
    public function count_goods_list($condition){
        $on='goods_common_distribution.goods_commonid=goods_common.goods_commonid,store.store_id=goods_common.store_id';
        return $this->table('goods_common_distribution,goods_common,store')->join('inner,inner')->on($on)->field('count(*) as total')->where($condition)->find();
    }

    //商户后台首页分销列表
    public function get_goods_list_in_home(){
        $on='goods_common_distribution.goods_commonid=goods_common.goods_commonid,goods_common.goods_commonid=goods.goods_commonid';
        $field='goods_common.is_distribution,goods_common.goods_name as goods_official_name,goods_common.goods_image as goods_official_image,goods_common.store_id,goods_common_distribution.*';
        $where['goods_common.is_distribution']=1;
        $where['goods_common.goods_state']=1;
        return $this->table('goods_common_distribution,goods_common,goods')->join('inner')->on($on)->where($where)->field($field)->page(8)->order('goods.goods_salenum desc')->select(array('group'=>'goods_common.goods_commonid'));
    }

    public function get_goods_info($condition){
        return $this->table('goods_distribution')->where($condition)->find();
    }

    public function get_distribute_goods_info($condition){
        return $this->table('distribution_goods')->where($condition)->find();
    }

    /**
     * 获取待分销数据
     * @param $condition
     */
    public function getWaitDistribution($condition,$params = array())
    {
        $on = "goods.goods_commonid =  goods_common.goods_commonid";
        $params['order'] = array();
        $sort = array(
            'price'=>'goods_common.goods_price',
            'hot'=>'goods.goods_salenum',
        );

//        排序
        foreach ($sort as $index => $item) {
            $sort = null;
            if(isset($_REQUEST[$index])){
                switch( $_REQUEST[$index]){
                    case '1':
                        $sort = "desc";
                        break;
                    case '0':
                        $sort = "asc";
                        break;
                }
                if(isset($sort)){
                    $params['order'][] = $item." ".$sort;
                }

            }
        }
        $params['order'][] = 'goods_common.goods_commonid desc';
        $order = implode(',',$params['order']);

        $limit = getLimit();
        $params['field'] = 'goods.goods_salenum
        ,goods_common.goods_commonid goods_id,goods_common.goods_image,goods_common.goods_price,goods_common.goods_name,goods_common.store_id
        ';
//        $params['field'] = '';
        $condition['goods_common.is_distribution']=1;
        $condition['goods_common.goods_state']=1;
        $model = $this->table('goods,goods_common')->join('inner')->on($on);
        if(isset($params['page'])) {
            $temp =  $model->field('count(1) as total')->where($condition)->find();
            $result['count'] = $temp['total'];
        }
        $model = $this->table('goods,goods_common')->join('inner')->on($on);

        $result['data'] = $model->order($order)->field($params['field'])->where($condition)->limit($limit)->select();
        return $result;
    }

    /**
     * functionname   : 获取首页 推荐的热门分销商品
     * author         : xuping
     */
    public function get_disgoods_list(){
        $condition['is_recommend']=1;
        $result=$this->table('goods_distribution')->where($condition)->order('id DESC')->limit(4)->select();
        $goods_model=Model('goods');
        $field='goods_id,goods_commonid,goods_name,goods_image,goods_price';
        foreach($result as $key=>$value){
            $res[]=$goods_model->getGoodsInfoByID($value['goods_id'],$field);
        }
        foreach($res as $k=>$v){
            $res[$k]['goods_image']=cthumb($v['goods_image'],'',$v['store_id'],'-recommend');
        }
        return $res;
    }
























}
