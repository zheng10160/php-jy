<?php
namespace app\models;

use core\base\Model;
use core\db\Db;

/**
 * 用户Model
 */
class Item extends Model
{

    /**
     * 数据库名称 严格对应配置文件
     * @var string
     */
    protected $db_name = 'hardware';


    /**
     * 自定义当前模型操作的数据库表名称，
     * 如果不指定，默认为类名称的小写字符串，
     * 这里就是 item 表
     * @var string
     */
    protected $table = 'item';

    /**
     * model使用方法
     * 搜索功能，因为Sql父类里面没有现成的like搜索，
     * 所以需要自己写SQL语句，对数据库的操作应该都放
     * 在Model里面，然后提供给Controller直接调用
     * @param $title string 查询的关键词
     * @return array 返回的数据
     */
    public function search($keyword)
    {
        $sql = "select * from `$this->table` where `item_name` like :keyword";
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, [':keyword' => "%$keyword%"]);
        $sth->execute();

        return $sth->fetchAll();
    }

    /**
     * 例子
     * @param $a
     * @param $b
     * @return mixed
     */
    public function sp_s($a,$b)
    {
        $spname = 'sp_km_command_type_select_all';

        return $this->exec_sp_assoc("call " . $spname . "('"
            . $a . "','"
            . $b .
            "')");
    }

}