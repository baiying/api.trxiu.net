<?php
/**
 * Created by PhpStorm.
 * User: Maple
 * Date: 16/5/27
 * Time: 10:06
 */

namespace app\service;

use app\components\ApiCode;
use app\models\Anchor;
use Yii;
use app\service\BaseService;
use app\models\BallotEAnchor;
use app\models\Ballot;

class BallotService extends BaseService
{
    private $ballot;
    private $ballotEAnchor;
    private $anchor;

    /**
     * 初始化活动
     */
    public function initBallot($data){
        $this->ballot = new Ballot();
        $this->ballot->attributes = $data;
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
        $this->ballot->attributes = $data;
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

    /**
     * 获取活动列表
     */
    public function getBallotList($where,$ext){

        $result = $selectWhere = array();

        isset($where['current_time']) && $selectWhere[] = "begin_time <= ".$where['current_time']." and end_time >=  ".$where['current_time'];
        isset($where['begin_time']) && $selectWhere[] = "begin_time <= ".$where['begin_time'];
        isset($where['end_time']) && $selectWhere[] = "end_time >= ".$where['end_time'];
        isset($where['status']) && $selectWhere[] = "status = ".$where['status'];
        $this->ballot = new Ballot();
        $result = $this->ballot->getListAndLimit('*',$selectWhere,$ext);
        if(!$result){
            return $this->export(false,'数据获取失败',$result);
        }
        return $this->export(true,'成功',$result);
    }

    /**
     * 获取详细活动信息
     */
    public function getBallotDetail($where){

        $this->ballot = new Ballot();
        $this->ballotEAnchor = new BallotEAnchor();
        $this->anchor = new Anchor();

        // 所有的查询都在主服务器上执行
        $result = $this->ballot->getRow('*',$where);
        $result['anchorList'] = $this->ballotEAnchor->getList('*',$where);

        foreach ($result['anchorList'] as $item){
            $anchorIdList[] = $item['anchor_id'];
        }
        if(isset($anchorIdList)){
            $anchorInformationList = $this->anchor->getList();
        }
        if(isset($anchorIdList) && isset($anchorInformationList)){
            foreach ($result['anchorList'] as $key => $value){
                foreach ($anchorInformationList as $item){
                    if ($item['anchor_id'] == $value['anchor_id']){
                        $result['anchorList'][$key]['Information'] = $item;
                    }
                }

            }
        }
        if(!$result){
            return $this->export(false,'获取失败',$result);
        }
        return $this->export(true,'成功',$result);

    }

    /**
     * 主播参选
     */
    public function ballotAddAnchor($data){
        $this->ballotEAnchor = new BallotEAnchor();
        $this->ballotEAnchor->attributes = $data;
        if(!$this->ballotEAnchor->validate()) {
            return $this->export(false,'属性验证失败',$this->ballotEAnchor->errors);
        }
        $data = (object)$data;
        $where['ballot_id'] = $data->ballot_id;
        $where['anchor_id'] = $data->anchor_id;
        $result = $this->ballotEAnchor->getRow('*',$where);
        if($result) return $this->export(false,'请勿重复添加',$result);
        $result = $this->ballotEAnchor->insertData($data);
        if(!$result){
            return $this->export(false,'插入失败',$result);
        }
        return $this->export(true,'成功',$result);

    }

    /**
     * 主播退赛
     */
    public function ballotDelAnchor($where){
        $this->ballotEAnchor = new BallotEAnchor();
        $result = $this->ballotEAnchor->getRow('*',$where);
        if(!$result) return $this->export(false,'数据不存在',$result);
        $result = $this->ballotEAnchor->delRow($where);
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);

    }

    /**
     * 投票
     */
    public function addVotes($where,$votes){
        $this->ballotEAnchor = new BallotEAnchor();
        $this->ballot = new Ballot();
        $ballotWhere['ballot_id'] = $where['ballot_id'];
        $result = $this->ballot->getRow('*',$ballotWhere);
        if(!$result){
            return $this->export(false,'活动不存在',$result);
        }
        $result = $this->ballotEAnchor->getRow('*',$where);
        if(!$result){
            return $this->export(false,'该主播没有参加活动',$result);
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $ballotEAnchor = BallotEAnchor::findOne($where);
            $ballotEAnchor->votes += $votes;
            $result = $ballotEAnchor->save();
            if(!$result){
                return $this->export(false,'操作失败',$result);
            }
            $ballotEAnchor = Ballot::findOne($ballotWhere);
            $ballotEAnchor->votes += $votes;
            $result = $ballotEAnchor->save();
            if(!$result){
                return $this->export(false,'操作失败',$result);
            }
            // ... 执行其他 SQL 语句 ...
            $transaction->commit();
        } catch(Exception $e) {
            $transaction->rollBack();
            $result = false;
        }
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功',$result);

    }

}