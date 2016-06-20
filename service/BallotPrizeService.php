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
     * 查询管理员列表
     * @param string|array $where      查询条件
     * @param string $args['order']    结果排序条件
     * @param number $args['page']     查询页码
     * @param number $args['pagesize'] 每页记录条数
     * @return array
     */
    public function search($where, $args = []) {
        $modelName = "app\models\BallotPrize";
        $service = new CurdService();
        return $service->fetchAll($modelName, $where, $args);
    }
}