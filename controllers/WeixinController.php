<?php
namespace app\controllers;

use Yii;
use app\controllers\NoAuthBaseController;
use app\components\ApiCode;
use app\service\ChargeSerialService;
use app\service\PayService;

class WeixinController extends NoAuthBaseController {
    /**
     * 验证token
     */
    public function actionCheckToken() {
        $signature = Yii::$app->request->get('signature');
        $timestamp = Yii::$app->request->get('timestamp');
        $nonce = Yii::$app->request->get('nonce');
        $echostr = Yii::$app->request->get('echostr');
        
        $token = Yii::$app->weixin->getToken();
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            echo $echostr;
        }else{
            echo "";
        }
    }
    /**
     * get-oauth-redirect-url
     * oauth 授权跳转接口
     * @param string $redirect_url  授权后重定向的回调链接地址
     * @param string $state         重定向后会带上state参数
     * @param string $scope         网页授权方式，取值：snsapi_base, snsapi_userinfo
     * @return array
     */
    public function actionGetOauthRedirectUrl() {
        $this->checkMethod('get');
        $rule = [
            'redirect_url'  => ['type'=>'string', 'required'=>true, 'default'=>''],
            'state'         => ['type'=>'string', 'required'=>true, 'default'=>''],
            'scope'         => ['type'=>'string', 'required'=>false, 'default'=>'snsapi_userinfo']
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $authUrl = Yii::$app->weixin->getOauthRedirect($args['redirect_url'], $args['state'], $args['scope']);
        $this->renderJson(ApiCode::SUCCESS, '授权跳转地址获取成功', ['authUrl'=>$authUrl]);
    }
    /**
     * oauth-access-token
     * oauth授权回调地址
     * @param string $code  授权回调编码
     * @return array
     */
    public function actionOauthAccessToken() {
        $this->checkMethod('get');
        $rule = [
            'code' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $accessToken = Yii::$app->weixin->getOauthAccessToken($args['code']);
        if($accessToken === FALSE) {
            $this->renderJson(ApiCode::ERROR_API_FAILED, '获取用户access_token失败');
        } else {
            $this->renderJson(ApiCode::SUCCESS, 'access_token获取成功', $accessToken);
        }
    }
    /**
     * refresh-access-token
     * 刷新access token并续期
     * @param string $refresh_token  刷新token
     * @return array
     */
    public function actionRefreshAccessToken() {
        $this->checkMethod('post');
        $rule = [
            'refresh_token' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $accessToken = Yii::$app->weixin->getOauthRefreshToken($args['refresh_token']);
        $this->renderJson(ApiCode::SUCCESS, 'access_token刷新成功', $accessToken);
    }
    /**
     * oauth-user-info
     * 获取微信授权用户信息
     * @param string $access_token  access_token
     * @param string $openid        用户openid
     * @return array
     */
    public function actionOauthUserInfo() {
        $this->checkMethod('get');
        $rule = [
            'access_token'  => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'openid'        => ['type' => 'string', 'required' => TRUE, 'default' => '']
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $userInfo = Yii::$app->weixin->getOauthUserinfo($args['access_token'], $args['openid']);
        $this->renderJson(ApiCode::SUCCESS, '用户信息获取成功', $userInfo);
    }
    /**
     * 获取微信jsapi签名
     * @param string $url   请求页面的地址
     */
    public function actionJsSign() {
        $this->checkMethod('post');
        $url = urldecode(Yii::$app->request->post('url'));
        $res = Yii::$app->weixin->getJsSign($url);
        $this->renderJson(ApiCode::SUCCESS, "OK", $res);
    }
    /**
     * unified-order
     * 微信统一下单接口，生成系统充值流水及微信预付订单
     * 流水单生成后需要在指定时间段内支付成功，否则将会取消
     * @param int $data['fans_id']      流水单所属用户ID
     * @param int $data['ballot_id']    流水单关联活动ID
     * @param int $data['anchor_id']    流水单关联主播ID
     * @param int $data['openid']      充值微信账号的openid
     * @param int $data['total']        充值金额，“分”为单位
     * @param int $data['status']       流水单状态，1 等待支付结果，2 支付成功，3 支付失败，4 超时未支付
     * @param int $data['type']         流水单类型，1 主播拉票
     * @param int $data['expirt']       支付超时时间，默认1小时
     */
    public function actionUnifiedOrder() {
        $this->checkMethod('post');
        $rule = [
            'fans_id'   => ['type'=>'int', 'required'=>true],
            'ballot_id' => ['type'=>'int', 'required'=>false, 'default'=>0],
            'anchor_id' => ['type'=>'int', 'required'=>false, 'default'=>0],
            'openid'    => ['type'=>'string', 'required'=>true],
            'total'     => ['type'=>'int', 'required'=>true],
            'status'    => ['type'=>'int', 'required'=>false, 'default'=>1],
            'type'      => ['type'=>'int', 'required'=>false, 'default'=>1],
            'expire'    => ['type'=>'int', 'required'=>false, 'default'=>3600],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 生成充值流水单
            $chargeService = new ChargeSerialService();
            $res = $chargeService->create($args);
            if($res['status']) {
                $serialno = $res['data']['serialno'];
                // 调用微信统一下单API
                $payService = new PayService();
                $res = $payService->wxUnifiedOrder([
                    'openid'    => $args['openid'],
                    'remark'    => '主播拉票充值',
                    'serialno'  => $serialno,
                    'total'     => $args['total'],
                    'expire'    => $args['expire']
                ]);
                if($res['status']) {
                    // 将微信服务器返回数据更新到流水记录中
                    $resUpd = $chargeService->update($serialno, $res['data']);
                    if($resUpd['status']) {
                        $trans->commit();
                        if($res['data']['result_code'] == "SUCCESS") {
                            $jsApiArgs = Yii::$app->wxpay->getJsApiParameters($res['data']);
                            $this->renderJson(ApiCode::SUCCESS, '预付单生成成功', $jsApiArgs);
                            
                        } else {
                            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
                        }
                        
                    } else {
                        $trans->rollBack();
                        $this->renderJson(ApiCode::ERROR_API_FAILED, $resUpd['message']);
                    }
                    
                } else {
                    $trans->rollBack();
                    $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
                }
                
            } else {
                $trans->rollBack();
                $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
            }
            
        } catch(Exception $e) {
            $trans->rollBack();
            $this->renderJson(ApiCode::ERROR_API_FAILED, $e->getMessage());
        }
    }
    /**
     * notify
     * 微信支付结果异步通知处理接口
     */
    public function actionNotify() {
        Yii::$app->wxpay->notify();
    }
    /**
     * query-pay-result
     * 查询微信支付是否成功
     * 以下两个查询参数提供任一即可
     * @param string $args['transaction_id']        微信订单号
     * @param string $args['out_trade_no']          商户订单号
     */
    public function actionQueryPayResult() {
        $this->checkMethod('get');
        $rule = [
            'transaction_id' => ['type'=>'string', 'required'=>FALSE],
            'out_trade_no'   => ['type'=>'string', 'required'=>FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        if(!isset($args['transaction_id']) && !isset($args['out_trade_no'])) {
            $this->renderJson(ApiCode::ERROR_API_FAILED, '微信订单号和商户订单号至少要提供其一');
        }
        $service = new PayService();
        $res = $service->wxQueryResult($args);
        if($res) {
            $this->renderJson(ApiCode::SUCCESS, '支付成功');
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, '支付失败');
        }
    }
}