<?php
namespace app\controllers;
/**
 * 拉票接口控制器
 */
use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\CanvassService;

class CanvassController extends BaseController {
    /**
     * create-canvass
     * 添加拉票
     * @param number $data['ballot_id']     活动ID
     * @param number $data['anchor_id']     主播ID
     * @param number $data['fans_id']       粉丝ID
     * @param number $data['charge']        充值金额
     * @param number $data['status']        拉票状态，1 有效，2 待支付，3 无效
     */
    public function actionCreateCanvass() {
        $this->checkMethod('post');
        $rule = [
            'ballot_id'     => ['type'=>'int', 'required'=>true],
            'anchor_id'     => ['type'=>'int', 'required'=>true],
            'fans_id'       => ['type'=>'int', 'required'=>true],
            'charge'        => ['type'=>'float', 'required'=>true],
            'status'        => ['type'=>'int', 'required'=>false, 'default'=>1],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new CanvassService();
        $res = $service->createCanvass($args);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, '拉票添加成功');
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
        }
    }
    /**
     * 获取拉票信息
     */
    public function actionInfo() {
        $this->checkMethod('get');
        $rule = [
            'canvass_id' => ['type'=>'string', 'required'=>true],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $service = new CanvassService();
        $res = $service->info($args['canvass_id']);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
        }
    }
    /**
     * receive-redpackage
     * 抽取红包
     * @param number $ballot_id     活动Id
     * @param string $canvass_id    拉票ID
     * @param number $fans_id       抽取红包的粉丝ID
     */
    public function actionReceiveRedpackage() {
        $this->checkMethod('post');
        $rule = [
            'ballot_id'     => ['type'=>'int', 'required'=>true],
            'canvass_id'    => ['type'=>'string', 'required'=>true],
            'fans_id'       => ['type'=>'int', 'required'=>true],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        extract($args);
        $service = new CanvassService();
        $res = $service->receiveRedpackage($ballot_id, $canvass_id, $fans_id);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message']);
        }
    }
}