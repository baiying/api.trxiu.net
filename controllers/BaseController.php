<?php
/**
 * API控制器基类
 */
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Json;
use yii\web\Response;
use app\components\ApiCode;

class BaseController extends Controller {
    // 关闭Post安全验证
    public $enableCsrfValidation = false;
    
    public function init() {
       if(!YII_DEBUG)
            $this->checkHmac();
    }
    /**
     * 将接口返回数据以统一格式的JSON输出
     * $code 		string 	执行结果标识码，请见Code.php
     * $message 	string 	执行结果描述信息
     * $data 		array 	执行结果输出的数据
     * $debug 		string 	调试模式下输出的信息
     * $extension 	array 	扩展设置
     */
    public function renderJson($code='', $message='', $data = [], $count = 0, $debug = '', $extension = []) {
        $result = array(
            'code' => $code, 
            'message' => $message, 
            'count'=>$count, 
            'data' => $data, 
            'debug' => $debug, 
            'extension' => $extension
        );
        echo Json::encode($result);
        exit;
    }
    /**
     * checkHmac
     * 检查访问接口签名是否正确
     * 
     * @return mixed
     */
    private function checkHmac() {
        $requestData = [];
        if(Yii::$app->request->isGet) {
            $requestData = Yii::$app->request->get();
        }
        if(Yii::$app->request->isPost) {
            $requestData = Yii::$app->request->post();
        }
        unset($requestData['r']);
        // 验证传递参数的合法性
        if(empty($requestData)) {
            $this->renderJson(ApiCode::ERROR_API_DENY, 'request data empty.');
        }
        if(!isset($requestData['_appid'])) {
            $this->renderJson(ApiCode::ERROR_API_DENY, 'Invalid appid.');
        }
        if(!isset($requestData['_hmac'])) {
            $this->renderJson(ApiCode::ERROR_API_DENY, 'Invalid hmac.');
        }
        if(!isset(Yii::$app->params['appKeyValues'][$requestData['_appid']])) {
            $this->renderJson(ApiCode::ERROR_API_DENY, 'Invalid appid.');
        }
        // 获取应用秘钥
        $hmacKey = Yii::$app->params['appKeyValues'][$requestData['_appid']];
        // 获取签名字符串
        $hmac = $requestData['_hmac'];
        unset($requestData['_hmac']);
        // 将传递参数按照参数名升序排列
        $dataKeys = array_keys($requestData);
        sort($dataKeys);
        $dataStr = $spl = "";
        foreach($dataKeys as $k) {
            $dataStr .= $spl . $k . "=" . $requestData[$k];
            $spl = "&";
        }
        $dataStr .= $spl . "salt={$hmacKey}";
        if($hmac != strtoupper(md5($dataStr))) {
            $this->renderJson(ApiCode::ERROR_API_DENY, 'Invalid hmac');
        }
        // 从传递参数数组中清除_appid和_hmac参数，防止影响接口程序
        if(Yii::$app->request->isGet) {
            unset($_GET['_appid']);
            unset($_GET['_hmac']);
        }
        if(Yii::$app->request->isPost) {
            unset($_POST['_appid']);
            unset($_POST['_hmac']);
        }
    }
    
    /**
     * 检查访问方式
     * @param string $method
     * @return multitype:boolean string multitype:
     */
    public function checkMethod($method = 'get') {
        switch($method) {
            case 'get':
                if(!Yii::$app->request->isGet) {
                    $this->renderJson(ApiCode::ERROR_API_DENY, '访问方式错误，请以GET方式访问');
                }
                break;
            case 'post':
                if(!Yii::$app->request->isPost) {
                    $this->renderJson(ApiCode::ERROR_API_DENY, '访问方式错误，请以POST方式访问');
                }
                break;
        }
    }
    /**
     * 获取请求参数
     * @param  array  $rule 参数白名单
     * @param  array  $data 传递的参数数组
     * @return array  
     */
    public function getRequestData($rule = [], $data = []) {
        $result = array();
        foreach($rule as $key=>$value) {
            if(!isset($data[$key]) && isset($value['required']) && $value['required'] == TRUE) {
                $this->renderJson(ApiCode::ERROR_API_DENY, 'Lost parameter: '.$key);
            }
            switch($value['type']) {
                case 'int':
                    if(!isset($data[$key])) {
                        if(isset($value['default'])) {
                            $result[$key] = intval($value['default']);
                        }
                    } else {
                        $result[$key] = intval($data[$key]);
                    }
                    break;
                case 'float':
                    if(!isset($data[$key])) {
                        if(isset($value['default'])) {
                            $result[$key] = floatval($value['default']);
                        } 
                    } else {
                        $result[$key] = floatval($data[$key]);
                    }
                    break;
                case 'string':
                    if(!isset($data[$key])) {
                        if(isset($value['default'])) {
                            $result[$key] = $value['default'];
                        }
                    } else {
                        $result[$key] = htmlspecialchars(addslashes(strip_tags(trim($data[$key]))));
                    }
                    break;
            }
        }
        return $result;
    }
    
}
