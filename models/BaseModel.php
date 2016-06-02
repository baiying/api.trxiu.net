<?php

namespace app\models;

use app\components\ApiCode;
use Yii;
use yii\db\Query;

class BaseModel extends \yii\db\ActiveRecord
{
    private $primaryConnection;
    private $secondaryConnection;


    public function init()
    {

        $this->primaryConnection = Yii::$app->db;
        $this->secondaryConnection = Yii::$app->db;
    }


    public function getList($select = "*", $where = array(), $ext = array())
    {

        $query = new Query();
        $query = $query->from($this->tableName());
        $this->getWhere($where,$query);
        if(isset($ext['like'])){
            $this->getLike($ext['like'],$query);
        }
        if (is_array($select)) {
            $select = implode(",", $select);
        }
        $query = $query->select($select);
        isset($ext['groupBy']) && $query->groupBy($ext['groupBy']);
        isset($ext['orderBy']) && $query->orderBy($ext['orderBy']);
        if (isset($ext['limit'])) {
            if (is_array($ext['limit'])) {
                $query->limit($ext['limit']['size']);
                $query->offset($ext['limit']['start']);
//                echo json_encode($query);exit;
            } else {
                $query->limit($ext['limit']);
            }
        }
        $result = $query->all();
//        $commandQuery = clone $query;
//        echo $commandQuery->createCommand()->getRawSql();exit;
        return $result;
    }

    public function getListAndLimit($select = "*", $where = "", $ext = array())
    {
        $ext['limit']['size'] = isset($ext['limit']['size']) ? $ext['limit']['size'] : 1;
        $ext['limit']['start'] = isset($ext['limit']['start']) ? $ext['limit']['start'] : 0;
        $result['list'] = $this->getList($select,$where,$ext);
        $query = new Query();
        $query = $query->from($this->tableName());
        //获取列表行数
        if(isset($ext['limit'])){
            $this->getWhere($where,$query);
            if(isset($ext['like'])){
                $this->getLike($ext['like'],$query);
            }
            $sumcount = $query->count();
            $conpage = ceil($sumcount / $ext['limit']['size']);
            $result['total'] = $sumcount;
            $result['pagecount'] = ($conpage == 0) ? 1 : $conpage;

        }
        return $result;
    }

    protected function getWhere($where,&$query){

        $query = $query->where($where);

        if ($where) {
            if (is_array($where)) {
                foreach ($where as $key => $item) {
                    if (is_array($item)) {
//                        echo json_encode($key);exit;
                        $query = $query->where(['in',$key, $item]);
                    } else if (is_numeric($key)) {
                        $query = $query->where($item);
                    } else {
                        $query = $query->where($where);
                    }
                }
            } else {
                $query->where($where);
            }
        }
    }

    protected function getLike(array $like,&$query){
        if ($like) {
            if (is_array($like)) {
                foreach ($like as $key => $item) {
                    if (is_array($item)) {

                    }else {
                        $query = $query->where('like', $key, '%'.$item.'%');
                    }
                }
            }
        }
    }

    public function insertData($data,$dataValueIsNull = false)
    {
        if (is_array($data)) $data = (object)$data;
        $data->create_time = time();
        if($dataValueIsNull == false){
            foreach ($data as $k => $v) {
                if (is_null($v)) {
                    unset($data->$k);
                }
            }
        }
        unset($data->tableObject);  //解决以$this为数据对象时，public属性被添加到数据对象问题
        return $this->primaryConnection->createCommand()->insert($this->tableName(),$data)->execute();
    }

    public function updateData($data, $where = '',$dataValueIsNull = false)
    {
        if (is_array($data)) $data = (object)$data;
        if($dataValueIsNull == false){
            foreach ($data as $k => $v) {
                if (is_null($v)) {
                    unset($data->$k);
                }
            }
        }
        unset($data->tableObject);
        return $this->primaryConnection->createCommand()->update($this->tableName(),$data,$where)->execute();

    }

    public function insertOrUpdate($data, $uKey, $updateIgnore = array(), $isUpdate = true)
    {
        $query = new Query();
        if (is_array($data)) $data = (object)$data;

        $query = $query->from($this->tableName());
        $query = $query->where([$uKey => $data->$uKey]);
        $result = $query->all();
        if ($result) {
            //update
            $where = array($uKey => $data->$uKey);
            unset($data->$uKey);
            if ($updateIgnore) {
                foreach ($updateIgnore as $ignore) {
                    unset($data->$ignore);
                }
            }
            if ($isUpdate) {
                return $this->primaryConnection->createCommand()->update($this->tableName(),$data,$where)->execute();
            }
        } else {
            //insert
            return $this->primaryConnection->createCommand()->insert($this->tableName(),$data)->execute();
        }

        return true;
    }


    public function getRow($select = "*", $where = "")
    {

        $query = new Query();
        $query = $query->from($this->tableName());
        $this->getWhere($where,$query);
        if (is_array($select)) {
            $select = implode(",", $select);
        }

        $query = $query->limit(1);
        $query = $query->select($select);
        $result = $query->one();
        return $result;
    }

    public function delRow($where)
    {
        $query = new Query();
        $query = $query->from($this->tableName());
        $query = $query->where($where);
        $result = $query->one();
        if (!$result) {
            return ApiCode::ERROR_API_NOTEXIST;
        }
        return $this->primaryConnection->createCommand()->delete($this->tableName(),$where)->execute();
    }


}
