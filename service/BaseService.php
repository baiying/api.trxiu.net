<?php
namespace app\service;

use Yii;
use yii\base\Component;

class BaseService extends Component {
    public $errors = [];
    
    /**
     * 格式化service功能输出的结果数组
     * @return array
     */
    public function export($status, $message = "", $data = []) {
    	return [
			'status'  => $status,
			'message' => $message,
			'data'    => $data
    	];
    }
}