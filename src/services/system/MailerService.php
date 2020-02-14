<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\base\Service;
use EngineCore\services\System;
use Yii;

/**
 * 发送邮件服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MailerService extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * @var string
     */
    public $viewPath = '@common/mail';
    
    /**
     * @var string|array 默认为：`Yii::$app->params['adminEmail']` 或 `no-reply@example.com`
     */
    public $sender;
    
    /**
     * @var string|\yii\mail\BaseMailer 默认为：`Yii::$app->getMailer()`
     */
    public $mailer;
    
    /**
     * 发送邮件
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array  $params
     *
     * @return bool
     */
    public function send($to, $subject, $view, $params = []): bool
    {
        $this->mailer = $this->mailer === null ? Yii::$app->getMailer() : Yii::$app->get($this->mailer);
        $this->mailer->viewPath = $this->viewPath;
        $this->mailer->getView()->theme = Yii::$app->view->theme;
        
        if ($this->sender === null) {
            $this->sender = Yii::$app->params['adminEmail'] ?? 'no-reply@example.com';
        }
        
        return $this->mailer->compose($view, $params)
                            ->setTo($to)
                            ->setFrom($this->sender)
                            ->setSubject($subject)
                            ->send();
    }
    
}