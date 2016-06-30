<?php
namespace app\service;
/**
 * 支付及充值服务类
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;

class PayService extends BaseService {
    /**
     * wxUnifiedOrder
     * 调用微信统一下单接口，生成系统充值流水及微信预付订单
     * 流水单生成后需要在指定时间段内支付成功，否则将会取消
     * @param string $data['openid']  充值账户的openid
     * @param string $data['remark']: 充值流水描述信息
     * @param string $data['serialno']: 流水单号
     * @param number $data['total']: 支付金额，精确到“分”，必须是整数
     * @param number $data['expire']: 支付期限，以秒为单位
     */
    public function wxUnifiedOrder($data = []) {
        $data['start'] = date("YmdHis");
        $data['expire'] = date("YmdHis", time() + $data['expire']);
        return Yii::$app->wxpay->createOrder($data);
    }
}