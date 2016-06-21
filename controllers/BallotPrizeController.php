<?php
namespace app\controllers;
/**
 * 活动奖项接口控制器
 */
use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\BallotPrizeService;

class BallotPrizeController extends BaseController {
    /**
     * create
     * 添加奖项
     * 
     * @param number $ballot_id 必填，活动ID
     * @param number $sort      必填，奖项排序号，升序排列
     * @param string $level     必填，奖项等级
     * @param string $title     必填，奖品名称
     * @param string $logo      必填，奖品logo图标地址
     * @param string $image     必填，奖品实物图片地址
     */
    public function actionCreate() {
        $this->checkMethod('post');
        $rule = [
            'ballot_id'     => ['type'=>'int', 'required'=>true],
            'sort'          => ['type'=>'int', 'required'=>true],
            'level'         => ['type'=>'string', 'required'=>true],
            'title'         => ['type'=>'string', 'required'=>true],
            'logo'          => ['type'=>'string', 'required'=>true],
            'image'         => ['type'=>'string', 'required'=>true],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new BallotPrizeService();
        $res = $service->create($args);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, "活动奖项设置成功", $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * update
     * 修改活动奖项设置
     * @param number $sort      奖项排序号，升序排列
     * @param number $anchor_id 获奖主播ID
     * @param string $level     奖项等级
     * @param string $title     奖品名称
     * @param string $logo      奖品logo图标地址
     * @param string $image     奖品实物图片地址
     */
    public function actionUpdate() {
        $this->checkMethod('post');
        $rule = [
            'prize_id'      => ['type'=>'int', 'required'=>true],
            'sort'          => ['type'=>'int', 'required'=>false],
            'anchor_id'     => ['type'=>'int', 'required'=>false],
            'level'         => ['type'=>'string', 'required'=>false],
            'title'         => ['type'=>'string', 'required'=>false],
            'logo'          => ['type'=>'string', 'required'=>false],
            'image'         => ['type'=>'string', 'required'=>false],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new BallotPrizeService();
        $prizeId = $args['prize_id'];
        unset($args['prize_id']);
        $res = $service->update($prizeId, $args);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, "活动奖项设置成功", $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * delete
     * 删除活动奖项
     * @param number $prize_id  活动奖项ID
     */
    public function actionDelete() {
        $this->checkMethod('post');
        $rule = [
            'prize_id' => ['type'=>'int', 'required'=>true]
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new BallotPrizeService();
        $res = $service->delete($args['prize_id']);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, "活动奖项删除成功", $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
    /**
     * search
     * 查询指定活动的全部奖项设置
     */
    public function actionSearch() {
        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type'=>'int', 'required'=>true]
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $service = new BallotPrizeService();
        $res = $service->search($args['ballot_id'], ['order'=>'sort ASC']);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $res['data']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
}