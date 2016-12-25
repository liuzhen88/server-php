<?php
/**
 * 关注
 */
defined('emall') or exit('Access Invalid!');

class followControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 消息推送测试代码，需删除
     */
     public function fxOp()
    {
        $member_id = $_GET['member_id'];
        $type = $_GET['type'];
        if ($type == 1) {
            QueueClient::push('jpush', array(
                'message' => '',
                'member_ids' => array(
                    $member_id
                ),
                'extend' => array(
                    'extras' => array(
                        'data' => array(
                            'message_type' => 'UPGRADE_FIRST_DISTRIBUTION',
                            'message_data' => array('distribution_goods_money' => 2888),
                        )
                    )
                )
            ));
        } elseif ($type == 2) {
            $jpush_data_result = array(
                'goods_id' => 10587,
                'goods_name' => '一件精品',
                'distribution_type' => 3,
                'distribution_money' => 40
            );
            QueueClient::push('jpush', array(
                'message' => '商品：分销返佣',
                'member_ids' => array(
                    $member_id
                ),
                'extend' => array(
                    'extras' => array(
                        'data' => array(
                            'message_type' => 'DISTRIBUTION_RECORD',
                            'message_data' => $jpush_data_result
                        )
                    )
                )
            ));
        }
    } 
}