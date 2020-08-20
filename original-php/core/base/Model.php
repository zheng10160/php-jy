<?php
namespace core\base;

use \PDOStatement;
use core\library\Log;

use core\db\Db;
class Model
{
    protected $model;

    // 数据库表名
    protected $table;

    // 数据库主键
    protected $primary = 'id';

    // WHERE和ORDER拼装后的条件
    private $filter = '';

    // Pdo bindParam()绑定的参数集合
    private $param = array();

    /**
     * 数据库名称 严格对应配置文件
     * @var string
     */
    protected $db_name;

    public function __construct()
    {
        // 获取数据库表名
        if (!$this->table) {

            // 获取模型类名称
            $this->model = get_class($this);

            // 删除类名最后的 Model 字符
            $this->model = substr($this->model, 0, -5);

            // 数据库表名与类名一致
            $this->table = strtolower($this->model);
        }
    }


    /*******************************************model 对象使用****************************************************/

    /**
     * 查询条件拼接，使用方式：
     *
     * $this->where(['id = 1','and title="Web"', ...])->fetch();
     * 为防止注入，建议通过$param方式传入参数：
     * $this->where(['id = :id'], [':id' => $id])->fetch();
     *
     * @param array $where 条件
     * @return $this 当前对象
     */
    public function where($where = array(), $param = array())
    {
        if ($where) {
            $this->filter .= ' WHERE ';
            $this->filter .= implode(' ', $where);

            $this->param = $param;
        }

        return $this;
    }

    /**
     * 拼装排序条件，使用方式：
     *
     * $this->order(['id DESC', 'title ASC', ...])->fetch();
     *
     * @param array $order 排序条件
     * @return $this
     */
    public function order($order = array())
    {
        if($order) {
            $this->filter .= ' ORDER BY ';
            $this->filter .= implode(',', $order);
        }

        return $this;
    }

    // 查询所有
    public function fetchAll()
    {
        $sql = sprintf("select * from `%s` %s", $this->table, $this->filter);
        $sth = Db::pdo($this->db_name)->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();

        return $sth->fetchAll();
    }

    // 查询一条
    public function fetch()
    {
        $sql = sprintf("select * from `%s` %s", $this->table, $this->filter);
        $sth = Db::pdo($this->db_name)->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();

        return $sth->fetch();
    }

    // 根据条件 (id) 删除
    public function delete($id)
    {
        $sql = sprintf("delete from `%s` where `%s` = :%s", $this->table, $this->primary, $this->primary);
        $sth = Db::pdo($this->db_name)->prepare($sql);
        $sth = $this->formatParam($sth, [$this->primary => $id]);
        $sth->execute();

        return $sth->rowCount();
    }

    // 新增数据
    public function add($data)
    {
        $sql = sprintf("insert into `%s` %s", $this->table, $this->formatInsert($data));
        $sth = Db::pdo($this->db_name)->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();

        return $sth->rowCount();
    }

    // 修改数据
    public function update($data)
    {
        $sql = sprintf("update `%s` set %s %s", $this->table, $this->formatUpdate($data), $this->filter);
        $sth = Db::pdo($this->db_name)->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();

        return $sth->rowCount();
    }

    /**
     * 占位符绑定具体的变量值
     * @param PDOStatement $sth 要绑定的PDOStatement对象
     * @param array $params 参数，有三种类型：
     * 1）如果SQL语句用问号?占位符，那么$params应该为
     *    [$a, $b, $c]
     * 2）如果SQL语句用冒号:占位符，那么$params应该为
     *    ['a' => $a, 'b' => $b, 'c' => $c]
     *    或者
     *    [':a' => $a, ':b' => $b, ':c' => $c]
     *
     * @return PDOStatement
     */
    public function formatParam(PDOStatement $sth, $params = array())
    {
        foreach ($params as $param => &$value) {
            $param = is_int($param) ? $param + 1 : ':' . ltrim($param, ':');
            $sth->bindParam($param, $value);
        }

        return $sth;
    }

    // 将数组转换成插入格式的sql语句
    private function formatInsert($data)
    {
        $fields = array();
        $names = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s`", $key);
            $names[] = sprintf(":%s", $key);
        }

        $field = implode(',', $fields);
        $name = implode(',', $names);

        return sprintf("(%s) values (%s)", $field, $name);
    }

    // 将数组转换成更新格式的sql语句
    private function formatUpdate($data)
    {
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s` = :%s", $key, $key);
        }

        return implode(',', $fields);
    }

    /**********************************************存储过程可以使用的几个方法***************************************************************/
    /**
     * 有返回参数 @ret 处理方法
     * @param $sql
     * @return bool|mixed
     */
    public function exec_call_sp($sql)
    {

        try {
            //使用PDO中的方法执行语句
            /*  $this->connection->query("call sp_boot_test(@ret)");*/
            Db::pdo($this->db_name)->query($sql);

            $stmt = Db::pdo($this->db_name)->query('select @ret');
            $rows = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $rows;

        }catch(\Exception $e) {

            (new Log)->write("The call to the stored procedure {$sql} failed"."\r\n");//写日志

            return false;
        }
    }

    /**
     * no parameter  无参数的存储过程调用
     * 处理返回多个结果集的处理 多个select
     * @param $sql
     * @return array
     */
    public function exec_sp_multiple_data($sql)
    {
        $stmt = Db::pdo($this->db_name)->query($sql);

        $res = [];//结果集数组
        $i = 0;
        try{
            do {
                $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
                if($rows){
                    $res[$i] = $rows;

                    $i +=1;
                }
            } while ($stmt->nextRowset());
        }catch (\Exception $e){
            //特殊错误不处理
        }

        return $res;
    }
    /**
     *  \PDO::FETCH_NUM 键是数字
     * no parameter  无参数的存储过程调用
     * @param $sql
     * @return bool|mixed 返回对象 如：$obj->name,$obj->id
     */
    public function exec_sp_obj($sql)
    {

        try {
            //使用PDO中的方法执行语句
            $stmt = Db::pdo($this->db_name)->prepare($sql);
            // $stmt->bindParam(1, $return_value, \PDO::PARAM_STR, 4000); //执行存储过程
            $stmt->execute();
            $row = $stmt->fetchObject();

            return $row;

        }catch(\Exception $e) {

            (new Log)->write("The call to the stored procedure {$sql} failed"."\r\n");//写日志

            return false;
        }
    }

    /**
     * no parameter  无参数的存储过程调用
     * @param $sql
     * @return [] 返回数组
     */
    public function exec_sp_assoc($sql)
    {

        try {
            //使用PDO中的方法执行语句
            $stmt = Db::pdo($this->db_name)->prepare($sql);
            // $stmt->bindParam(1, $return_value, \PDO::PARAM_STR, 4000); //执行存储过程
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $row;

        }catch(\Exception $e) {

            (new Log)->write("The call to the stored procedure {$sql} failed"."\r\n");//写日志

            return false;
        }
    }
}