<?php
namespace app\components;

use Yii;
use yii\base\Component;
/**
 * 微信接口类
 * @author bai
 */
class Weixin extends Component {
    
    const OAUTH_PREFIX              = 'https://open.weixin.qq.com/connect/oauth2';
	const OAUTH_AUTHORIZE_URL       = '/authorize?';
    const API_BASE_URL_PREFIX       = 'https://api.weixin.qq.com';
    const OAUTH_TOKEN_URL           = '/sns/oauth2/access_token?';
    const OAUTH_REFRESH_URL         = '/sns/oauth2/refresh_token?';
    const OAUTH_USERINFO_URL        = '/sns/userinfo?';
    const OAUTH_AUTH_URL            = '/sns/auth?';
    const API_URL_PREFIX            = 'https://api.weixin.qq.com/cgi-bin';
    const AUTH_URL                  = '/token?grant_type=client_credential&';
    const GET_TICKET_URL            = '/ticket/getticket?';
    
    private $token;
    private $encodingAesKey;
    private $appid;
    private $appsecret;
    private $access_token;
    private $user_token;
    private $jsapi_ticket;
    private $api_ticket;
    
    public  $errCode = 40001;
    public  $errMsg = "no access";
    
    public function __construct($config = []) {
        $this->appid            = $config['appid'];
        $this->appsecret        = $config['appsecret'];
        $this->encodingAesKey   = $config['encodingAesKey'];
        $this->token            = $config['token'];
    }
    
    /**
     * GET 请求
     * @param string $url
     */
    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * 生成随机字串
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    public function generateNonceStr($length=16){
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++){
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
    	return $str;
    }
    /**
     * 获取签名
     * @param array $arrdata 签名数组
     * @param string $method 签名方法
     * @return boolean|string 签名值
     */
    public function getSignature($arrdata,$method="sha1") {
        if (!function_exists($method)) return false;
        ksort($arrdata);
        $paramstring = "";
        foreach($arrdata as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        $Sign = $method($paramstring);
        return $Sign;
    }
    /**
     * 获取自定义token
     * @return array
     */
    public function getToken() {
        return $this->token;
    }
    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOauthRedirect($callback, $state='', $scope='snsapi_userinfo'){
        return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }
    /**
     * 通过code获取Access Token
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOauthAccessToken($code){
        if (!$code) return false;
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }
    
    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return boolean|mixed
     */
    public function getOauthRefreshToken($refresh_token){
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_REFRESH_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }
    
    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserinfo($access_token,$openid){
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    
    /**
     * 检验授权凭证是否有效
     * @param string $access_token
     * @param string $openid
     * @return boolean 是否有效
     */
    public function getOauthAuth($access_token,$openid){
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_AUTH_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            } else
                if ($json['errcode']==0) return true;
        }
        return false;
    }
    
    /**
	 * 获取JSAPI授权TICKET
	 * @param string $appid 用于多个appid时使用,可空
	 * @param string $jsapi_ticket 手动指定jsapi_ticket，非必要情况不建议用
	 */
	public function getJsTicket($appid='',$jsapi_ticket=''){
		if (!$this->access_token && !$this->checkAuth()) return false;
		if (!$appid) $appid = $this->appid;
		if ($jsapi_ticket) { //手动指定token，优先使用
		    $this->jsapi_ticket = $jsapi_ticket;
		    return $this->jsapi_ticket;
		}
		/*
		$authname = 'wechat_jsapi_ticket'.$appid;
		if ($rs = $this->getCache($authname))  {
			$this->jsapi_ticket = $rs;
			return $rs;
		}
		*/
		$result = $this->http_get(self::API_URL_PREFIX.self::GET_TICKET_URL.'access_token='.$this->access_token.'&type=jsapi');
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->jsapi_ticket = $json['ticket'];
// 			$expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
// 			$this->setCache($authname,$this->jsapi_ticket,$expire);
			return $this->jsapi_ticket;
		}
		return false;
	}
	
	/**
	 * 获取JsApi使用签名
	 * @param string $url 网页的URL，自动处理#及其后面部分
	 * @param string $timestamp 当前时间戳 (为空则自动生成)
	 * @param string $noncestr 随机串 (为空则自动生成)
	 * @param string $appid 用于多个appid时使用,可空
	 * @return array|bool 返回签名字串
	 */
	public function getJsSign($url, $timestamp=0, $noncestr='', $appid=''){
	    if (!$this->jsapi_ticket && !$this->getJsTicket($appid) || !$url) return false;
	    if (!$timestamp)
	        $timestamp = time();
	    if (!$noncestr)
	        $noncestr = $this->generateNonceStr();
	    $ret = strpos($url,'#');
	    if ($ret)
	        $url = substr($url,0,$ret);
	    $url = trim($url);
	    if (empty($url))
	        return false;
	    $arrdata = array("timestamp" => $timestamp, "noncestr" => $noncestr, "url" => $url, "jsapi_ticket" => $this->jsapi_ticket);
	    $sign = $this->getSignature($arrdata);
	    if (!$sign)
	        return false;
	    $signPackage = array(
	        "appId"     => $this->appid,
	        "nonceStr"  => $noncestr,
	        "timestamp" => $timestamp,
	        "url"       => $url,
	        "signature" => $sign
	    );
	    return $signPackage;
	}
	/**
	 * 获取access_token
	 * @param string $appid 如在类初始化时已提供，则可为空
	 * @param string $appsecret 如在类初始化时已提供，则可为空
	 * @param string $token 手动指定access_token，非必要情况不建议用
	 */
	public function checkAuth($appid='',$appsecret='',$token=''){
	    if (!$appid || !$appsecret) {
	        $appid = $this->appid;
	        $appsecret = $this->appsecret;
	    }
	    if ($token) { //手动指定token，优先使用
	        $this->access_token=$token;
	        return $this->access_token;
	    }
	    /*
	    $authname = 'wechat_access_token'.$appid;
	    if ($rs = $this->getCache($authname))  {
	        $this->access_token = $rs;
	        return $rs;
	    }
	    */
	    $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
	    if ($result) {
	        $json = json_decode($result,true);
	        if (!$json || isset($json['errcode'])) {
	            $this->errCode = $json['errcode'];
	            $this->errMsg = $json['errmsg'];
	            return false;
	        }
	        $this->access_token = $json['access_token'];
// 	        $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
// 	        $this->setCache($authname,$this->access_token,$expire);
	        return $this->access_token;
	    }
	    return false;
	}
}