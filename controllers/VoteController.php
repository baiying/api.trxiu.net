<?php
namespace app\controllers;
/**
 * 投票接口控制器类
 */
use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\VoteService;

class VoteController extends BaseController {
    /**
     * add
     * 投一票
     * @param number $data['ballot_id']     活动ID，必填
     * @param number $data['anchor_id']     主播ID，必填
     * @param number $data['fans_id']       粉丝ID，必填
     * @param string $data['canvass_id']    拉票ID，选填
     * @param number $data['earn]           抽取拉票红包金额，选填
     */
    public function actionAdd() {
        $this->checkMethod('post');
        $rule = [
            'ballot_id' => ['type'=>'int', 'required'=>TRUE],
            'anchor_id' => ['type'=>'int', 'required'=>TRUE],
            'fans_id'   => ['type'=>'int', 'required'=>TRUE],
            'canvass_id'=> ['type'=>'string', 'required'=>FALSE, 'default'=>''],
            'earn'      => ['type'=>'float', 'required'=>FALSE, 'default'=>0.00]
        ];
        $data = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new VoteService();
        $res = $service->addOne($data);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
}