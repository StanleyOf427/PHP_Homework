<?php
/**
 * Created in Visual Studio Code
 * 2018-05-06 by StanleyOf427
 *
 * 注意：由于使用mysqli_error()，须在PHP5.3及以上版本使用
 * 简单封装的一个MySQL类，用于增删查改(其实完全按照C#思路来了XD)
 * Row和Column类分别表示行和列数据，比如用Column类定义表的各列名称类型是否主键等等，
 * 或者可以创建Row对象数组用来批量插入数据
 */

class DataBase
{
    private $dbname = "college_inf"; //用于储存各个学校信息的数据库
    private $dbhost = 'localhost:3306'; // mysql服务器主机地址
    private $dbuser = 'root'; // mysql用户名
    private $dbpass = '123456'; // mysql用户名密码
    private $conn;

    public $Result;//数组类型,比如：Result["id"]=1;Result["name"]="xtu";

//region 构造函数

    public function _construct()
    {
    }

    public function _construct1($dbname)
    {
        $this->dbname = $dbname;
    }

    public function _construct4($dbname, $dbhost, $dbuser, $dbpass)
    {
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
    }

//endregion

//region 操作数据库
    //建立连接
    public function GetConn()
    {
        $this->conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass);
        if ($this->conn->connect_error) {
            echo ('连接失败：' . $this->conn->connect_error);
            return false;
        }
        return true;
    }

    //关闭连接
    public function CloseConn()
    {
        if (!mysqli_close($this->conn)) {
            return false;
        }

        return true;
    }

    //建库
    public function CreateDB()
    {
        $sql = 'CREATE DATABASE ' . $this->dbname;

        $retval = $this->conn->query($sql);
        if (!$retval) {
            echo ('创建数据库失败: ' . mysqli_error($this->conn));
            return false;
        }
        return true;
    }

    //删库
    public function DeleteDB()
    {
        $sql = 'DROP DATABASE ' . $this->dbname;
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('删除数据库失败: ' . mysqli_error($this->conn));
            return false;
        }
        return true;
    }

    //endregion

//region 操作表

    //建表
    public function CreatTable($tname, $columnlist)
    {

        //组成数据库建表语句
        $sql = "CREATE TABLE " . $tname . " (";
        $i = 0;
        foreach ($columnlist as $column) {
            if ($i > 0) {
                $sql = $sql . ", ";
            }

            $sql = $sql .
            $column->ColumnName . " " . $column->ColumnType . " " . $column->AutoIncrease . " " .
            $column->PrimaryKey;
            $i++;
        }
        $sql = $sql . ") ";

        //建表
        mysqli_select_db($this->conn, $this->dbname);
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('数据表创建失败: ' . mysqli_error($this->conn));
            return false;
        }
        return true;
    }

//删表
    public function DeleteTable($tname)
    {
        $sql = "DROP TABLE " . $tname;
        mysqli_select_db($this->conn, $this->dbname);
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('数据表删除失败: ' . mysqli_error($this->conn));
            return false;
        }
        mysqli_free_result($retval);
        return true;
    }

//endregion

//region 操作数据
    //插入数据
    public function AddData($tname, $rowlist)
    {
        $name_s = "";
        $value_s = "";
        $i = 0;
        foreach ($rowlist as $row) {
            if ($i > 0) {
                $name_s = $name_s . ", ";
                $value_s = $value_s . ", ";
            }

            $name_s = $name_s . $row->RowName;
            $value_s = $value_s . "'" . $row->Value . "'";
            $value_s = $value_s;
            $i++;
        }
        $sql = "INSERT INTO " . $tname . " (" . $name_s .
            ") VALUES " .
            "($value_s)";

        mysqli_select_db($this->conn, $this->dbname);
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('无法插入数据: ' . mysqli_error($this->conn));
            return false;
        }
        // mysqli_free_result($retval);
        return true;
    }

    //查找数据
    /*参数
    $tname:表名
    $row:查找信息
     */
    public function Search($tname, $row)
    {
        mysqli_query($this->conn, "set names utf8");

        $sql = 'SELECT *
        FROM ' . $tname . '
        WHERE ' . $row->RowName . '="' . $row->Value . '"';

        mysqli_select_db($this->conn, 'RUNOOB');
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('无法读取数据: ' . mysqli_error($this->conn));
            return false;
        }
        $result = array();
        $i = 0;
        while ($row = mysqli_fetch_array($retval, MYSQL_ASSOC)) {
            $result[$i] = $row;
        }
        $this->Result = $result;
        //   mysqli_free_result($retval);
        return true;

    }

    //更新数据
    /*参数
    $tname:表名
    $rowlist_change:新行信息，Row类型数组
    $rowlist_key:要改变行的主键,可为多个，数组表示
     */
    public function Update($tname, $rowlist_change, $rowlist_key)
    {
        $set_string="";
        $primarykey_string="";
        $i = 0;
        foreach ($rowlist_change as $value) {
            if ($i > 0) {
                $set_string = $set_string . ", ";
            }
            $set_string = $set_string . $value->RowName . "=" . $value->Value;
            $i++;
        }

        $i = 0;
        foreach ($rowlist_key as $value) {
            if ($i > 0) {
                $primarykey_string = $primarykey_string . "AND ";
            }
            $primarykey_string = $primarykey_string . $value->RowName . "='" . $value->Value . "'";
            $i++;
        }

        $sql = "UPDATE " . $tname . " SET " . $set_string . " WHERE " . $primarykey_string;
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('更新数据失败: ' . mysqli_error($this->conn));
            return false;
        }
        return true;

    }
 

//删除数据
    public function Delete($tname, $rowlist_key)
    {
        $primarykey_string;
        $i = 0;
        foreach ($rowlist_key as $value) {
            if ($i > 0) {
                $set_string = $set_string . "AND ";
            }
            $set_string = $set_string . $value->RowName . "='" . $value->Value . "'";
            $i++;
        }

        $sql = "DELETE FOME" . tname . " WHERE " . $primarykey_string;
        $retval = mysqli_query($this->conn, $sql);
        if (!$retval) {
            echo ('删除数据失败: ' . mysqli_error($this->conn));
            return false;
        }

        return true;

    }

//endregion

}

//region 数据库信息的类
class Row
{
    public $RowName;
    public $Value;

    public function _construct($name, $value)
    {
        $this->RowName = $name;
        $this->Value = $value;
    }
}

class Column
{
    public $ColumnName;
    public $ColumnType;
    public $Value;
    public $PrimaryKey;
    public $AutoIncrease;

    public function _construct($name, $type)
    {
        $this->ColumnName = $name;
        $this->ColumnType = strtoupper($type);
        $this->Value = "";
        $this->PrimaryKey = "";
        $this->AutoIncrease = "";
    }
    public function _construct2($name, $value)
    {
        $this->ColumnName = $name;
        $this->Value = $value;
        $this->ColumnType = "";
        $this->PrimaryKey = "";
        $this->AutoIncrease = "";
    }

    public function _construct4($name, $type, $is_primarykey, $is_autoincrease)
    {
        $this->ColumnName = $name;
        $this->ColumnType = strtoupper($type);
        $this->Value = "";
        $this->PrimaryKey = "";
        $this->AutoIncrease = "";
        if ($is_primarykey == true) {
            $this->PrimaryKey = "PRIMARY KEY";
        }

        if ($is_autoincrease == true) {
            $this->AutoIncrease = "AUTO_INCREMENT";
        }

    }
}
//endregion