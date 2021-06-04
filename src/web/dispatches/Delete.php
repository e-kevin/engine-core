<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\web\dispatches;

use EngineCore\db\ActiveRecord;
use EngineCore\dispatch\Dispatch;
use EngineCore\helpers\StringHelper;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * 根据模型`$modelClass`删除指定的数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Delete extends Dispatch
{
    
    /**
     * @var string|ActiveRecord 需要操作的模型对象名, e.g. `User::class`
     */
    public $modelClass = null;
    
    /**
     * @var boolean 是否标记删除而非真实删除，默认为标记删除
     */
    public $markAsDeleted = true;
    
    /**
     * @var string 标记删除的操作字段
     */
    public $deletedMarkAttribute = 'is_active';
    
    /**
     * @var integer 标记删除的值 e.g. 0为删除，1为激活
     */
    public $deletedMarkValue = 0;
    
    /**
     * @var string|array 操作成功后需要跳转的地址，默认跳转到来源地址
     */
    public $successJumpUrl = null;
    
    /**
     * @var string|array 操作失败后需要跳转的地址，默认跳转到来源地址
     */
    public $errorJumpUrl = null;
    
    /**
     * @var array|string 附带进跳转地址里的url地址参数，仅在`$successJumpUrl`或`$errorJumpUrl`不为`null`时生效
     *  - `string`: 自动从当前请求地址里提取参数，如：'name, name2'。如果请求地址里不存在指定参数，则抛出异常
     *  - `array`: 自定义url地址参数，如：['name' => 'value', 'name2' => 'value2']
     */
    public $urlParams = [];
    
    /**
     * @var string 操作成功后的提示消息
     */
    public $successMessage;
    
    /**
     * @var string 操作失败后的提示消息
     */
    public $errorMessage;
    
    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     */
    public function init()
    {
        if (null !== $this->successJumpUrl || null !== $this->errorJumpUrl) {
            if (!empty($this->urlParams) && is_string($this->urlParams)) {
                $params = [];
                foreach (StringHelper::stringToArray($this->urlParams) as $param) {
                    $params[$param] = Yii::$app->getRequest()->getQueryParam($param);
                    if ($params[$param] === null) {
                        throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                            'params' => $param,
                        ]));
                    }
                }
                $this->urlParams = $params;
            }
        }
    }
    
    /**
     * @param bool $res
     *
     * @return \yii\web\Response
     */
    protected function getResult($res)
    {
        if ($res) {
            return $this->response->success(
                $this->successMessage ?: Yii::t('ec/app', 'Delete successful.'),
                $this->successJumpUrl ? array_merge([$this->successJumpUrl], $this->urlParams) : Yii::$app->getRequest()->getReferrer()
            );
        } else {
            return $this->response->error(
                $this->errorMessage ?: Yii::t('ec/app', 'Delete failure.'),
                $this->errorJumpUrl ? array_merge([$this->errorJumpUrl], $this->urlParams) : Yii::$app->getRequest()->getReferrer()
            );
        }
    }
    
}