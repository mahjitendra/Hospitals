<?php

/**
 * Database Connection Class
 * 
 * Handles database connections and operations using PDO
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;
    
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/database.php';
        $this->connect();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect()
    {
        try {
            $defaultConnection = $this->config['default'];
            $config = $this->config['connections'][$defaultConnection];
            
            $dsn = $this->buildDsn($config);
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
            
        } catch (PDOException $e) {
            Logger::error('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }
    
    private function buildDsn($config)
    {
        switch ($config['driver']) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            case 'pgsql':
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            case 'sqlite':
                return "sqlite:{$config['database']}";
            default:
                throw new Exception('Unsupported database driver');
        }
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
    
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error('Database query failed: ' . $e->getMessage());
            throw new Exception('Database query failed');
        }
    }
    
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insert($table, $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    public function commit()
    {
        return $this->connection->commit();
    }
    
    public function rollback()
    {
        return $this->connection->rollback();
    }
    
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    public function count($table, $where = '1=1', $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return (int) $result['count'];
    }
    
    public function exists($table, $where, $params = [])
    {
        return $this->count($table, $where, $params) > 0;
    }
    
    public function paginate($table, $page = 1, $perPage = 25, $where = '1=1', $params = [], $orderBy = 'id DESC')
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalCount = $this->count($table, $where, $params);
        
        // Get data
        $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $totalCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($totalCount / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalCount)
        ];
    }
    
    public function __destruct()
    {
        $this->connection = null;
    }
}