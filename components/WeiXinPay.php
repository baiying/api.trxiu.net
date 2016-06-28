<?php
namespace app\components;

use Yii;
use yii\base\Component;
/**
 * 微信支付组件
 * @author bai
 *
 */
class WeiXinPay extends Component{
    
    public $notifyUrl = "";
    
    function __construct($config = []) {
        $this->notifyUrl = $config['notifyUrl'];
    }
    /**
     * 生成支付链接
     * @param unknown $data
     * 必传参数：
     * remark: 流水单描述信息
     * serialno: 流水单号
     * total: 支付金额，精确到“分”，必须是整数
     * start: 流水单生成时间
     * expire: 流水单过期时间
     * @return multitype:boolean string 成功时返回，其他抛异常 |multitype:boolean NULL
     */
    public function getCodeUrl($data = []) {
        require "WxPay/lib/WxPay.Api.php";
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($data['remark']);
        $input->SetAttach($data['serialno']);
        $input->SetOut_trade_no($data['serialno']);
        $input->SetTotal_fee($data['total']);
        $input->SetTime_start($data['start']);
        $input->SetTime_expire($data['expire']);
        $input->SetGoods_tag("");
        $input->SetNotify_url($this->notifyUrl);
        $input->SetTrade_type("JSAPI");
        $input->SetProduct_id($data['seqno']);
        try{
            $result = \WxPayApi::unifiedOrder($input);
            if(isset($result['err_code']) && $result['err_code'] != '') {
                return ['status'=>FALSE, 'message'=>$result['err_code'] . ", " . $result['err_code_des']];
            }
            $url = urlencode($result['code_url']);
            return ['status'=>TRUE, 'message'=>'支付链接生成成功', 'data'=>$url];
        } catch (\WxPayException $e) {
            return ['status'=>FALSE, 'message'=>$e->errorMessage()];
        }
    }
    /**
     * 支付结果通知回调方法
     */
    public function notify() {
        require "WxPay/lib/WxPay.Notify.Extend.php";
        $notify = new \WxPayNotifyExtend();
        $notify->Handle(true);
    }
}