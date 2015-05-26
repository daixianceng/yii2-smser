<?php

namespace daixianceng\smser;

/**
 * 短信发送基类
 *
 * @author Cosmo <daixianceng@gmail.com>
 */
abstract class Smser extends \yii\base\Component
{
    /**
     * 请求地址
     *
     * @var string
     */
    public $url;
    
    /**
     * 用户名
     *
     * @var string
     */
    public $username;
    
    /**
     * 密码
     *
     * @var string
     */
    protected $password;
    
    /**
     * 状态码
     *
     * @var string
     */
    protected $state;
    
    /**
     * 状态信息
     *
     * @var string
     */
    protected $message;
    
    /**
     * 发送短信
     *
     * @param string $mobile  对方手机号码
     * @param string $content 短信内容
     * @return boolean 短信是否发送成功
     */
    abstract public function send($mobile, $content);
    
    /**
     * 设置密码
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    /**
     * 获取状态码
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * 获取状态信息
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}