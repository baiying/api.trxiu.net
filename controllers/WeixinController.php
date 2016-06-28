<?php
namespace app\controllers;

use Yii;
use app\controllers\NoAuthBaseController;
use app\components\ApiCode;

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
        $this->renderJson(ApiCode::SUCCESS, 'access_token获取成功', $accessToken);
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
        $this->checkMethod('get');
        $rule = [
            'url' => ['type'=>'string', 'required'=>true]
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $res = Yii::$app->weixin->getJsSign($args['url']);
        $this->renderJson(ApiCode::SUCCESS, "OK", $res);
    }
}