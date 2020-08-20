<?php
namespace core\base;

/**
 * 控制器基类
 */
class Controller
{
    protected $_controller;
    protected $_action;

    protected $_config;

    // 构造函数，初始化属性，并实例化对应模型
    //控制器
    //操作名
    //配置文件参数
    public function __construct($controller, $action,$config)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_config = $config;
    }

}