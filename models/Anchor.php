<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "anchor".
 *
 * @property integer $anchor_id
 * @property string $anchor_name
 * @property string $thumb
 * @property string $backimage
 * @property string $qrcode
 * @property string $platform
 * @property string $broadcast
 * @property string $description
 * @property integer $create_time
 * @property integer $modify_time
 * @property integer $last_time
 */
class Anchor extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'anchor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time', 'last_time'], 'integer'],
            [['anchor_name'], 'string', 'max' => 16],
            [['thumb', 'backimage', 'qrcode', 'broadcast'], 'string', 'max' => 128],
            [['platform'], 'string', 'max' => 8],
            [['description'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'anchor_id' => 'Anchor ID',
            'anchor_name' => 'Anchor Name',
            'thumb' => 'Thumb',
            'backimage' => 'Backimage',
            'qrcode' => 'Qrcode',
            'platform' => 'Platform',
            'broadcast' => 'Broadcast',
            'description' => 'Description',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'last_time' => 'Last Time',
        ];
    }
}
