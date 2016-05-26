<?php
namespace app\controllers;

use Yii;
use app\controllers\NoAuthBaseController;
use app\components\ApiCode;

class WeixinController extends NoAuthBaseController {
    /**
     * get-oauth-redirect
     * oauth 授权跳转接口
     * @param string $redirect_url  授权后重定向的回调链接地址
     * @param string $state         重定向后会带上state参数
     * @return array
     */
    public function actionGetOauthRedirect() {
        $authUrl = Yii::$app->weixin->getOauthRedirect('http://e.cheweixiu.com/wx/notify/hub/', 'callbackstate');
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
}