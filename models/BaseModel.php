<?php

namespace app\models;

use Yii;
use yii\db\Query;

class BaseModel extends \yii\db\ActiveRecord
{

//    public function getList($select = '*',$where = '',$ext = ''){
//        $query = new Query();
//        $query = $query->select($select);
//        $query = $query->from($this->tableName());
//        $query = $query->where($where);
//        $query = $query->all();
//        return $query;
//    }


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
        if($dataValueIsNull == false){
            foreach ($data as $k => $v) {
                if (is_null($v)) {
                    unset($data->$k);
                }
            }
        }
        unset($data->tableObject);  //解决以$this为数据对象时，public属性被添加到数据对象问题
        return $this->db->insert($this->table_name, $data);
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
        if ($where) $this->db->where($where);
        return $this->db->update($this->table_name, $data);
    }

    public function insertOrUpdate($data, $uKey, $updateIgnore = array(), $isUpdate = true)
    {
        if (is_array($data)) $data = (object)$data;

        $this->db->where($uKey, $data->$uKey);
        $count = $this->db->count_all_results($this->table_name);
        if ($count) {
            //update
            $where = array($uKey => $data->$uKey);
            unset($data->$uKey);
            if ($updateIgnore) {
                foreach ($updateIgnore as $ignore) {
                    unset($data->$ignore);
                }
            }
            if ($isUpdate) {
                return $this->update($data, $where);
            }
        } else {
            //insert
            return $this->insert($data);
        }

        return true;
    }


    public function getRow($select = "*", $where = "")
    {
        $this->getWhere($where);
        if (is_array($select)) {
            $select = implode(",", $select);
        }
        $select && $this->db->select($select);
        $this->db->limit(1);
        $query = $this->db->get($this->table_name);
        return $query->row_array();
    }

    public function delRow($where)
    {
        $this->getWhere($where);
        $del = $this->db->delete($this->table_name);
        return $del;
    }


}
