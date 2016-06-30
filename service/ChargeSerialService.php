<?php
namespace app\service;
/**
 * 充值流水服务类
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;

class ChargeSerialService extends BaseService {
    /**
     * create
     * 创建流水单
     * 
     * @param int $data['fans_id']      流水单所属用户ID
     * @param int $data['ballot_id']    流水单关联活动ID
     * @param int $data['anchor_id']    流水单关联主播ID
     * @param int $data['open_id']      充值微信账号的openid
     * @param int $data['total']        充值金额，“分”为单位
     * @param int $data['status']       流水单状态，1 等待支付结果，2 支付成功，3 支付失败，4 超时未支付
     * @param int $data['type']         流水单类型，1 主播拉票
     */
    public function create($data = []) {
        $rule = [
            'fans_id'   => ['type'=>'int', 'required'=>true],
            'ballot_id' => ['type'=>'int', 'required'=>false, 'default'=>0],
            'anchor_id' => ['type'=>'int', 'required'=>false, 'default'=>0],
            'open_id'   => ['type'=>'int', 'required'=>true],
            'total'     => ['type'=>'int', 'required'=>true],
            'status'    => ['type'=>'int', 'required'=>false, 'default'=>1],
            'type'      => ['type'=>'int', 'required'=>false, 'default'=>1],
        ];
        $data['serialno'] = Yii::$app->utils->createID(Yii::$app->id);
        $data['create_time'] = time();
        $curd = new CurdService();
        return $curd->createRecord("app\models\ChargeSerial", $data);
    }
    /**
     * update
     * 修改流水单中数据
     * @param string $serialno  流水单号
     * @param array $data       修改数据数组
     */
    public function update($serialno, $data = []) {
        $curd = new CurdService();
        return $curd->updateRecord("app\models\ChargeSerial", ['serialno'=>$serialno], $data);
    }
}