<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/5/30
 * Time: 15:37
 */

namespace app\controllers;

use Yii;
use app\components\ApiCode;
use app\service\BallotService;
use app\controllers\BaseController;

class BallotController extends BaseController
{
    private $ballotService;


    /**
     * 初始化活动
     */
    public function actionInitBallot(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_name' => ['type' => 'string', 'required' => TRUE],
            'description' => ['type' => 'string', 'required' => TRUE],
            'begin_time' => ['type' => 'int', 'required' => FALSE, 'default' => time()],
            'end_time' => ['type' => 'int', 'required' => TRUE],
            'status' => ['type' => 'string', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['ballot_name'] = $args['ballot_name'];
        $data['description'] = $args['description'];
        $data['begin_time'] = $args['begin_time'];
        $data['end_time'] = $args['end_time'];

        $data['status'] = $args['status'];
        $this->ballotService = new BallotService();
        $result = $this->ballotService->initBallot($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);


    }

    /**
     * 修改活动内容
     */
    public function actionUpBallot(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE],
            'ballot_name' => ['type' => 'string', 'required' => FALSE],
            'description' => ['type' => 'string', 'required' => FALSE],
            'begin_time' => ['type' => 'int', 'required' => FALSE],
            'end_time' => ['type' => 'int', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['ballot_id'] = $args['ballot_id'];
        isset($args['ballot_name']) && $data['ballot_name'] = $args['ballot_name'];
        isset($args['description']) && $data['description'] = $args['description'];
        isset($args['begin_time']) && $data['begin_time'] = date('Y-m-d H:i:s',$args['begin_time']);
        isset($args['end_time']) && $data['end_time'] = date('Y-m-d H:i:s',$args['end_time']);
        isset($args['status']) && $data['status'] = $args['status'];
        foreach ($data as $k => $v){
            if (!$v){
                unset($data[$k]);
            }
        }
        $this->ballotService = new BallotService();
        $result = $this->ballotService->upBallot($data,$where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }

    /**
     * 获取当前活动列表
     */
    public function actionGetBallotList(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'current_time' => ['type' => 'int', 'required' => FALSE],
            'begin_time' => ['type' => 'int', 'required' => FALSE],
            'end_time' => ['type' => 'int', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE],
            'page' => ['type' => 'int', 'required' => FALSE],
            'size' => ['type' => 'int', 'required' => FALSE],
            'order' => ['type' => 'string', 'required' => FALSE],
            'by' => ['type' => 'string', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        isset($args['current_time']) && $where['current_time'] = $args['current_time'];
        isset($args['begin_time']) && $where['begin_time'] = $args['begin_time'];
        isset($args['end_time']) && $where['end_time'] = $args['end_time'];
        isset($args['status']) && $where['status'] = $args['status'];
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $order = isset($args['order']) ? $args['order'] : 'desc';
        $by = isset($args['by']) ? $args['by'] : 'create_time';
        $ext['orderBy'] = [$by=>$order];
        foreach ($where as $k => $v){
            if (!$v){
                unset($where[$k]);
            }
        }
        $this->ballotService = new BallotService();
        $result = $this->ballotService->getBallotList($where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取当前活动信息
     */
    public function actionGetBallotDetail(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $where['ballot_id'] = $args['ballot_id'];
        $this->ballotService = new BallotService();
        $result = $this->ballotService->getBallotDetail($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     *添加参赛主播
     */
    public function actionBallotAddAnchor(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE],
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $data['ballot_id'] = $args['ballot_id'];
        $data['anchor_id'] = $args['anchor_id'];
        $this->ballotService = new BallotService();
        $result = $this->ballotService->ballotAddAnchor($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 主播退赛
     */
    public function actionBallotDelAnchor(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE],
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $where['ballot_id'] = $args['ballot_id'];
        $where['anchor_id'] = $args['anchor_id'];
        $this->ballotService = new BallotService();
        $result = $this->ballotService->ballotDelAnchor($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 投票
     */
    public function actionAddVotes(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE],//活动ID
            'anchor_id' => ['type' => 'int', 'required' => TRUE],//主播ID
            'fans_id' => ['type' => 'int', 'required' => TRUE],//粉丝ID
            'votes' => ['type' => 'int', 'required' => FALSE],//投票票数
            'is_canvass' => ['type' => 'int', 'required' => TRUE],//是否被拉票
            'canvass_id' => ['type' => 'int', 'required' => FALSE],//拉票ID
            'amount' => ['type' => 'int', 'required' => FALSE],//拉票金额
            'url' => ['type' => 'string', 'required' => TRUE],//拉票分享地址
            'status' => ['type' => 'int', 'required' => TRUE],//状态，1 有效，2 待支付，3 无效
            'active_time' => ['type' => 'int', 'required' => FALSE],//拉票生效时间
            'end_time' => ['type' => 'int', 'required' => TRUE],//拉票结束时间
            'new_fans' => ['type' => 'int', 'required' => FALSE,'default' => 2],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $data['ballot_id'] = $args['ballot_id'];
        $data['anchor_id'] = $args['anchor_id'];
        $data['fans_id'] = $args['fans_id'];
        $data['votes'] =  isset($args['votes']) ? $args['votes'] : 1;
        $data['create_time'] = time();
        $data['new_fans'] = $args['new_fans'];
        $data['active_time'] = isset($args['active_time']) ? $args['active_time'] : time();
        isset($args['end_time']) && $data['end_time'] = $args['end_time'];
        isset($args['is_canvass']) && $data['is_canvass'] = $args['is_canvass'];
        isset($args['amount']) && $data['amount'] = $args['amount'];
        isset($args['url']) && $data['url'] = $args['url'];
        isset($args['status']) && $data['status'] = $args['status'];

        $this->ballotService = new BallotService();
        $result = $this->ballotService->addVotes($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }


    /**
     * 领取红包
     */
    public function actionGetRedPacket(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE],//活动ID
            'anchor_id' => ['type' => 'int', 'required' => TRUE],//主播ID
            'fans_id' => ['type' => 'int', 'required' => TRUE],//粉丝ID
            'canvass_id' => ['type' => 'string', 'required' => TRUE],//拉票ID
            'new_fans' => ['type' => 'int', 'required' => FALSE,'default'=>2],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $data['ballot_id'] = $args['ballot_id'];
        $data['anchor_id'] = $args['anchor_id'];
        $data['fans_id'] = $args['fans_id'];
        $data['canvass_id'] = $args['canvass_id'];
        $data['new_fans'] = $args['new_fans'];
        $this->ballotService = new BallotService();

        $result = $this->ballotService->getRedPacket($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }


    /**
     * 查看
     */
    public function actionCheckRedPacket(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'canvass_id' => ['type' => 'string', 'required' => TRUE],//拉票ID
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $canvass_id = $args['canvass_id'];
        $this->ballotService = new BallotService();

        $result = $this->ballotService->checkRedPacket($canvass_id);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }
}
