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
    public function actionAddanchor(){

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
        $data['thumb'] = $args['thumb'];
        $data['backimage'] = $args['backimage'];
        $data['qrcode'] = $args['qrcode'];
        $data['platform'] = $args['platform'];
        $data['broadcast'] = $args['broadcast'];
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
    public function actionUpdateanchor(){

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
        $data['anchor_name'] = $args['anchor_name'];
        $data['thumb'] = $args['thumb'];
        $data['backimage'] = $args['backimage'];
        $data['qrcode'] = $args['qrcode'];
        $data['platform'] = $args['platform'];
        $data['broadcast'] = $args['broadcast'];
        $data['description'] = $args['description'];
        $data['modify_time'] = time();
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->updateAnchor($data,$where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 主播发布动态
     */
    public function actionAddanchornews(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
            'images' => ['type' => 'string', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE, 'default' => 1],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['anchor_id'] = $args['anchor_id'];
        $data['content'] = $args['content'];
        isset($data['images']) && $data['images'] = $args['images'];
        $data['status'] = $args['status'];
        $data['create_time'] = time();
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->addAnchorNews($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 动态评论
     */
    public function actionNewscomment(){

        $result = $data = $where = array();

        $this->checkMethod('get');
        $rule = [
            'news_id' => ['type' => 'int', 'required' => TRUE],
            'fans_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
            'parent_comment_id' => ['type' => 'int', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE, 'default' => 1],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $data['news_id'] = $args['news_id'];
        $data['fans_id'] = $args['fans_id'];
        $data['content'] = $args['content'];
        $data['status'] = $args['status'];
        isset($data['parent_comment_id']) && $data['parent_comment_id'] = $args['parent_comment_id'];
        $data['create_time'] = time();
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->newComment($data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }


    /**
     * 获取主播列表
     */
    public function actionGetanchorlist(){

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
    public function actionGetanchorlistandnews(){

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

    /**
     * 获取主播资料页
     */
    public function actionGetanchorinformation(){

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
     * 获取主播动态页(每条动态获取前三条评论)
     */
    public function actionGetanchornews(){

        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
            'page' => ['type' => 'int', 'required' => FALSE],
            'size' => ['type' => 'int', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['anchor_id'] = $args['anchor_id'];
        //构建查询条件
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = ['create_time'=>'desc'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getAnchorNews($where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取动态评论
     */
    public function actionGetnewscommentlist(){

        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'news_id' => ['type' => 'int', 'required' => TRUE],
            'page' => ['type' => 'int', 'required' => FALSE],
            'size' => ['type' => 'int', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['news_id'] = $args['news_id'];
        //构建查询条件
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = ['create_time'=>'desc'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getNewsCommentList($where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取当前评论信息
     */
    public function actionGetcomment(){

        $result = $data = $where = $ext = array();

        $this->checkMethod('get');
        $rule = [
            'comment_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        //构建查询条件
        $where['comment_id'] = $args['comment_id'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getCommentAndFans($where);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }


}