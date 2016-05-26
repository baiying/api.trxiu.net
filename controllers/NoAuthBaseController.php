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

class NoAuthBaseController extends Controller {
    // 关闭Post安全验证
    public $enableCsrfValidation = false;
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
     * 检查访问方式
     * @param string $method
     * @return array
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
            if(!isset($data[$key]) && $value['required'] == TRUE) {
                $this->renderJson(ApiCode::ERROR_API_DENY, 'Lost parameter: '.$key);
            }
            switch($value['type']) {
                case 'int':
                    $result[$key] = intval($data[$key]);
                    break;
                case 'float':
                    $result[$key] = floatval($data[$key]);
                    break;
                case 'string':
                    $result[$key] = htmlspecialchars(addslashes(strip_tags(trim($data[$key]))));
                    break;
            }
        }
        return $result;
    }
}