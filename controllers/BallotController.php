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
    public function actionInitballot(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_name' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'description' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
            'begin_time' => ['type' => 'int', 'required' => TRUE, 'default' => ''],
            'end_time' => ['type' => 'int', 'required' => TRUE, 'default' => ''],
            'status' => ['type' => 'string', 'required' => TRUE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['ballot_name'] = $args['ballot_name'];
        $data['description'] = $args['description'];
        $data['begin_time'] = date('Y-m-d H:i:s',$args['begin_time']);
        $data['end_time'] = date('Y-m-d H:i:s',$args['end_time']);

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
    public function actionUpballot(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'ballot_id' => ['type' => 'int', 'required' => TRUE, 'default' => ''],
            'ballot_name' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'description' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
            'begin_time' => ['type' => 'int', 'required' => FALSE, 'default' => ''],
            'end_time' => ['type' => 'int', 'required' => FALSE, 'default' => ''],
            'status' => ['type' => 'string', 'required' => FALSE, 'default' => ''],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['ballot_id'] = $args['ballot_id'];
        $data['ballot_name'] = $args['ballot_name'];
        $data['description'] = $args['description'];
        $data['begin_time'] = date('Y-m-d H:i:s',$args['begin_time']);
        $data['end_time'] = date('Y-m-d H:i:s',$args['end_time']);
        $data['status'] = $args['status'];
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
    public function actionGetballotlist(){

    }

    /**
     * 获取当前活动信息
     */
    public function actionGetballotdetail(){

    }

    /**
     *添加参赛主播
     */
    public function actionAddanchor(){

    }

    /**
     * 主播退赛
     */
    public function actionDelanchor(){

    }

    /**
     * 投票
     */
    public function actionAddvotes(){

    }
}