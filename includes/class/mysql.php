<?php

/**
 * @package phpBB-WAP
 * @简体中文：中文phpBB-WAP团队
 * @license http://opensource.org/licenses/gpl-license.php
 **/

/**
 * 这是一款自由软件, 您可以在 Free Software Foundation 发布的
 * GNU General Public License 的条款下重新发布或修改; 您可以
 * 选择目前 version 2 这个版本（亦可以选择任何更新的版本，由
 * 你喜欢）作为新的牌照.
 **/

/**
 * 	phpBB-WAP mysql 数据库类
 *	作者: Crazy
 */

if (!defined('IN_PHPBB')) exit;

if (!defined('SQL_LAYER')) {
    define('SQL_LAYER', 'mysql');

    class sql_db
    {

        var $db_connect_id;
        var $query_result;
        var $num_queries = 0;

        /**
         * 构造函数：用于初始化数据库连接
         *
         * @param string $sqlserver 数据库服务器地址
         * @param string $sqluser 数据库用户名
         * @param string $sqlpassword 数据库用户密码
         * @param string $database 数据库名称
         * @param bool $persistency 是否使用持久连接，默认为 true
         * @return mixed 返回数据库连接标识或 false，如果连接失败
         */
        function __construct($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
        {

            $this->persistency = $persistency;
            $this->user = $sqluser;
            $this->password = $sqlpassword;
            $this->server = $sqlserver;
            $this->dbname = $database;

            if ($this->persistency) {
                $this->db_connect_id = @mysqli_connect('p:' . $this->server, $this->user, $this->password, $this->dbname, NULL);
            } else {
                $this->db_connect_id = @mysqli_connect($this->server, $this->user, $this->password, $this->dbname, NULL);
            }

            if ($this->db_connect_id) {
                if ($database != "") {
                    $this->dbname = $database;
                    $dbselect = @mysqli_select_db($this->db_connect_id, $this->dbname);
                    if (!$dbselect) {
                        @mysqli_close($this->db_connect_id);
                        $this->db_connect_id = $dbselect;
                    }
                }
                return $this->db_connect_id;
            } else {
                return false;
            }
        }

        /**
         * 关闭数据库连接
         *
         * 该函数首先检查数据库连接标识$db_connect_id是否存在如果存在，它会进一步检查查询结果$query_result是否存在
         * 如果查询结果存在，函数将释放查询结果以释放资源最后，函数关闭数据库连接
         * 如果数据库连接标识不存在，函数返回false
         *
         * @return bool 返回关闭数据库连接的结果如果连接未初始化，则返回false
         */
        function sql_close()
        {
            if ($this->db_connect_id) {
                if ($this->query_result) {
                    @mysqli_free_result($this->query_result);
                }
                $result = @mysqli_close($this->db_connect_id);
                return $result;
            } else {
                return false;
            }
        }

        /**
         * 执行SQL查询并处理查询结果。
         *
         * 该函数用于执行SQL查询。它可以根据是否涉及事务来决定如何处理查询结果。
         * 在事务中，即使在执行查询时发生错误，该函数也会返回true以允许后续查询继续执行。
         * 这是为了确保整个事务过程可以顺利进行。
         *
         * @param string $query 要执行的SQL查询语句，默认为空字符串。
         * @param bool $transaction 标记是否为事务操作，默认为FALSE。
         * @return mixed 如果查询成功，返回查询结果资源；否则根据$transaction参数返回布尔值。
         */
        function sql_query($query = "", $transaction = FALSE)
        {
            // Remove any pre-existing queries
            unset($this->query_result);
            if ($query != "") {
                $this->num_queries++;

                $this->query_result = @mysqli_query($this->db_connect_id, $query);
            }
            if ($this->query_result) {
                return $this->query_result;
            } else {
                return ($transaction == END_TRANSACTION) ? true : false;
            }
        }

        /**
         * 获取SQL查询结果的行数
         * 
         * 如果提供了$query_id，则使用提供的查询ID；如果未提供，则使用类内部的查询结果属性
         * 此函数主要用于在执行SQL查询后，判断返回的结果集中包含的行数
         * 
         * @param resource $query_id 可选的查询ID，代表SQL查询的结果集，默认为0
         * @return int|bool 返回结果集的行数，如果没有结果集或发生错误则返回false
         */
        function sql_numrows($query_id = 0)
        {
            if (!$query_id) {
                $query_id = $this->query_result;
            }
            if ($query_id) {
                $result = @mysqli_num_rows($query_id);
                return $result;
            } else {
                return false;
            }
        }
        /**
         * 获取上一次SQL操作影响的行数
         *
         * 此函数用于获取最近一次SQL操作（如INSERT、UPDATE、DELETE）影响的行数
         * 它通过MySQLi扩展提供的mysqli_affected_rows函数来获取受影响的行数
         * 如果数据库连接不存在，则返回false
         *
         * @return int|bool 返回影响的行数，如果没有连接到数据库则返回false
         */
        function sql_affectedrows()
        {
            if ($this->db_connect_id) {
                $result = @mysqli_affected_rows($this->db_connect_id);
                return $result;
            } else {
                return false;
            }
        }



        /**
         * 获取SQL查询结果的下一行数据
         * 
         * 该函数用于从SQL查询结果中获取下一行数据，并以数组形式返回如果查询结果为空，则返回false
         * 主要用于简化数据获取过程，提高代码可读性和可维护性
         * 
         * @param resource $query_id 查询结果的标识符，默认为0如果未指定，则使用类内部的查询结果标识符
         * @return array|bool 返回查询结果的下一行数据，如果没有更多数据则返回false
         */
        function sql_fetchrow($query_id = 0)
        {
            // 检查是否提供了查询结果标识符，如果没有，则使用类内部的查询结果标识符
            if (!$query_id) {
                $query_id = $this->query_result;
            }

            // 再次检查查询结果标识符是否存在，如果存在，则尝试获取下一行数据
            if ($query_id) {
                $result = @mysqli_fetch_array($query_id);
                return $result;
            } else {
                // 如果查询结果标识符不存在，则返回false，表示没有数据可获取
                return false;
            }
        }
        /**
         * 获取SQL查询的结果集
         * 
         * 该函数用于获取SQL查询的全部结果集并以数组形式返回
         * 如果没有提供查询ID，函数将使用类内部的查询结果
         * 如果查询ID无效或查询没有结果，函数将返回false
         * 
         * @param resource $query_id 查询ID，默认为0如果未提供，则使用类内部的查询结果
         * @return array|bool 返回查询结果集的数组形式，如果没有结果或查询ID无效，则返回false
         */
        function sql_fetchrowset($query_id = 0)
        {
            // 检查是否提供了有效的查询ID，如果没有，则使用类内部的查询结果
            if (!$query_id) {
                $query_id = $this->query_result;
            }

            // 如果查询ID有效，则继续处理查询结果
            if ($query_id) {
                // 循环获取查询结果的每一行，并将其添加到结果数组中
                while ($row = @mysqli_fetch_array($query_id)) {
                    $result[] = $row;
                }

                // 返回查询结果集的数组形式
                return $result;
            } else {
                // 如果查询ID无效，返回false
                return false;
            }
        }

        /**
         * 获取最后一个插入记录的自增ID
         * 
         * 此函数用于获取当前数据库连接下，最后一次插入操作生成的自增ID
         * 如果数据库连接有效，则使用 mysqli_insert_id 函数获取ID
         * 如果数据库连接无效，则返回 false 表示获取失败
         * 
         * @return mixed 返回最后一个插入记录的自增ID，如果失败则返回 false
         */
        function sql_nextid()
        {
            // 检查数据库连接是否有效
            if ($this->db_connect_id) {
                // 使用 mysqli_insert_id 函数获取最后一个插入记录的自增ID
                $result = @mysqli_insert_id($this->db_connect_id);
                // 返回获取到的ID
                return $result;
            } else {
                // 如果数据库连接无效，返回 false 表示获取失败
                return false;
            }
        }
        /**
         * 释放SQL查询结果资源
         *
         * 该函数旨在释放之前查询所使用的资源，以优化内存使用或避免内存泄漏。它首先检查提供的查询ID是否有效，
         * 如果没有提供或为0，则使用类内部存储的查询结果ID。释放结果资源后，函数返回true，表示操作成功；
         * 否则返回false，表示没有结果资源可释放。
         *
         * @param resource $query_id [可选] 查询结果资源ID，默认为0。如果未提供，默认使用类内部的查询结果ID。
         * @return bool 成功释放结果资源返回true，否则返回false。
         */
        function sql_freeresult($query_id = 0)
        {
            // 检查提供的查询ID是否有效，如果为0，则使用类内部的查询结果ID
            if (!$query_id) {
                $query_id = $this->query_result;
            }

            // 如果查询ID有效，则尝试释放结果资源
            if ($query_id) {
                @mysqli_free_result($query_id);

                // 成功释放结果资源，返回true
                return true;
            } else {
                // 如果查询ID无效，返回false
                return false;
            }
        }
        /**
         * 获取数据库操作错误信息
         *
         * 当数据库查询发生错误时调用此函数，它会返回错误信息和错误码
         * 主要用于调试和日志记录，以便开发者定位问题
         *
         * @param int $query_id 可选的查询ID参数，本例中未使用，但预留以备将来扩展
         * @return array 包含错误信息和错误码的数组
         */
        function sql_error($query_id = 0)
        {
            // 获取并存储数据库错误信息
            $result['message'] = @mysqli_error($this->db_connect_id);
            // 获取并存储数据库错误码
            $result['code'] = @mysqli_errno($this->db_connect_id);

            // 返回包含错误信息和错误码的数组
            return $result;
        }
        /**
         * 为在SQL查询中使用的字符串转义特殊字符。
         *
         * 该函数主要用于转义用户输入中的特殊字符，以防止SQL注入攻击。
         * 它使用PHP内置函数mysqli_real_escape_string进行转义。
         *
         * @param string $msg 需要转义的输入字符串。
         * @return string 转义后的字符串。
         */
        function sql_escape($msg)
        {
            return mysqli_real_escape_string($this->db_connect_id, $msg);
        }
    } // class sql_db

} // if ... define
