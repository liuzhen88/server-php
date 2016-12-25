<?php
/**
 * 分销商返佣记录表
 */
defined('emall') or exit('Access Invalid!');
class distribution_recordsModel extends Model{
    public function __construct() {
        parent::__construct('distribution_records');
    }
    public function addRecords($param, $members=array())
    {
        $insert_id = $this->table('distribution_records')->insert($param);
        if (!$insert_id) {
            throw new Exception();
        }
        $distribution_goods_money =  bcmul($param['goods_num'], $param['goods_price'], 2);  //分销的商品金额
        $distribution_money = 0;    //总共分销的商品总额
        //会员二级提升到一级分销商
        $upgrade_first_distribution = 0; //是否升级一级分销商
        if (in_array($param['distribution_type'], array(2, 3, 5)) && $members['is_distribution'] == 2 )
        {
            $distribution_money = bcadd($members['distribution_points'], $distribution_goods_money, 2);
            //$member_records = $this->getRecordsInfo(array('dis_member_id'=>$param['dis_member_id'], 'distribution_type'=>array('IN',array(3,5))), 'sum(goods_price*goods_num) as goods_price');
            //$distribution_money = isset($member_records['goods_price']) ? $member_records['goods_price'] : 0 ;
            if ($distribution_money >= UPGRADE_FIRST_DISTRIBUTION)
            {
                $upgrade_first_distribution = 1;
            }
        }
        //商户分销以及会员从商户分销，增加商户分销数量
        if (in_array($param['distribution_type'], array(1, 4))){
            $update = Model('distribution_goods')->where(array('store_id'=>$param['dis_store_id']))->update(array('goods_salenum'=>array('exp','goods_salenum+'.$param['goods_num'])));
            if (!$update) {
                throw new Exception('操作失败');
            }
        }
        
        $pd_log_data = array(
            'member_id' => $param['dis_member_id'],
            'member_name' => $param['dis_member_name'],
            'amount' => $param['distribution_money'],
            'distribution_goods_money' => $distribution_goods_money,
            'order_sn'=>'商品：'.$param['goods_name'].'分销返佣',
            'upgrade_first_distribution' => $upgrade_first_distribution,
            'is_store_distribution' => in_array($param['distribution_type'], array(1 ,4 )) ? 1 : 2,
            'store_id' => $param['dis_store_id'],
            'lg_mark' => array(
                'order_sn' => $param['order_sn'],
            ),
        );
        Model('predeposit')->changePd('distribution_get', $pd_log_data);
        $jpush_data_result = array(
            'goods_id' => $param['goods_id'],
            'goods_name' => $param['goods_name'],
            'distribution_type' => $param['distribution_type'],
            'distribution_money' => $param['distribution_money'],
        );
        //分销返佣暂时不极光推送
        /* QueueClient::push('jpush', array(
            'message'=>'商品：'.$param['goods_name'].'分销返佣',
            'member_ids'=>array($param['dis_member_id']),
            'extend'=>array(
                'extras'=>array(
                    'data' => array(
                    'message_type' => 'DISTRIBUTION_RECORD',
                    'message_data'=>$jpush_data_result,
                    ),
                ),
            )
          )
        ); */
        if ($upgrade_first_distribution == 1)
        {
            QueueClient::push('jpush', array(
                'message'=>'恭喜您成为一级分销商',
                'member_ids'=>array($param['dis_member_id']),
                'extend'=>array(
                    'extras'=>array(
                        'data' => array(
                            'message_type' => 'UPGRADE_FIRST_DISTRIBUTION',
                            'message_data'=>array('distribution_goods_money' => $distribution_money),
                        ),
                    ),
                 )
                )
            );
        }
        return $insert_id;
    }
    
    public function getRecordsInfo($condition, $field='*') {
        $list = $this->field($field)->where($condition)->find();
        return $list;
    }


    /**
     * 获取分销金额详情前一天所有数据
     * @return [type] [description]
     */
    public function getRecordsAll(){
        $rt_time = $this->table('distribution_statis')->order('id desc')->limit(1)->find();
        if(count($rt_time)>0)
            $time = date("Y-m-d",strtotime($rt_time['di_day']));
        else
            $time = '2015-10-01';

            $rtAll  =   $this->table('distribution_records')->field('dis_member_id,goods_name,dis_store_id,store_id,dis_store_id,distribution_money,distribution_type,first_price,second_price,left(from_unixtime(add_time),10) as ymd,left(from_unixtime(add_time),7) as ym,left(from_unixtime(add_time),4) as y')
                        ->table('distribution_records')
                        ->where("'".$time."' < left(from_unixtime(add_time),10)  and left(from_unixtime(add_time),10) <= '".date('Y-m-d',strtotime('-1 day'))."'")
                        //->group('dis_member_id')
                        ->select();
                       // echo $this->_sql(); 
                        //exit();
        return $rtAll;
    }


    /**
     * functionname   : getRecords
     * author         : xuping
     * @param $array
     * @param $field
     * @param int $page
     * @return array|mixed|null
     */
    public function getRecords($array,$field,$page=10){
        $conut=$this->where($array)->sum('distribution_money');
        $list = $this->field($field)->where($array)->page($page)->group('goods_id')->select();
        $res['list']=$list;
        $res['count']=$conut;
        return $res;
    }


    public function getRecord($array,$field){
        $list = $this->table('distribution_records')->field($field)->where($array)->find();
        return $list;
    }


    /**
     * functionname   : getRecords
     * author         : xuping
     * @param $array
     * @param $field
     * @param int $page
     * @return array|mixed|null
     *
     */
    public function getRecords_list($array,$field){
        $on='order_goods.order_id=distribution_records.order_id';
        $list = $this->table('distribution_records,order_goods')->field($field)->join('inner left')->on($on)->where($array)->select();
        return $list;
    }
}