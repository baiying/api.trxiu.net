<?php
namespace app\service;

use Yii;
use app\service\BaseService;
use app\models\Manager;

class CurdService extends BaseService {
    /**
     * 添加数据记录
     * @param object $model 模型对象实例
     * @param array $data   添加的数据，以字段名为键，添加的数据为值
     */
    public function createRecord($modelName, $data) {
        $primaryKeyName = $modelName::primaryKey()[0];
        $model = new $modelName(['scenario' => 'create']);
        $model->attributes = $data;
        if(!$model->validate()) {
            $this->errLog($model->errors, $modelName);
            return $this->export(FALSE, $modelName.'属性验证失败', $model->errors);
        } else {
            if($model->save()) {
                return $this->export(TRUE, $modelName.'数据添加成功', [$primaryKeyName=>$model->$primaryKeyName]);
            } else {
                return $this->export(TRUE, $modelName.'数据添加失败', $model->errors);
            }
        }
    }
    /**
     * 修改数据记录
     * @param string $modelName 模型对象名称
     * @param array  $filter    数据记录查询条件，字段名做为键值，查询条件作为值
     * @param array  $data      修改的数据数组，字段名做为键值，查询条件作为值
     * @return array
     */
    public function updateRecord($modelName, $filter = [], $data = []) {
        if(empty($filter)) {
            return $this->export(FALSE, '缺少filter参数');
        }
        $model = $modelName::findOne($filter);
        if(empty($model)) {
            return $this->export(FALSE, '未查询到符合条件的记录');
        }
        $model->attributes = $data;
        if(!$model->validate()) {
            $this->errLog($model->errors, $modelName);
            return $this->export(FALSE, $modelName.'属性验证失败', $model->errors);
        } else {
            if($model->save()) {
                return $this->export(TRUE, $modelName.'更新成功');
            } else {
                return $this->export(FALSE, $modelName.'更新失败', $model->errors);
            }
        }
    }
    /**
     * 删除数据记录
     * @param string $modelName 模型名称
     * @param array  $filter    查询条件数组
     * @return multitype:string number NULL |multitype:string number
     */
    public function deleteRecord($modelName, $filter = []) {
        $records = $modelName::deleteAll($filter);
        if($records) {
            return $this->export(TRUE, '记录删除成功', $records);
        } else {
            return $this->export(TRUE, '没有需要删除的数据', $records);
        }
    }
    /**
     * 查询单条数据
     * @param string        $modelName  模型名称
     * @param string|array  $where      查询条件
     */
    public function fetchOne($modelName, $where) {
        // 获取符合条件的记录
        $query = $modelName::findOne($where);
        return $this->export(TRUE, '查询操作执行成功', $query);
    }
    /**
     * 查询数据列表
     * @param string        $modelName  模型名称
     * @param string|array  $where      查询条件
     * @param array         $args       附加条件
     */
    public function fetchAll($modelName, $where, $args = []) {
        // 计算符合条件的记录总数
        $count = $modelName::find()->where($where)->count();
        // 获取符合条件的记录
        $query = $modelName::find();
        if((is_array($where) && !empty($where)) || $where != "") {
            $query = $query->where($where);
        }
        if(isset($args['order'])) {
            $query = $query->orderBy($args['order']);
        }
        if(isset($args['page']) && isset($args['pagesize'])) {
            $offset = ($args['page'] - 1) * $args['pagesize'];
            $query = $query->offset($offset)->limit($args['pagesize']);
        }
        return $this->export(TRUE, '查询操作执行成功', ['count'=>$count, 'data'=>$query->all()]);
    }
    /**
     * 记录CURD操作中出现的错误日志
     * 本方法仅适用由model操作引起的错误
     * @param unknown $errors
     */
    private function errLog($errors, $modelName = '') {
        // 错误日志文件
        // 如果数据库操作错误，则记录日志
        $errLog = "======== " . date("Y-m-d H:i:s") . " ========\n";
        if(!empty($errors)) {
            foreach($errors as $key=>$val) {
                foreach($val as $err) {
                    $errLog .= $key . ": " . $err . "\n";
                }
            }
        }
        if(empty($modelName) === FALSE){
            $errLog .= "======== " .$modelName. "\n";
        }
        Yii::warning($errLog, 'service_warning');
    }
}