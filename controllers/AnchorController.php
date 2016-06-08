<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/6/2
 * Time: 13:50
 */

namespace app\controllers;

use Yii;
use app\components\ApiCode;
use app\controllers\BaseController;
use app\service\AnchorService;
use app\service\AnchorNewsService;
use app\service\AnchorCommentService;

class AnchorController extends BaseController
{
    private $anchorService;
    private $anchorNewsService;
    private $anchorCommentService;

    /**
     * 添加新主播
     */
    public function actionAddAnchor(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'anchor_name' => ['type' => 'string', 'required' => TRUE],
            'thumb' => ['type' => 'string', 'required' => FALSE],
            'backimage' => ['type' => 'string', 'required' => FALSE],
            'qrcode' => ['type' => 'string', 'required' => FALSE],
            'platform' => ['type' => 'string', 'required' => FALSE],
            'broadcast' => ['type' => 'string', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['anchor_name'] = $args['anchor_name'];
        isset($args['thumb']) && $data['thumb'] = $args['thumb'];
        isset($args['backimage']) && $data['backimage'] = $args['backimage'];
        isset($args['qrcode']) && $data['qrcode'] = $args['qrcode'];
        isset($args['platform']) && $data['platform'] = $args['platform'];
        isset($args['broadcast']) && $data['broadcast'] = $args['broadcast'];
        $data['create_time'] = time();
        $data['modify_time'] = time();
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->addAnchor($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }

    /**
     * 修改主播资料
     */
    public function actionUpDateAnchor(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
            'anchor_name' => ['type' => 'string', 'required' => TRUE],
            'thumb' => ['type' => 'string', 'required' => FALSE],
            'backimage' => ['type' => 'string', 'required' => FALSE],
            'qrcode' => ['type' => 'string', 'required' => FALSE],
            'platform' => ['type' => 'string', 'required' => FALSE],
            'broadcast' => ['type' => 'string', 'required' => FALSE],
            'description' => ['type' => 'string', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['anchor_name'] = $args['anchor_name'];
        $where['anchor_id'] = $args['anchor_id'];
        isset($args['thumb']) && $data['thumb'] = $args['thumb'];
        isset($args['backimage']) && $data['backimage'] = $args['backimage'];
        isset($args['qrcode']) && $data['qrcode'] = $args['qrcode'];
        isset($args['platform']) && $data['platform'] = $args['platform'];
        isset($args['broadcast']) && $data['broadcast'] = $args['broadcast'];
        isset($args['description']) && $data['description'] = $args['description'];
        $data['modify_time'] = time();
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->updateAnchor($data,$where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取主播资料页
     */
    public function actionGetAnchorInformation(){

        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['anchor_id'] = $args['anchor_id'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getAnchorInformation($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }



    /**
     * 获取主播列表
     */
    public function actionGetAnchorList(){

        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'page' => ['type' => 'int', 'required' => FALSE],
            'size' => ['type' => 'int', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = ['modify_time'=>'desc'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getAnchorList($where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取主播列表并获取最新一条动态
     */
    public function actionGetAnchorListAndNews(){

        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'page' => ['type' => 'int', 'required' => FALSE],
            'size' => ['type' => 'int', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = ['modify_time'=>'desc'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getAnchorListAndNews($where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }



}