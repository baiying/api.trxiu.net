<?php
namespace app\components;

use Yii;
use yii\base\Component;

class ApiCode extends Component {
    const ERROR_SECRET_INVALID      = -1;
    const SUCCESS                   = 200;
    const ERROR_API_DENY            = 400;
    const ERROR_API_NOTEXIST        = 404;
    const ERROR_API_FAILED          = 500;
}