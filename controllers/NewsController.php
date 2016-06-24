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

class NewsController extends BaseController
{
    private $anchorService;
    private $anchorNewsService;
    private $anchorCommentService;


    /**
     * 主播发布动态
     */
    public function actionAddAnchorNews(){

        $result = $data = $where = array();

        $this->checkMethod('post');
        $rule = [
            'anchor_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
            'images' => ['type' => 'string', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE, 'default' => 1],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());

        //构建查询条件
        $data['anchor_id'] = $args['anchor_id'];
        $data['content'] = $args['content'];
        isset($args['images']) && $data['images'] = json_encode(explode(",", $args['images']));
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
     * 修改主播动态内容
     */
    public function actionEditAnchorNews() {
        $this->checkMethod('post');
        $rule = [
            'news_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => false],
            'images' => ['type' => 'string', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());
        
        //构建查询条件
        isset($args['content']) && $data['content'] = $args['content'];
        isset($args['images']) && $data['images'] = json_encode(explode(",", $args['images']));
        isset($args['status']) && $data['status'] = $args['status'];
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->editAnchorNews($args['news_id'], $data);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }

    /**
     * 动态评论
     */
    public function actionNewsComment(){

        $result = $data = $where = array();

        $this->checkMethod('post');
        $rule = [
            'news_id' => ['type' => 'int', 'required' => TRUE],
            'fans_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
            'parent_comment_id' => ['type' => 'int', 'required' => FALSE],
            'status' => ['type' => 'int', 'required' => FALSE, 'default' => 1],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());

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
     * 获取主播动态页(每条动态获取前三条评论)
     */
    public function actionGetAnchorNews(){

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
        $ext['orderBy'] = "create_time DESC";
        $this->anchorService = new AnchorService();
        $result = $this->anchorService->getAnchorNews($where,$ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data'], $result['data']['total']);

    }

    /**
     * 获取动态评论
     */
    public function actionGetNewsCommentList(){

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
    public function actionGetComment(){

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