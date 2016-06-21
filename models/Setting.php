<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property integer $id
 * @property double $fee
 * @property string $rule_vote
 * @property string $rule_red
 */
class Setting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fee'], 'number'],
            [['rule_vote', 'rule_red'], 'required'],
            [['rule_vote', 'rule_red'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fee' => 'Fee',
            'rule_vote' => 'Rule Vote',
            'rule_red' => 'Rule Red',
        ];
    }
}
