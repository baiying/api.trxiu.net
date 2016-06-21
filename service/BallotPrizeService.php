<?php
namespace app\service;
/**
 * 活动奖项服务类
 * @bai
 */
use Yii;
use app\service\BaseService;
use app\service\CurdService;
use app\models\BallotPrize;
use app\models\BallotEAnchor;
use app\models\Fans;
use app\models\Anchor;

class BallotPrizeService extends BaseService {
    /**
     * create
     * 添加奖项
     * 
     * @param number $data['ballot_id'] 必填，活动ID
     * @param number $data['sort']      必填，奖项排序号，升序排列
     * @param string $data['level']     必填，奖项等级
     * @param string $data['title']     必填，奖品名称
     * @param string $data['logo']      必填，奖品logo图标地址
     * @param string $data['image']     必填，奖品实物图片地址
     */
    public function create($data = []) {
        $modelName = "app\models\BallotPrize";
        $service = new CurdService();
        $data['create_time'] = time();
        return $service->createRecord($modelName, $data);
    }
    /**
     * update
     * 修改奖项设置
     * @param unknown $prizeId
     * @param unknown $data
     */
    public function update($prizeId, $data = []) {
        $modelName = "app\models\BallotPrize";
        $service = new CurdService();
        return $service->updateRecord($modelName, ['prize_id'=>$prizeId], $data);
    }
    /**
     * delete
     * 删除奖项设置
     * @param unknown $prizeId
     */
    public function delete($prizeId) {
        $modelName = "app\models\BallotPrize";
        $service = new CurdService();
        return $service->deleteRecord($modelName, ['prize_id'=>$prizeId]);
    }
    /**
     * search
     * 查询活动奖项设置
     * @param number $ballotId         活动ID
     * @param string $args['order']    结果排序条件
     * @param number $args['page']     查询页码
     * @param number $args['pagesize'] 每页记录条数
     * @return array
     */
    public function search($ballotId, $args = []) {
        $service = new CurdService();
        $res = $service->fetchAll("app\models\BallotPrize", ['ballot_id'=>$ballotId], $args);
        if(!$res['status']) {
            return $res;
        }
        $prizes = $res['data']['data'];
        // 如果已经颁奖则获取获奖主播信息
        $result = [];
        foreach($prizes as $item) {
            $arr = $item->attributes;
            if($item->anchor_id > 0) {
                // 获取本活动中本主播获取的票数
                $vote = BallotEAnchor::find()->where(['ballot_id'=>$ballotId, 'anchor_id'=>$item->anchor_id])->one();
                $arr['vote'] = !empty($vote) ? $vote->votes : 0;
                
                // 获取主播基本信息
                $anchor = Anchor::findOne(['anchor_id'=>$item->anchor_id]);
                $fans = $anchor->fans;
                $arr['thumb'] = $fans->wx_thumb;
                $arr['name'] = $fans->wx_name;
                $arr['platform'] = $anchor->platform;
            }
            $result[] = $arr;
        }
        return $this->export(TRUE, '活动奖项获取成功', $result);
    }
    
}