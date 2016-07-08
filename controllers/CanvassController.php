<?php
namespace app\controllers;
/**
 * 拉票接口控制器
 */
use Yii;
use app\controllers\BaseController;
use app\components\ApiCode;
use app\service\CanvassService;
use app\models\CanvassRed;

class CanvassController extends BaseController {
    /**
     * create-canvass
     * 添加拉票
     * @param number $data['ballot_id']     活动ID
     * @param number $data['anchor_id']     主播ID
     * @param number $data['fans_id']       粉丝ID
     * @param string $data['source_id']     来源拉票ID
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
            'source_id'     => ['type'=>'string', 'required'=>false],
            'status'        => ['type'=>'int', 'required'=>false, 'default'=>1],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        $service = new CanvassService();
        $res = $service->createCanvass($args);
        if($res['status']) {
            $this->renderJson(ApiCode::SUCCESS, '拉票添加成功', $res['data']);
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
     * 根据拉票信息和粉丝ID获取是否领取过红包
     */
    public function actionGetRedByFansId() {
        $this->checkMethod('get');
        $rule = [
            'canvass_id' => ['type'=>'string', 'required'=>true],
            'fans_id' => ['type'=>'int', 'required'=>true],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $CanvassRed = new CanvassRed();
        $where['canvass_id'] = $args['canvass_id'];
        $where['fans_id'] = $args['fans_id'];
        $res = $CanvassRed->getRow('*',$where);
        if(!$res) {
            $this->renderJson(ApiCode::ERROR_API_FAILED, '获取红包信息失败');
        }
        $this->renderJson(ApiCode::SUCCESS, '成功', $res);
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
    
    public function actionSearch() {
        $this->checkMethod('get');
        $result = [];
        $rule = [
            'ballot_id' => ['type'=>'int', 'required'=>TRUE],
            'anchor_id' => ['type'=>'int', 'required'=>FALSE],
            'fans_id'   => ['type'=>'int', 'required'=>FALSE],
            'canvass_id'=> ['type'=>'string', 'required'=>FALSE],
            'page'      => ['type'=>'int', 'required'=>FALSE, 'default'=>1],
            'pagesize'  => ['type'=>'int', 'required'=>FALSE, 'default'=>20],
            'order'     => ['type'=>'string', 'required'=>FALSE, 'default'=>'create_time DESC'],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $service = new CanvassService();
        $res = $service->search($args);
        if($res['status']) {
            // 补全拉票发起人信息
            foreach($res['data']['data'] as $item) {
                $arr = $item->attributes;
                $fans = $item->fans;
                $arr['name'] = $fans->wx_name;
                $arr['thumb'] = $fans->wx_thumb;
                $result[] = $arr;
            }
            $this->renderJson(ApiCode::SUCCESS, $res['message'], $result, $res['data']['count']);
        } else {
            $this->renderJson(ApiCode::ERROR_API_FAILED, $res['message'], $res['data']);
        }
    }
}
