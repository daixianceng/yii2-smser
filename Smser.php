<?php

namespace xfstudio\smser;

use Yii;
use yii\helpers\FileHelper;

/**
 * 短信发送基类
 *
 * @author Cosmo <xfstudio@gmail.com>
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
     * 是否使用文件形式保存发送内容
     *
     * @var boolean
     */
    public $useFileTransport = true;

    /**
     * 发送短信
     *
     * @param string $mobile  对方手机号码
     * @param string $content 短信内容
     * @return boolean        短信是否发送成功
     */
    public function send($mobile, $content)
    {
        if ($this->useFileTransport && $this->_fileTransport($mobile, $content)) {
            $this->message = '短信发送成功！';
            return true;
        }

        return false;
    }

    /**
     * 发送模板短信
     *
     * @param string $mobile  对方手机号码
     * @param mixed $data     键值对
     * @param number $id      模板id
     * @return boolean        短信是否发送成功
     */
    public function sendByTemplate($mobile, $data, $id)
    {
        if ($this->useFileTransport) {
            $content = print_r($data, true);
            if ($this->_fileTransport($mobile, $content)) {
                $this->message = '短信发送成功！';
                return true;
            }
        }

        return false;
    }

    /**
     * 用于存储短信内容
     *
     * @param string $mobile
     * @param string $content
     * @throws \Exception
     * @return boolean
     */
    private function _fileTransport($mobile, $content)
    {
        $dir = Yii::getAlias('@app/runtime/smser');

        try {
            if (!FileHelper::createDirectory($dir)) {
                throw new \Exception('无法创建目录：' . $dir);
            }

            $filename = $dir . DIRECTORY_SEPARATOR . time() . mt_rand(1000, 9999) . '.msg';
            if (!touch($filename)) {
                throw new \Exception('无法创建文件：' . $filename);
            }

            if (!file_put_contents($filename, "TO - $mobile" . PHP_EOL . "CONTENT - $content")) {
                throw new \Exception('短信发送失败！');
            }

            return true;
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

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