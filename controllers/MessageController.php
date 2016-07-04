<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/6/28
 * Time: 14:36
 */

namespace app\controllers;

use app\models\Fans;
use Yii;
use app\components\ApiCode;
use app\controllers\BaseController;
use app\service\AnchorService;
use app\service\AnchorNewsService;
use app\service\AnchorCommentService;
use app\service\MessageService;


class MessageController extends BaseController
{
    private $messageService;
    private $fans;

    /**
     * 发送消息
     */
    public function actionAddMessage(){
        $this->messageService = new MessageService();
        $this->checkMethod('post');
        $rule = [
            'send_fans_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
            'receive_fans_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());

        $message = $args;
        $message['code'] = Yii::$app->utils->createID(Yii::$app->id);
        $message['create_time'] = time();
        $result = $this->messageService->addMessage($message);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }
    /**
     * 群发消息
     */
    public function actionAddMessageAtAll(){
        $this->messageService = new MessageService();
        $this->checkMethod('post');
        $rule = [
            'send_fans_id' => ['type' => 'int', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());

        $message = $args;
        $message['code'] = Yii::$app->utils->createID(Yii::$app->id);
        $message['create_time'] = time();
        $result = $this->messageService->addMessageAtAll($message);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }
    /**
     * 群发消息
     */
    public function actionAddMessageMore(){
        $this->messageService = new MessageService();
        $this->checkMethod('post');
        $rule = [
            'send_fans_id' => ['type' => 'int', 'required' => TRUE],
            'fans_id_list' => ['type' => 'string', 'required' => TRUE],
            'content' => ['type' => 'string', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->post());

        $fans_id_list_str = $args['fans_id_list'];
        $fans_id_list = explode(',',$fans_id_list_str);
        foreach ($fans_id_list as $key => $value){
            if($value == ""){
                unset($fans_id_list[$key]);
            }
        }
        $message['send_fans_id'] = $args['send_fans_id'];
        $message['content'] = $args['content'];
        $message['code'] = Yii::$app->utils->createID(Yii::$app->id);
        $message['create_time'] = time();
        $result = $this->messageService->addMessageMore($message,$fans_id_list);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }

    /**
     * 查看消息
     */
    public function actionGetMessageById(){

        $this->checkMethod('get');
        $rule = [
            'message_id' => ['type' => 'int', 'required' => TRUE],
            'fans_id' => ['type' => 'int', 'required' => TRUE],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());

        $this->messageService = new MessageService();
        $message_id = $args['message_id'];
        $receive_fans_id = $args['fans_id'];

        $result = $this->messageService->getMessageById($receive_fans_id,$message_id);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);
    }

    /**
     * 读取消息列表
     */
    public function actionGetMessageListByFansId(){

        $this->checkMethod('get');
        $rule = [
            'fans_id' => ['type' => 'int', 'required' => TRUE],
            'page' => ['type' => 'int','required'=>false,'dafault'=>1],
            'size' => ['type' => 'int','required'=>false,'dafault'=>10],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $this->messageService = new MessageService();
        //构建查询条件
        $fansWhere['fans_id'] = $args['fans_id'];
        $this->fans = new Fans();
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans){
            $this->renderJson(ApiCode::ERROR_API_FAILED,'您的用户已锁定或不存在');
        }
        //构建查询条件
        $where['receive_fans_id'] = $args['fans_id'];
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = "create_time DESC";
        $result = $this->messageService->getMessageList($where, $ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);

    }

    /**
     * 获取全部消息列表 (后台应用)
     */
    public function actionGetAllMessageList(){

        $this->checkMethod('get');
        $rule = [
            'page' => ['type' => 'int','required'=>false,'dafault'=>1],
            'size' => ['type' => 'int','required'=>false,'dafault'=>10],
        ];
        $args = $this->getRequestData($rule, Yii::$app->request->get());
        $this->messageService = new MessageService();
        $ext['limit']['page'] = isset($args['page']) ? $args['page'] : 1;
        $ext['limit']['size'] = isset($args['size']) ? $args['size'] : 10;
        //计算limit数据
        $ext['limit']['start'] = ($ext['limit']['page'] - 1) * $ext['limit']['size'];
        $ext['orderBy'] = "create_time DESC";
        $ext['groupBy'] = 'code';
        $result = $this->messageService->getMessageList(['send_fans_id'=>0], $ext);
        if($result['status']==false){
            $this->renderJson(ApiCode::ERROR_API_FAILED,$result['message'],$result['data']);
        }
        $this->renderJson(ApiCode::SUCCESS,$result['message'],$result['data']);


    }
}
