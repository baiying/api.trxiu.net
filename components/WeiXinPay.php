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
     * 生成微信预付单
     * @param unknown $data
     * 必传参数：
     * openid: 充值账号openid
     * remark: 流水单描述信息
     * serialno: 流水单号
     * total: 支付金额，精确到“分”，必须是整数
     * start: 流水单生成时间
     * expire: 流水单过期时间
     * @return multitype:boolean string 成功时返回，其他抛异常 |multitype:boolean NULL
     */
    public function createOrder($data = []) {
        require "WxPay/lib/WxPay.Api.php";
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($data['remark']);
        $input->SetOut_trade_no($data['serialno']);
        $input->SetTotal_fee($data['total']);
        $input->SetTime_start($data['start']);
        $input->SetTime_expire($data['expire']);
        $input->SetGoods_tag("");
        $input->SetNotify_url($this->notifyUrl);
        $input->SetTrade_type("JSAPI");
        $input->SetProduct_id($data['serialno']);
        $input->SetOpenid($data['openid']);
        try{
            $result = \WxPayApi::unifiedOrder($input);
            if(isset($result['return_code']) && $result['return_code'] == 'SUCCESS') {
                if($result['result_code'] != "SUCCESS") {
                    return ['status'=>TRUE, 'message'=>$this->getErrorMsg($result['result_code']), 'data'=>$result];
                } else {
                    return ['status'=>TRUE, 'message'=>'OK', 'data'=>$result];
                }
                
            } else {
                return ['status'=>FALSE, 'message'=>$result['return_msg'], 'data'=>$result];
            }
            
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
    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     *
     * @return json数据，可直接填入js函数作为参数
     */
    public function getJsApiParameters($UnifiedOrderResult){
        require_once "WxPay/lib/WxPay.Data.php";
        $jsapi = new \WxPayJsApiPay();
        $jsapi->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(\WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign());
        $parameters = json_encode($jsapi->GetValues());
        return $parameters;
    }
    //查询订单是否支付成功
    public function Queryorder($transaction_id) {
        require_once "WxPay/lib/WxPay.Data.php";
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);
        if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS"){
            return true;
        }
        return false;
    }
    /**
     * 支付结果通知回调方法
     */
    public function notify() {
        require "WxPay/lib/WxPay.Notify.php";
        $notify = new \WxPayNotifyExtend();
        $notify->Handle(true);
    }
    /**
     * 获取生成预付单失败原因
     * @param unknown $result_code
     * @return string
     */
    private function getErrorMsg($result_code) {
        $dict = [
            'NOAUTH'                => '商户无此接口权限',
            'NOTENOUGH'             => '余额不足',
            'ORDERPAID'             => '商户订单已支付',
            'ORDERCLOSED'           => '订单已关闭',
            'SYSTEMERROR'           => '系统错误',
            'APPID_NOT_EXIST'       => 'APPID不存在',
            'MCHID_NOT_EXIST'       => 'MCHID不存在',
            'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
            'LACK_PARAMS'           => '缺少参数',
            'OUT_TRADE_NO_USED'     => '商户订单号重复',
            'SIGNERROR'             => '签名错误',
            'XML_FORMAT_ERROR'      => 'XML格式错误',
            'REQUIRE_POST_METHOD'   => '请使用post方法',
            'POST_DATA_EMPTY'       => 'post数据为空',
            'NOT_UTF8'              => '编码格式错误',
        ];
        return isset($dict[$result_code]) ? $dict[$result_code] : '未知错误';
    }
}