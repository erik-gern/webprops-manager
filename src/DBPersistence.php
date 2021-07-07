<?php

namespace PropMgr;

use PDO;
use PropMgr\SqlHelper;
use Monolog\Logger;

class DBPersistence
{
	protected PDO $db;
	protected array $loggers = [];
	protected array $ids;
	protected array $dataColumns;
	protected string $deleteStrategy = 'hard';
	protected ?string $deleteColumn = null;
	protected ?string $deleteValue = null;
	protected int $maxRowLimit = 1000;
	
	public function __construct(
		PDO $db, 
		string $table, 
		$ids=['id'],
		array $dataCols=[]
	) {
		$this->db = $db;
		$this->table = $table;
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		$this->ids = $ids;
		$this->dataColumns = $dataCols;
	}
	
	public function useSoftDelete(string $deleteCol='_deleted', $deleteVal=1) {
		$this->deleteStrategy = 'soft';
		$this->deleteColumn = $deleteCol;
		$this->deleteValue = $deleteVal;
	}
	
	protected function log($str, $severity=Logger::DEBUG) {
		foreach ($this->loggers as $logger) {
			$logger->log($severity, $str);
		}
	}
	
	public function addLogger(Logger $logger) {
		$this->loggers[] = $logger;
	}
	
	public function count(): int {
		$sql = "SELECT COUNT(*) FROM {$this->table}";
		$this->log('DBPersistence::count');
		$this->log($sql);
		try {
			$stmt = SqlHelper::prepareStatement($this->db, $sql);
		}
		catch (any $e) {
			$this->log($e, Logger::ERROR);
		}
		return intval($stmt->fetchColumn());
	}

	public function get($ids): array {
		if (!is_array($ids)) {
			$idStr = $ids;
			$ids = [];
			$ids[$this->ids[0]] = $idStr;
		}
		$allCols = implode(',', array_merge($this->ids, $this->dataColumns));
		$sql = "SELECT $allCols FROM {$this->table} ";
		$whereParts = SqlHelper::whereClauseWithParams($ids);
		$sql .= $whereParts[0];
		$params = array_merge([], $whereParts[1]);
		$sql .= ' LIMIT 1';
		$this->log('DBPersistence::get');
		$this->log($sql);
		$stmt = SqlHelper::prepareStatement($this->db, $sql, $params);
		$record = $stmt->fetch();
		if ($record === false) {
			throw new \Exception('Could not fetch record: '.implode(':', $stmt->errorInfo()));
		}
		return $record;
	}
	
	public function list($where=[], $sort=[], ?int $start=0, ?int $limit=-1): array {
		if ($limit = -1) {
			$limit = $this->maxRowLimit;
		}
		elseif ($limit > $this->maxRowLimit) {
			$limit = $this->maxRowLimit;
		}
				
		// get list of all columns for selection
		$allCols = array_unique(array_merge($this->ids, $this->dataColumns), SORT_STRING);	
		
		// allowlist sort based on available columns	
		if (count($sort) > 0) {
			$sort = array_filter($sort, function($sortPair) use ($allCols) {
				return in_array($sortPair[0], $allCols);
			});
		}
		
		// assemble sql
		$selectedCols = implode(',', $allCols);
		$sql = "SELECT $selectedCols FROM {$this->table}";
		$params = [];
		if (count(array_keys($where)) > 0) {
			$whereParts = SqlHelper::whereClauseWithParams($where);
			$whereSql = $whereParts[0];
			$whereParams = $whereParts[1];
			$sql .= ' '.$whereSql;
			$params = array_merge($params, $whereParams);
		}
		if (count($sort) > 0) {
			$sql .= ' '.SqlHelper::orderByClauseFromSort($sort);
		}

		$sql .= " LIMIT $limit";
		if ($start > 0) {
			$sql .= " OFFSET $start";
		}
		
		$this->log('DBPersistence::list');
		$this->log($sql);
		$stmt = SqlHelper::prepareStatement($this->db, $sql, $params);
		
		return $stmt->fetchAll();		
	}
	
	public function create($data, $ids=[]): int {
		if (!is_array($ids)) {
			$idStr = $ids;
			$ids = [];
			$ids[$this->ids[0]] = $idStr;
		}
		
		$cols = array_intersect($this->dataColumns, array_keys($data));
		if (count($ids) > 0) {
			$cols = array_unique(array_merge($cols, array_keys($ids)), SORT_STRING);
			$data = array_merge($data, $ids);
		}
		
		$paramKeys = array_map(function($c){ return ':'.$c; }, $cols);
		
		$sql = "INSERT INTO {$this->table} (";
		$sql .= implode(',', $cols);
		$sql .= ') VALUES (';
		$sql .= implode(',', $paramKeys);
		$sql .= ")";
		
		$params = [];
		foreach ($cols as $col) {
			$params[':'.$col] = $data[$col];
		}
		
		$this->log('DBPersistence::create');
		$this->log($sql);
		$stmt = SqlHelper::prepareStatement($this->db, $sql, $params);
		
		$insertId = $this->db->lastInsertId();
		return intval($insertId);
	}
	
	public function update($ids, $data) {
		if (!is_array($ids)) {
			$idStr = $ids;
			$ids = [];
			$ids[$this->ids[0]] = $idStr;
		}
		$cols = array_intersect($this->dataColumns, array_keys($data));
		$paramKeys = array_map(function($c){ return ':'.$c; }, $cols);
		
		$sql = "UPDATE {$this->table} SET ";
		$sql .= implode(',', array_map(function($c){ return $c.'=:'.$c; }, $cols));
		$whereParts = SqlHelper::whereClauseWithParams($ids);
		$sql .= ' '.$whereParts[0];
		
		$params = array_merge([], $whereParts[1]);
		foreach ($cols as $col) {
			$params[':'.$col] = $data[$col];
		}
		
		$this->log('DBPersistence::update');
		$this->log($sql);
		$stmt = SqlHelper::prepareStatement($this->db, $sql, $params);
	}
	
	public function delete($ids): int {
		if (!is_array($ids)) {
			$idStr = $ids;
			$ids = [];
			$ids[$this->ids[0]] = $idStr;		
		}
		$params = array_merge([], $ids);
		if ($this->deleteStrategy == 'soft') {
			$sql = "UPDATE {$this->table} SET {$this->deleteColumn}=:delete_value ";	
			$params[':delete_value'] = $this->deleteValue;	
		}
		else {
			$sql = "DELETE FROM {$this->table} ";			
		}
		$whereParts = SqlHelper::whereClauseWithParams($ids);
		$sql .= $whereParts[0];
		$params = array_merge($params, $whereParts[1]);
		$this->log('DBPersistence::delete');
		$this->log($sql);
		$stmt = SqlHelper::prepareStatement($this->db, $sql, $params);
		return $stmt->rowCount();
	}	
	
}