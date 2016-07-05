<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/6/2
 * Time: 14:17
 */

namespace app\service;

use Yii;
use app\components\ApiCode;
use app\service\BaseService;
use app\models\Anchor;
use app\models\AnchorNews;
use app\models\AnchorComment;
use app\models\Message;
use app\models\Fans;

class MessageService extends BaseService
{
    private $message;
    private $fans;

    /**
     * 添加消息
     */
    public function addMessage($data = array()){
        $this->fans = new Fans();
        $fansWhere['fans_id'] = $data['send_fans_id'];
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans){
            return $this->export(false,'您的用户已锁定或不存在',$fans);
        }
        $fansWhere['fans_id'] = $data['receive_fans_id'];
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans){
            return $this->export(false,'您要发送消息的用户不存在',$fans);
        }
        $this->message = new Message();
        $this->message->attributes = $data;
        if(!$this->message->validate()) {
            return $this->export(false,'属性验证失败',$this->message->errors);
        }
        $result = $this->message->insertData($data);
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);
    }
    /**
     * 群发消息
     */
    public function addMessageAtAll($data = array()){
        $this->fans = new Fans();
        $fansList = $this->fans->getList('fans_id');
        if(!$fansList){
            return $this->export(false,'获取用户列表失败');
        }
        $this->message = new Message();
        $this->message->attributes = $data;
        if(!$this->message->validate()) {
            return $this->export(false,'属性验证失败',$this->message->errors);
        }
        $result = array();
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($fansList as $item){
                $data['receive_fans_id'] = $item['fans_id'];
                $result = $this->message->insertData($data);
                if(!$result){
                    return $this->export(false,'操作失败',$result);
                }
            }
            // ... 执行其他 SQL 语句 ...
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败');
        }
        return $this->export(true,'成功',$result);
    }
    /**
     * 选择性亲发
     */
    public function addMessageMore($data = array(),$fans_id_list = array()){
        $this->fans = new Fans();
        if(!isset($data['send_fans_id'])){
            return $this->export(false,'没有填写发送人');
        }
        $fans = $this->fans->getRow('fans_id',['fans_id'=>$data['send_fans_id']]);
        if(!$fans){
            return $this->export(false,'您没有这个权限');
        }
        $this->message = new Message();
        $this->message->attributes = $data;
        if(!$this->message->validate()) {
            return $this->export(false,'属性验证失败',$this->message->errors);
        }
        $result = array();
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($fans_id_list as $item){
                $data['receive_fans_id'] = $item;
                $result = $this->message->insertData($data);
                if(!$result){
                    return $this->export(false,'操作失败',$result);
                }
            }
            // ... 执行其他 SQL 语句 ...
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败');
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 删除消息
     */
    public function delMessage($message_id,$send_fans_id){
        $this->fans = new Fans();
        $this->message = new Message();
        $fansWhere['fans_id'] = $send_fans_id;
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans){
            return $this->export(false,'您的用户已锁定或不存在',$fans);
        }
        $messageWhere['message_id'] = $message_id;
        $messageWhere['send_fans_id'] = $send_fans_id;
        $message = $this->message->getRow('*',$messageWhere);
        if(!$message){
            return $this->export(false,'消息不存在',$message);
        }
        $result = $this->message->delRow($messageWhere);
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 修改消息内容
     */
    public function updateMessage($where = array(),$data = array()){
        $this->message = new Message();
        $message = $this->message->getRow('*',$where);
        if(!$message){
            return $this->export(false,'消息不存在',$message);
        }
        $this->fans = new Fans();
        if(isset($data['send_fans_id'])){
            $fansWhere['fans_id'] = $data['send_fans_id'];
            $fans = $this->fans->getRow('*',$fansWhere);
            if(!$fans){
                return $this->export(false,'您的用户已锁定或不存在',$fans);
            }
        }
        if(isset($data['receive_fans_id'])){
            $fansWhere['fans_id'] = $data['receive_fans_id'];
            $fans = $this->fans->getRow('*',$fansWhere);
            if(!$fans){
                return $this->export(false,'您要发送消息的用户不存在',$fans);
            }
        }
        if(isset($data['content']) && $data['content'] == ''){
            return $this->export(false,'消息内容不可为空');
        }
        $result = $this->message->updateData($data,$where);
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 读取单条消息
     */
    public function getMessageById($receive_fans_id,$message_id){
        $this->fans = new Fans();
        $fans = $this->fans->getRow('*',['fans_id' => $receive_fans_id ]);
        if(!$fans){
            return $this->export(false,'您的用户已冻结或不存在',$fans);
        }
        $messageWhere['receive_fans_id'] = $receive_fans_id;
        $messageWhere['message_id'] = $message_id;
        $this->message = Message::findOne($messageWhere);
        if(!$this->message){
            return $this->export(false,'您要查看的消息不存在');
        }
        if($this->message->status == 1){
            $this->message->receive_time = time();
            $this->message->status = 2;
            $result = $this->message->save();
            if(!$result){
                return $this->export(false,'读取消息失败',$result);
            }
        }
        $result = $this->message->attributes;
        if($this->message->send_fans_id){
            $send_fans = $this->fans->getRow('*',['fans_id'=>$this->message->send_fans_id]);
            if(!$send_fans){
                return $this->export(false,'读取用户消息时发生错误',$send_fans);
            }
            $result['send_fans'] = $send_fans;
        }else{
            $result['send_fans']['fans_id'] = 0;
            $result['send_fans']['wx_name'] = '系统消息';
            $result['send_fans']['wx_thumb'] = "http://o8syigvwe.bkt.clouddn.com/o_1amdi1me5l8c16jc18bq1d6hdrgn.png";

        }

        $receive_fans = $this->fans->getRow('*',['fans_id'=>$this->message->receive_fans_id]);
        if(!$receive_fans){
            return $this->export(false,'读取用户消息时发生错误',$receive_fans);
        }
        $result['receive_fans'] = $receive_fans;
        return $this->export(true,'成功',$result);
    }

    /**
     * 获取消息列表
     */
    public function getMessageList($where,$ext){
        $this->message = new Message();
        $result = $this->message->getListAndLimit('*',$where,$ext);
        if(!$result){
            return $this->export(false,'获取失败');
        }
        $this->fans = new Fans();
        foreach ($result['list'] as $key => $value){
            if($value['send_fans_id']!=0){
                $send_fans = $this->fans->getRow('*',['fans_id'=>$value['send_fans_id']]);
                if(!$send_fans){
                    return $this->export(false,'读取用户消息时发生错误',$send_fans);
                }
                $result['list'][$key]['send_fans'] = $send_fans;
            }else{
                $result['list'][$key]['send_fans']['fans_id'] = 0;
                $result['list'][$key]['send_fans']['wx_name'] = '系统消息';
                $result['list'][$key]['send_fans']['wx_thumb'] = "http://o8syigvwe.bkt.clouddn.com/o_1amdi1me5l8c16jc18bq1d6hdrgn.png";
            }
            if($value['receive_fans_id']!=0){
                $receive_fans = $this->fans->getRow('*',['fans_id'=>$value['receive_fans_id']]);
                if(!$receive_fans){
                    return $this->export(false,'读取用户消息时发生错误',$receive_fans);
                }
                $result['list'][$key]['receive_fans'] = $receive_fans;
            }else{
                $result['list'][$key]['receive_fans']['fans_id'] = 0;
                $result['list'][$key]['receive_fans']['wx_name'] = '系统消息';
                $result['list'][$key]['receive_fans']['wx_thumb'] = "http://wx.qlogo.cn/mmopen/oYwP0cFmRU0yeRFvMnAZkytQiczSB4lAkXcPdH1pam409VQmuovLd55pp5libkUoOBpXLBJojibnKd7TSst5hicDaw/0";
            }
        }
        return $this->export(true,'成功',$result);
    }
}
