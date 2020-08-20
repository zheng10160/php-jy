<?php
namespace app\controllers;

use core\base\Controller;
use app\models\Item;

use core\redis\RedisHandler;
class ItemController extends Controller
{
    protected static $redis;

    //注意 一下db 操作方法有两种
    // 1: 基于model 对象操作
    // 2：存储过程操作
    //
    //redis操作方法
    public function cs()
    {
        self::$redis = RedisHandler::getInstance($this->_config['redis']);
    }

    //执行存储过程操作
    public function t_sp()
    {

        $res = (new Item())->sp_s('a','b');
    }

    public function index()
    {
        echo 'welcome';
    }

    // 首页方法，测试框架自定义DB查询
    public function select_a()
    {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        if ($keyword) {
            $items = (new Item())->search($keyword);
        } else {
            // 查询所有内容，并按倒序排列输出
            // where()方法可不传入参数，或者省略
            $items = (new Item)->where()->order(['id DESC'])->fetchAll();
        }

    }

    // 查看单条记录详情
    public function detail($id)
    {
        // 通过?占位符传入$id参数
        $item = (new Item())->where(["id = ?"], [$id])->fetch();

       var_dump($item);
    }
    
    // 添加记录，测试框架DB记录创建（Create）
    public function add()
    {
        $data['item_name'] = $_POST['value'];
        $count = (new Item)->add($data);


    }
    
    // 操作管理
    public function manage($id = 0)
    {
        $item = array();
        if ($id) {
            // 通过名称占位符传入参数
            $item = (new Item())->where(["id = :id"], [':id' => $id])->fetch();
        }

    }
    
    // 更新记录，测试框架DB记录更新（Update）
    public function update()
    {
        $data = array('id' => $_POST['id'], 'item_name' => $_POST['value']);
        $count = (new Item)->where(['id = :id'], [':id' => $data['id']])->update($data);

    }
    
    // 删除记录，测试框架DB记录删除（Delete）
    public function delete($id = null)
    {
        $count = (new Item)->delete($id);

    }
}