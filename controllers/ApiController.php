<?php
namespace app\controllers;

use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;

class ApiController extends BaseController {

	public function actionTest() {
		$this->checkMethod('post');
		// 接口参数白名单
		$rule = [
			'msg' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
		];
		$data = $this->getRequestData($rule, Yii::$app->request->post());
		$this->renderJson(ApiCode::SUCCESS, 'OK', $data['msg']);
	}
}