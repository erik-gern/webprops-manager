<?php

namespace PropMgr;

use PDO;
use PDOStatement;

class SqlHelper
{
	public static array $comparators = [
		'=',
		'==',
		'>',
		'>=',
		'<',
		'<=',
		'LIKE',
	];
	
	public static function whereClauseWithParams(array $where): array {
		if (count(array_keys($where)) == 0) {
			return ['', []];
		}
		$where = array_map(function($val){
			if (!is_array($val)) {
				$val = ['=', $val];
			}
			if (!in_array($val[0], self::$comparators)) {
				$val[0] = '=';
			}
			return $val;
		}, $where);
		
		$sql = 'WHERE ';
		$cols = array_keys($where);
		$sql .= implode(' AND ', array_map(function($col) use ($where) {
			return $col.' '.$where[$col][0].' :'.$col;
		}, $cols));

		$params = [];
		foreach ($cols as $col) {
			$params[':'.$col] = $where[$col][1];
		}

		return [$sql, $params];
	}
	
	public static function orderByClauseFromSort(array $sort): string {
		if (count($sort) == 0) {
			return '';
		}
		$str = 'ORDER BY ';
		$str .= implode(', ', array_map(function($pair){
			return $pair[0] . ' COLLATE NOCASE ' . $pair[1];
		}, $sort));
		return $str;
	}
	
	public static function guessParamType($val): int {
		if (is_numeric($val)) {
			if (is_float($val)) {
				return PDO::PARAM_FLOAT;
			}
			else {			
				return PDO::PARAM_INT;
			}
		}
		elseif (is_null($val)) {
			return PDO::PARAM_NULL;
		}
		else {
			return PDO::PARAM_STR;
		}
	}
	
	public static function prepareStatement(PDO $db, string $sql, array $params=[]): PDOStatement {
		$paramKeys = array_keys($params);
		if (count($paramKeys) == 0) {
			$stmt = $db->query($sql);
			if ($stmt === false) {
				throw new \Exception('Could not create statement: check SQL syntax. '.implode(':', $db->errorInfo()));
			}
		}
		else {
			$stmt = $db->prepare($sql);
			if ($stmt === false) {
				throw new \Exception('Could not create statement: check SQL syntax. '.implode(':', $db->errorInfo()));
			}
			// check parameters for non-included bindings
			preg_match_all('/\:[a-zA-Z0-9_]+/', $sql, $paramSqlMatches);
			$paramSqlKeys = array_map(function($m){ return $m[0]; }, $paramSqlMatches);
			$paramKeys = array_keys($params);
			if (count($paramSqlKeys) > count($paramKeys)) {
				$missingKeys = array_diff($paramSqlKeys, $paramKeys);
				throw new Exception('Keys found in SQL that are missing in parameters: '.implode(',', $missingKeys));
			}
			
			// bind params
			foreach ($params as $key => $val) {
				$stmt->bindValue($key, $val, self::guessParamType($val));
			}
			$stmt->execute();
		}
		return $stmt;
	}	
}
