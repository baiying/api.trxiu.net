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
use app\models\Canvass;
use app\models\Fans;
use app\models\VoteLog;
use Yii;
use app\service\BaseService;
use app\models\BallotEAnchor;
use app\models\Ballot;

class BallotService extends BaseService
{
    private $ballot;
    private $ballotEAnchor;
    private $anchor;
    private $canvass;
    private $vote_log;
    private $fans;

    /**
     * 初始化活动
     */
    public function initBallot($data){
        isset($data['begin_time']) && $data['begin_time'] = (int)strtotime($data['begin_time']);
        isset($data['end_time']) && $data['end_time'] = (int)strtotime($data['end_time']);
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
        isset($data['begin_time']) && $data['begin_time'] = (int)strtotime($data['begin_time']);
        isset($data['end_time']) && $data['end_time'] = (int)strtotime($data['end_time']);
        $this->ballot = new Ballot();
        $this->ballot->attributes = $data;
        if(!$this->ballot->validate()) {
            return $this->export(false,'属性验证失败',$this->ballot->errors);
        }
        // $data = (object)$data;
//        $data->update_time = time();
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
        $this->fans = new Fans();

        // 所有的查询都在主服务器上执行
        $result = $this->ballot->getRow('*',$where);
        $result['votes_total'] = $result['votes'] + $result['votes_amend'];
        $result['anchorList'] = $this->ballotEAnchor->getList('*',$where);

        foreach ($result['anchorList'] as $key => $item){
            $anchorIdList[] = $item['anchor_id'];
            $result['anchorList'][$key]['votes_total'] = $result['anchorList'][$key]['votes'] + $result['anchorList'][$key]['votes_amend'];
        }
        if(isset($anchorIdList)){
            $anchorInformationList = $this->anchor->getList();
            $anchorWhere = array();
            foreach ($anchorInformationList as $item){
                $anchorWhere['anchor_id'][] = $item['anchor_id'];
            }
            $fansList = $this->fans->getList('*',$anchorWhere);
            foreach ($anchorInformationList as $key=>$value){
                $anchorInformationList[$key]['anchor_name'] = '';
                $anchorInformationList[$key]['thumb'] = '';
                foreach ($fansList as $item){
                    if($item['anchor_id'] == $value['anchor_id']){
                        $anchorInformationList[$key]['anchor_name'] = $item['wx_name'];
                        $anchorInformationList[$key]['thumb'] = $item['wx_thumb'];
                    }
                }
            }
//            echo json_encode($anchorInformationList);exit;
        }
        if(isset($anchorIdList) && isset($anchorInformationList)){
            foreach ($result['anchorList'] as $key => $value){
                foreach ($anchorInformationList as $item){
                    if ($item['anchor_id'] == $value['anchor_id']){
                        $result['anchorList'][$key]['Information'] = $item;
                    }
                }
                if(!isset($result['anchorList'][$key]['Information'])){
                    $result['anchorList'][$key]['Information']['anchor_id'] = $value['anchor_id'];
                    $result['anchorList'][$key]['Information']['backimage'] = '';
                    $result['anchorList'][$key]['Information']['qrcode'] = '';
                    $result['anchorList'][$key]['Information']['platform'] = '';
                    $result['anchorList'][$key]['Information']['broadcast'] = '';
                    $result['anchorList'][$key]['Information']['description'] = '';
                    $result['anchorList'][$key]['Information']['create_time'] = '';
                    $result['anchorList'][$key]['Information']['modify_time'] = '';
                    $result['anchorList'][$key]['Information']['last_time'] = '';
                    $result['anchorList'][$key]['Information']['anchor_name'] = '';
                    $result['anchorList'][$key]['Information']['thumb'] = '';
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
     * 修改主播票数修正值
     */
    public function upVotesAmend($ballot_anchor_id ,$amendNum){
        $this->ballotEAnchor = new BallotEAnchor();
        $this->ballot = new Ballot();
        //验证主播
        $this->ballotEAnchor = BallotEAnchor::findOne(['ballot_anchor_id'=>$ballot_anchor_id]);
        if(!$this->ballotEAnchor){
            return $this->export(false,'ID不存在');
        }
        $ballot_id = $this->ballotEAnchor->ballot_id;
        $this->ballot = Ballot::findOne(['ballot_id'=>$ballot_id]);
        if(!$this->ballot){
            return $this->export(false,'活动不存在');
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $this->ballot->votes_amend = $amendNum;
            $result = $this->ballot->save();
            if(!$result){
                return $this->export(false,'更新失败,活动票数修正未成功',$result);
            }
            $this->ballotEAnchor->votes_amend = $amendNum;
            $this->ballotEAnchor->save();
            if(!$result){
                return $this->export(false,'更新失败,主播票数修正未成功',$result);
            }
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
    public function addVotes($data){
        //验证必填参数
        if(!isset($data['ballot_id']) || !isset($data['anchor_id']) || !isset($data['fans_id']) || !isset($data['votes'])){
            return $this->export(false,'缺失参数','ballot_id,anchor_id,fans_id,votes');
        }
        //拼装条件
        $serverNum =  Yii::$app->id;
        list($t1, $t2) = explode(' ', microtime());
        $time = $t2 .ceil( ($t1 * 1000) );
        $rand = rand(1000,9999);
        $canvass_id = $serverNum.$time.$rand;
        $canvassData['canvass_id'] = $canvass_id;
        $ballotWhere['ballot_id'] = $data['ballot_id'];
        $ballotEAnchorWhere['ballot_id'] = $data['ballot_id'];
        $ballotEAnchorWhere['anchor_id'] = $data['anchor_id'];


        $serverNum =  Yii::$app->id;
        list($t1, $t2) = explode(' ', microtime());
        $time = $t2 .ceil( ($t1 * 1000) );
        $rand = rand(1000,9999);
        $canvass_id = $serverNum.$time.$rand;
        $canvassData['canvass_id'] = $canvass_id;
        $canvassData['ballot_id'] = $data['ballot_id'];
        $canvassData['anchor_id'] = $data['anchor_id'];
        $canvassData['fans_id'] = $data['fans_id'];


        $serverNum =  Yii::$app->id;
        list($t1, $t2) = explode(' ', microtime());
        $time = $t2 .ceil( ($t1 * 1000) );
        $rand = rand(1000,9999);
        $vote_id = $serverNum.$time.$rand;
        $votes = isset($data['votes']) ? $data['votes'] : 1;
        $vote_logData['vote_id'] = $vote_id;
        $vote_logData['ballot_id'] = $data['ballot_id'];
        $vote_logData['anchor_id'] = $data['anchor_id'];
        $vote_logData['fans_id'] = $data['fans_id'];
        isset($data['amount']) && $canvassData['amount'] = $data['amount'];
        isset($data['url']) && $canvassData['url'] = $data['url'];
        isset($data['status']) && $canvassData['status'] = $data['status'];
        isset($data['create_time']) && $canvassData['create_time'] = $data['create_time'];
        isset($data['active_time']) && $canvassData['active_time'] = $data['active_time'];
        isset($data['end_time']) && $canvassData['end_time'] = $data['end_time'];
        isset($data['refund']) && $canvassData['refund'] = $data['refund'];
        isset($data['create_time']) && $vote_logData['create_time'] = $data['create_time'];
        isset($data['new_fans']) && $vote_logData['new_fans'] = $data['new_fans'];

        $this->ballotEAnchor = new BallotEAnchor();
        $this->ballot = new Ballot();
        $this->canvass = new Canvass();
        $this->vote_log = new VoteLog();
        $this->fans = new Fans();
        //验证
        $result = $this->ballot->getRow('*',$ballotWhere);
        if(!$result){
            return $this->export(false,'活动不存在',$result);
        }
        $result = $this->ballotEAnchor->getRow('*',$ballotEAnchorWhere);
        if(!$result){
            return $this->export(false,'该主播没有参加活动',$result);
        }
        $fansWhere['fans_id'] = $data['fans_id'];
        $result = $this->fans->getRow('*',$fansWhere);
        if(!$result){
            return $this->export(false,'您还没有注册过，请授权登录后投票',$result);
        }
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $ballotEAnchor = BallotEAnchor::findOne($ballotEAnchorWhere);
            $ballotEAnchor->votes += $votes;
            $result = $ballotEAnchor->save();
            if(!$result){
                return $this->export(false,'投票操作失败',$result);
            }
            $ballotEAnchor = Ballot::findOne($ballotWhere);
            $ballotEAnchor->votes += $votes;
            $result = $ballotEAnchor->save();
            if(!$result){
                return $this->export(false,'投票操作失败',$result);
            }
            $this->canvass->attributes = $canvassData;
            if(!$this->canvass->validate()) {
                return $this->export(false,'属性验证失败',$this->canvass->errors);
            }
            $result = $this->canvass->insertData($canvassData);
            if(!$result){
                return $this->export(false,'拉票信息插入失败',$result);
            }
            $this->vote_log->attributes = $vote_logData;
            if(!$this->vote_log->validate()) {
                return $this->export(false,'属性验证失败',$this->vote_log->errors);
            }
            $result = $this->vote_log->insertData($vote_logData);
            if(!$result){
                return $this->export(false,'投票记录插入失败',$result);
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

    /**
     * 领取红包
     * @param $data
     * @return array|mixed
     */
    public function getRedPacket($data){
        //验证必填参数
        if(!isset($data['ballot_id']) || !isset($data['anchor_id']) || !isset($data['fans_id']) || !isset($data['canvass_id'])){
            return $this->export(false,'缺失参数','ballot_id,anchor_id,fans_id,votes');
        }
        $this->ballotEAnchor = new BallotEAnchor();
        $this->ballot = new Ballot();
        $this->canvass = new Canvass();
        $this->vote_log = new VoteLog();
        $this->fans = new Fans();
        $fansWhere['fans_id'] = $data['fans_id'];
        $result = $this->fans->getRow('*',$fansWhere);
        if(!$result){
            return $this->export(false,'您还没有注册过，请授权登录后投票',$result);
        }
        $where['ballot_id'] = $data['ballot_id'];
        $where['anchor_id'] = $data['anchor_id'];
        $where['canvass_id'] = $data['canvass_id'];
        $result = $this->canvass->getRow('*',$where);
        if(!$result||$result['amount']==0){
            return $this->export(false,'您领取的红包不存在，或已经过期',$result);
        }
        $amount = $result['amount'];
        $vote_logRowWhere['ballot_id'] = $data['ballot_id'];
        $vote_logRowWhere['anchor_id'] = $data['anchor_id'];
        $vote_logRowWhere['fans_id'] = $data['fans_id'];
        $vote_logRowWhere['canvass_id'] = $data['canvass_id'];
        $result = $this->vote_log->getRow('*',$vote_logRowWhere);//验证是否领取过
        if($result){
            return $this->export(false,'您已经领取过红包了哦！请勿重复领取',$result);
        }
        $vote_logWhere['ballot_id'] = $data['ballot_id'];
        $vote_logWhere['anchor_id'] = $data['anchor_id'];
        $vote_logWhere['canvass_id'] = $data['canvass_id'];
        $result = $this->vote_log->getList('*',$vote_logWhere);
        $countNum = count($result);
        $lost = 0;
        foreach ($result as $item){
            $lost += $item['earn'];
        }
        $total = $num = $this->computeNum($amount);
        $ranking = $countNum+1;
        $total = $total-$lost;
        if($ranking>$num){
            return $this->export(false,'红包已经被领完了哦',$result);
        }
        $result = $this->computeRedPacket($total,$num,$ranking);
        if(!$result['status']||!$result['data']){
            return $this->export(false,'红包计算失败',$result['message']);
        }
        $remaining = $result['data']['total'];//余额
        $money = $result['data']['money'];//领取到
        $ranking = $result['data']['ranking'];//领取排序

        $serverNum =  Yii::$app->id;
        list($t1, $t2) = explode(' ', microtime());
        $time = $t2 .ceil( ($t1 * 1000) );
        $rand = rand(1000,9999);
        $vote_id = $serverNum.$time.$rand;
        $vote_logData['ballot_id'] = $data['ballot_id'];
        $vote_logData['anchor_id'] = $data['anchor_id'];
        $vote_logData['fans_id'] = $data['fans_id'];
        $vote_logData['canvass_id'] = $data['canvass_id'];
        $vote_logData['vote_id'] = $vote_id;
        $vote_logData['create_time'] = time();
        $vote_logData['earn'] = $money;
        isset($data['new_fans']) && $vote_logData['new_fans'] = $data['new_fans'];

        $this->vote_log->attributes = $vote_logData;
        if(!$this->vote_log->validate()) {
            return $this->export(false,'属性验证失败',$this->vote_log->errors);
        }
        $result = $this->vote_log->insertData($vote_logData);
        if(!$result){
            return $this->export(false,'操作失败',$result);
        }
        return $this->export(true,'成功','操作成功,您是第'.$ranking.'位领取的朋友，您领取到了'.$money.'元。'.'还剩'.$remaining.'元待领取。');

    }


    /**
     * 查看当前红包
     * @param $canvass_id
     */
    public function checkRedPacket($canvass_id){

        $this->ballotEAnchor = new BallotEAnchor();
        $this->ballot = new Ballot();
        $this->canvass = new Canvass();
        $this->vote_log = new VoteLog();
        $this->fans = new Fans();
        $this->anchor = new Anchor();
        $where['canvass_id'] = $canvass_id;
        $redPacket = $this->canvass->getRow('*',$where);
        if(!$redPacket||$redPacket['amount']==0){
            return $this->export(false,'您领取的红包不存在，或已经过期',$redPacket);
        }
        $bollotWhere['ballot_id'] = $redPacket['ballot_id'];
        $ballot = $this->ballot->getRow('*',$bollotWhere);
        if(!$ballot){
            return $this->export(false,'活动不存在',$ballot);
        }
        $ballotEAnchorWhere['anchor_id'] = $redPacket['anchor_id'];
        $ballotEAnchorWhere['ballot_id'] = $redPacket['ballot_id'];
        $ballotEAnchor = $this->ballotEAnchor->getRow('*',$ballotEAnchorWhere);
        if(!$ballotEAnchor){
            return $this->export(false,'该主播没有参加活动',$ballotEAnchor);
        }
        $anchorWhere['anchor_id'] = $redPacket['anchor_id'];
        $anchor = $this->anchor->getRow('*',$anchorWhere);
        if(!$anchor){
            return $this->export(false,'主播不存在',$anchor);
        }
        $fansWhere['fans_id'] = $redPacket['fans_id'];
        $fans = $this->fans->getRow('*',$fansWhere);
        if(!$fans){
            return $this->export(false,'该用户不存在',$fans);
        }
        $amount = $redPacket['amount'];
        $vote_logWhere['canvass_id'] = $canvass_id;
        $vote_logList = $this->vote_log->getList('*',$vote_logWhere);
        $countNum = count($vote_logList);
        $lost = 0;
        foreach ($vote_logList as $item){
            $lost += $item['earn'];
        }
        $total = $num = $this->computeNum($amount);
        $residue = $num - $countNum;
        $total = $total-$lost;
        $rs['amount'] = $amount;
        $rs['num'] = $num;
        $rs['countNum'] = $countNum;
        $rs['lost'] = $lost;
        $rs['residue'] = $residue;
        $rs['total'] = $total;
        $rs['ballot'] = $ballot;
        $rs['redPacket'] = $redPacket;
        $rs['anchor'] = $anchor;
        $rs['fans'] = $fans;
        $rs['vote_logList'] = $vote_logList;

        return $this->export(true,'成功',$rs);


    }

    /**
     * 计算红包个数
     */
    public function computeNum($total){
        if($total%2!=0){
            $total = floor($total)+1;//舍去取整加一
        }
        $num = round($total/2);
        return $num;
    }


    /**
     * 计算红包
     * @红包总金额 $total
     * @拆分个数 $num
     * @当前排位 $ranking
     */
    public function computeRedPacket($total,$num,$ranking){
        if($ranking < $num){
            $restrictArr = [
                'default'=>['min'=>0.01,'max'=>2.50,],
                'max'=>['min'=>2.00,'max'=>2.50,],
                'min'=>['min'=>0.01,'max'=>0.50,],
                [
                    'min'=>1.20,'max'=>1.30,
                ],[
                    'min'=>1.10,'max'=>1.40,
                ],[
                    'min'=>1.00,'max'=>1.50,
                ],[
                    'min'=>0.90,'max'=>1.60,
                ],[
                    'min'=>0.80,'max'=>1.70,
                ],[
                    'min'=>0.70,'max'=>1.80,
                ],[
                    'min'=>0.60,'max'=>1.90,
                ],[
                    'min'=>0.50,'max'=>2.00,
                ],[
                    'min'=>0.40,'max'=>2.10,
                ],[
                    'min'=>0.30,'max'=>2.20,
                ],[
                    'min'=>0.20,'max'=>2.30,
                ],[
                    'min'=>1.10,'max'=>1.40,
                ],
            ];
            $restrict = $restrictArr[array_rand($restrictArr,1)];
            $this->randRedPacket($money,$restrict,$total,$ranking,$restrictArr,$num,$restrictArr['max']['min']);
        }else{
            $money = round($total,2);
        }

        $total=round($total-$money,2);
//        $result =  '你是第'.$ranking.'个领取红包的人领取金额：'.$money.' 元，余额：'.$total.' 元 <br>';
        $result = [
            'ranking' => $ranking,
            'money' => $money,
            'total' => $total,
        ];
        return $this->export(true,'成功',$result);

    }
    /**
     * changeStatus
     * 遍历全部待进行和进行中的活动，自动更新活动状态
     */
    public function changeStatus() {
        // 处理待进行的活动
        $ballots = Ballot::find()->where(['status'=>2])->all();
        if(!empty($ballots)) {
            foreach($ballots as $item) {
                if($item->begin_time < time() && $item->end_time > time()) {
                    $item->status = 1;
                    $item->save();
                }
            }
        }
        // 处理进行中活动
        $ballots = Ballot::find()->where(['status'=>1])->all();
        if(!empty($ballots)) {
            foreach($ballots as $item) {
                if($item->end_time < time()) {
                    $item->status = 3;
                    $item->save();
                }
            }
        }
    }
    /**
     * @最小值 $min
     * @安全上限 $safe_total
     * @取值区间 $restrict
     * @总金额 $total
     * @return float
     */
    private function randRedPacket(&$money,$restrict,$total,$ranking,$restrictArr,$num,$min=0.01){
//        $min=0.01;//每个人最少能收到0.01元
        $safe_total=($total-($num-$ranking)*$min)/($num-$ranking);//随机安全上限
        if($safe_total>$restrict['max']||$safe_total<$restrict['min']){
            $safe_total = $restrict['max'];
        }
        $money = round(mt_rand($restrict['min'],$safe_total*100)/100,2);
        if(($total-$money)/($num-$ranking) > $restrictArr['max']['min']){
            $restrict = $restrictArr['max'];
            $this->randRedPacket($money,$restrict,$total,$ranking,$restrictArr,$num,$restrictArr['max']['min']);
        }
        if(($total-$money)/($num-$ranking) < $restrictArr['min']['min']){
            $restrict = $restrictArr['min'];
            $this->randRedPacket($money,$restrict,$total,$ranking,$restrictArr,$num,$restrictArr['max']['min']);
        }
    }

}
