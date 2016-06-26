<?php
namespace app\service;
/**
 * 投票服务类
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;
use app\models\VoteLog;
use app\models\BallotEAnchor;

class VoteService extends BaseService {
    /**
     * addOne
     * 投一票
     * @param number $data['ballot_id']     活动ID，必填
     * @param number $data['anchor_id']     主播ID，必填
     * @param number $data['fans_id']       粉丝ID，必填
     * @param string $data['canvass_id']    拉票ID，选填
     * @param number $data['earn]           抽取拉票红包金额，选填
     */
    public function addOne($data = []) {
        // 判断该粉丝在今天是否已经为本主播投过免费票
        $time = strtotime(date("Y-m-d", time())." 00:00:00");
        $res = VoteLog::find()->where("ballot_id = {$data['ballot_id']} AND anchor_id = {$data['anchor_id']} AND fans_id = {$data['fans_id']} AND canvass_id = '' AND create_time > $time")->all();
        if(!empty($res) && $data['canvass_id'] == "") {
            return $this->export(FALSE, '今天已经为本主播投过票了');
        }
        // 获取主播在活动中的得票数实例
        $anchorVote = BallotEAnchor::findOne(['ballot_id'=>$data['ballot_id'], 'anchor_id'=>$data['anchor_id']]);
        if(empty($anchorVote)) {
            return $this->export(FALSE, '被投票的主播并未参加本次活动');
        }
        // 数据库事务开始
        $trans = Yii::$app->db->beginTransaction();
        try{
            // 投票信息入库
            $curd = new CurdService();
            $data['vote_id'] = Yii::$app->utils->createID(Yii::$app->id);
            $data['create_time'] = time();
            $res = $curd->createRecord("app\models\VoteLog", $data);
            if($res['status']) {
                // 更新主播的票数
                $anchorVote->votes += 1;
                if($data['canvass_id'] == "") {
                    $anchorVote->vote_free += 1;
                } else {
                    $anchorVote->vote_pay += 1;
                }
                if($anchorVote->save()) {
                    $trans->commit();
                    return $this->export(TRUE, '投票成功');
                } else {
                    $trans->rollBack();
                    return $this->export(FALSE, '投票失败');
                }
            } else {
                $trans->rollBack();
                return $this->export(FALSE, $res['message'], $res['data']);
            }
            
        } catch(Exception $e) {
            $trans->rollBack();
            return $this->export(FALSE, $e->getMessage());
        }
    }
}