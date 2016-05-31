<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/5/27
 * Time: 10:06
 */

namespace app\service;

use app\components\ApiCode;
use Yii;
use app\service\BaseService;
use app\models\BallotEAnchor;
use app\models\Ballot;

class BallotService extends BaseService
{
    private $ballot;
    private $ballotEAnchor;

    /**
     * 初始化活动
     */
    public function initBallot($data){
        $this->ballot = new Ballot();
        if(!$this->ballot->validate()) {
            return $this->export(false,'属性验证失败',$this->ballot->errors);
        }
        $data = (object)$data;
        $data->create_time = time();
        isset($data->begin_time) && $data->begin_time = strtotime($data->begin_time);
        isset($data->end_time) && $data->end_time = strtotime($data->end_time);
        $where['ballot_name'] = $data->ballot_name;
        $where['begin_time'] = $data->begin_time;
        $where['end_time'] = $data->end_time;
        $result = $this->ballot->getRow('*',$where);
        if($result) return $this->export(false,'该活动已存在',$result);
        $result = $this->ballot->insertData($data);
        if(!$result){
            return $this->export(false,'插入失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 修改活动信息
     */
    public function upBallot($data,$where){
        $this->ballot = new Ballot();
        if(!$this->ballot->validate()) {
            return $this->export(false,'属性验证失败',$this->ballot->errors);
        }
        $data = (object)$data;
//        $data->update_time = time();
        isset($data->begin_time) && $data->begin_time = strtotime($data->begin_time);
        isset($data->end_time) && $data->end_time = strtotime($data->end_time);
        $result = $this->ballot->updateData($data,$where);
        if(!$result){
            return $this->export(false,'数据无变化',$result);
        }
        return $this->export(true,'成功',$result);

    }


}