<?php

//mongodb封装类
class Mdb extends Mongo {

	var $mongo; //Mongodb连接
	var $db; //数据库
	var $table; //表（集合）
	var $cursor; //游标
	var $error; //错误信息
	var $out = 'php_data'; //mapreduce存储数据表

	/**
	 +----------------------------------------------------------
	 * 构造函数
	 +----------------------------------------------------------
	 * @param string $server 支持传入多个mongo_server(1.一个出问题时连接其它的server 2.自动将查询均匀分发到不同server)
	 * @param array $server 数组或字符串-array("mongodb://username:password@127.0.0.1:12345",...)
	 * @param Boolean $connect 初始化mongo对象时是否连接
	 * @param Boolean $auto_balance 是否自动做负载均衡
	 +----------------------------------------------------------
	 * @return void
	 +----------------------------------------------------------
	 */

	function __construct($server = null, $connect = true, $auto_balance = true) {
		$connect_str = '';
		if ($server && is_array($server)) {
			$server_num = count($server);
			if ($server_num > 1 && $auto_balance) {
				$prior_server_num = rand(1, $server_num);
				$rand_keys = array_rand($server, $server_num);
				$server_str = $server[$prior_server_num - 1];
				foreach ($rand_keys as $key) {
					if ($key != $prior_server_num - 1) {
						$connect_str.=',' . $server[$key];
					}
				}
			} else {
				$connect_str = implode(',', $server);
			}
		} elseif (empty($server)) {
			$connect_str = 'mongodb://' . MONGO_USER . ':' . MONGO_PWD . '@' . MONGO_HOST . ':' . MONGO_PORT . '/' . MONGO_GAME;
		} else {
			$connect_str = $server;
		}
		try {
			$this->mongo = new Mongo($connect_str, array('connect' => $connect));
			$this->db = $this->mongo->MONGO_GAME;
		} catch (MongoConnectionException $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	/**
	 +----------------------------------------------------------
	 * 选择数据库
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return Object
	 +----------------------------------------------------------
	 */
	public function selectDb($db_name) {
		return $this->db = $this->mongo->$db_name;
	}

	/**
	 +----------------------------------------------------------
	 * 求记录数，支持分组，相当于mysql:select count(*) from table group by name
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 +----------------------------------------------------------
	 * @return int
	 +----------------------------------------------------------
	 */
	public function count($table_name, $condition = array(), $group = array()) {
		if ($group) {
			$opers = array('count' => true);
			return $this->operation($table_name, $opers, $condition, $group);
		} else {
			return $this->db->$table_name->count($condition);
		}
	}

	/**
	 +----------------------------------------------------------
	 * 查询表的记录集合
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 查询条件
	 * @param array $fields 获取字段
	 * @param array $result_condition 查询结果限制条件start、limit、sort等
	 * $result_condition['sort']=array('字段名'=>1)升序
	 * $result_condition['sort']=array('字段名'=>0)降序
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function find($table_name, $condition, $fields = array(), $result_condition = array()) {
		$this->table = $this->db->$table_name;
		$this->cursor = $this->table->find($condition, $fields);
		if (isset($result_condition['sort']) && is_array($result_condition['sort']) && $result_condition['sort']) {
			$this->cursor->sort($result_condition['sort']);
		}
		if (isset($result_condition['start'])) {
			$this->cursor->skip(intval($result_condition['start']));
		}
		if (isset($result_condition['limit']) && $result_condition['limit'] > 0) {
			$this->cursor->limit(intval($result_condition['limit']));
		}
		$result = array();
		while ($this->cursor->hasNext()) {
			$result[] = $this->cursor->getNext();
		}
		return $result;
	}

	/**
	 +----------------------------------------------------------
	 * 查询多条不重复记录值
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param string $field 字段
	 * @param array $condition 查询条件
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function distinct($table_name, $field, $condition = array()) {
		$command = array(
            'distinct' => $table_name,
            'key' => $field,
            'query' => $condition
		);
		$result = $this->db->command($command);
		return $result['values'];
	}

	/**
	 +----------------------------------------------------------
	 * 查询表的记录集合
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 查询条件
	 * @param array $fields 获取字段
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	function findOne($table_name, $condition, $fields = array()) {
		return $this->db->$table_name->findOne($condition, $fields);
	}

	/**
	 +----------------------------------------------------------
	 * 插入记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $record 记录集
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	public function insert($table_name, $record) {
		try {
			$result=$this->db->$table_name->insert($record,array('w'=>1));
		}catch(Exception $e){
			$result=false;
		}
		return $result ? true : false;
	}

	/**
	 +----------------------------------------------------------
	 * 批量插入记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $record 记录集
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	public function batchInsert($table_name, $record,$options=array()) {
		try {
			$result=$this->db->$table_name->batchInsert($record, $options);
		}catch (Exception $e){
			$result=false;
		}
		return $result ? true : false;
	}

	/**
	 +----------------------------------------------------------
	 * 更新记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 条件
	 * @param array $newdata 数据集
	 * @param array $options 选项 upsert、multiple
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	public function update($table_name, $condition, $newdata, $options = array()) {
		$options['safe'] = 1;
		if (!isset($options['multiple'])) {
			$options['multiple'] = 0;
		}
		return $this->db->$table_name->update($condition, $newdata, $options);
	}

	/**
	 +----------------------------------------------------------
	 * Xóa记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 条件
	 * @param array $options 选项 justOne
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	function remove($table_name, $condition, $options = array()) {
		$options['safe'] = 1;
		return $this->db->$table_name->remove($condition, $options);
	}

	/**
	 +----------------------------------------------------------
	 * 更新并返回数据
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 条件
	 * @param array $newdata 更新（插入）数据集
	 * @param array $fields 字段
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	public function findAndModify($table_name, $condition, $newdata, $fields) {
		return $result = $this->db->command(
		array(
				'findAndModify' => $table_name,
				'query' => $condition,
				'update' => $newData,
				'fields' => $fields,
				'new' => true
		));
	}

	/**
	 +----------------------------------------------------------
	 * 求和，支持分组
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $fields 求和字段,如array('a','b')
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	function sum($table_name, $fields, $condition = array(), $group = array()) {
		$opers = array('sum' => implode(',', $fields));
		return $this->operation($table_name, $opers, $condition, $group);
	}

	/**
	 +----------------------------------------------------------
	 * 求平均数,支持分组
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $fields 求平均数字段,如array('a','b')
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function avg($table_name, $fields, $condition = array(), $group = array()) {
		$opers = array('avg' => implode(',', $fields));
		return $this->operation($table_name, $opers, $condition, $group);
	}

	/**
	 +----------------------------------------------------------
	 * 求最大值,支持分组
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $fields 求最大值字段,如array('a','b')
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function max($table_name, $fields, $condition = array(), $group = array()) {
		$opers = array('max' => implode(',', $fields));
		return $this->operation($table_name, $opers, $condition, $group);
	}

	/**
	 +----------------------------------------------------------
	 * 求最小值,支持分组
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $fields 求最小值字段,如array('a','b')
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function min($table_name, $fields, $condition = array(), $group = array()) {
		$opers = array('min' => implode(',', $fields));
		return $this->operation($table_name, $opers, $condition, $group);
	}

	/**
	 +----------------------------------------------------------
	 * 求记录数、和、最大值、最小值等,支持分组。相当于mysql:select count(*),sum(a),max(b),min(c) from table group by d
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $opers 运算数组 如 array('count'=>true,'sum'=>'a,b','avg'=>'c','max'=>'d','min'=>'e')
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 * @param string $type 运算
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function operation($table_name, $opers, $condition = array(), $group = array()) {
		if (!is_array($opers) || !$opers)
		return flase;
		$allowType = array('count', 'sum', 'avg', 'max', 'min'); //合法运算类型
		$fields = $operType = $showFields = array();
		foreach ($opers as $type => $field) {
			if ($type == 'count') {
				$showFields[] = "value.$type";
			} else {
				$items = explode(',', $field);
				$fields = array_merge($fields, $items);
				//返回结果显示
				if (in_array($type, $allowType)) {
					foreach ($items as $item) {
						$initialize_item=strstr($item,'.') ? ltrim(strstr($item,'.'),'.') : $item;//初始化字段
						$showFields[] = "value.{$initialize_item}_{$type}";
					}
				}
			}
		}
		$fields = array_unique($fields);
		$emitItem = array('count:1');
		$resultItem = array('count:0');
		$operItem = array('result.count+=values[index].count');
		$avgItem = array();
		foreach ($fields as $field) {
			$initialize_field=strstr($field,'.') ? ltrim(strstr($field,'.'),'.') : $field;//初始化字段
			$countField = $initialize_field . '_count';
			$sumField = $initialize_field . '_sum';
			$avgField = $initialize_field . '_avg';
			$maxField = $initialize_field . '_max';
			$minField = $initialize_field . '_min';

			$emitItem[] = "$sumField:this.$field,$avgField:this.$field,$maxField:this.$field,$minField:this.$field";
			$resultItem[] = "$sumField:0,$avgField:0,$maxField:values[0].$maxField,$minField:values[0].$minField";
			$operItem[] = "result.$sumField+=values[index].$sumField";
			$operItem[] = "result.$maxField=values[index].$maxField>result.$maxField ? values[index].$maxField : result.$maxField";
			$operItem[] = "result.$minField=values[index].$minField<result.$minField ? values[index].$minField : result.$minField";
			$avgItem[] = "result.{$avgField}=result.count>0 ? result.{$sumField}/result.count : 0";
		}
		$emitItem = implode(',', $emitItem);
		$resultItem = implode(',', $resultItem);
		$operItem = implode(";\n", $operItem);
		$avgItem = implode(";\n", $avgItem);
		$group = is_array($group) && !empty($group) ? "this.{$group[0]}" : '0';
		$map = "function(){emit($group,{ $emitItem });}";
		$reduce = "function(key,values){ var result={ $resultItem };for(var index in values){ $operItem; } $avgItem ; return result;}";
		$command = array(
			'mapreduce' => $table_name,
			'map' => $map,
			'reduce' => $reduce,
			'out' => $this->out);
		if (is_array($condition) && $condition){
			$command['query'] = $condition;
		}
		$result = $this->db->command($command);
		return iterator_to_array($this->db->{$result['result']}->find(array(), $showFields));
	}

	/**
	 +----------------------------------------------------------
	 * 求分库记录数，支持分组，只适合分库使用（S1_jtxm2_0,S1_jtxm2_1,S1_jtxm2_2,S1_jtxm2_3）
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $condition 查询条件
	 * @param array $group 分组字段，如array('c') 暂不支持组合分分组
	 +----------------------------------------------------------
	 * @return int
	 +----------------------------------------------------------
	 */
	public function allCount($table_name, $condition = array(), $group = array()){
		$count=0;
		for($i=0;$i<4;$i++){
			$this->selectDb(MONGO_PERFIX.$i);
			$count+=$this->count($table_name, $condition, $group);
		}
		return $count;
	}

	/**
	 +----------------------------------------------------------
	 * 创建索引
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $index 索引array("id"=>1)-在id字段建立升序索引
	 * @param array $index_param 其它条件-是否唯一索引等
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	public function ensureIndex($table_name, $index, $index_param = array()) {
		$index_param['safe'] = 1;
		return $this->db->$table_name->ensureIndex($index, $index_param);
	}

	/**
	 +----------------------------------------------------------
	 * 分组统计(适用于小量数据)
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $table_name 表名
	 * @param array $keys group的字段
	 * @param array $initial 初始值
	 * @param array $reduce 字段
	 * @param array $condition 查找条件
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function group($table_name, $keys, $initial, $reduce, $condition = array()) {
		$result = $this->db->$table_name->group($keys, $initial, $reduce, $condition);
		return $result['retval'];
	}

	public function command($command){
		return $this->db->command($command);
	}

	/**
	 +----------------------------------------------------------
	 * 关闭数据库连接
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return bool
	 +----------------------------------------------------------
	 */
	public function close() {
		return $this->mongo->close();
	}

	/**
	 +----------------------------------------------------------
	 * 错误信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 +----------------------------------------------------------
	 * 函数析构
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return void
	 +----------------------------------------------------------
	 */
	public function __destruct() {
		if ($this->mongo){
			$this->mongo->close();
		}
		unset($this->db);
		unset($this->table);
		unset($this->cursor);
	}

}