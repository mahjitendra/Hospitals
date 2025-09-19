<?php

/**
 * Base Model Class
 * 
 * Provides common database operations for all models
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find a record by ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Find a record by specific field
     */
    public function findBy($field, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value LIMIT 1";
        return $this->db->fetch($sql, ['value' => $value]);
    }
    
    /**
     * Get all records
     */
    public function all($orderBy = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get records with conditions
     */
    public function where($conditions, $params = [], $orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create a new record
     */
    public function create($data)
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date($this->dateFormat);
            $data['updated_at'] = date($this->dateFormat);
        }
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update a record
     */
    public function update($id, $data)
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date($this->dateFormat);
        }
        
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Delete a record
     */
    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Count records
     */
    public function count($where = '1=1', $params = [])
    {
        return $this->db->count($this->table, $where, $params);
    }
    
    /**
     * Check if record exists
     */
    public function exists($where, $params = [])
    {
        return $this->db->exists($this->table, $where, $params);
    }
    
    /**
     * Paginate records
     */
    public function paginate($page = 1, $perPage = 25, $where = '1=1', $params = [], $orderBy = 'id DESC')
    {
        return $this->db->paginate($this->table, $page, $perPage, $where, $params, $orderBy);
    }
    
    /**
     * Get first record
     */
    public function first($where = '1=1', $params = [], $orderBy = 'id ASC')
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Get last record
     */
    public function last($where = '1=1', $params = [], $orderBy = 'id DESC')
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Filter data based on fillable fields
     */
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            // If no fillable fields specified, remove guarded fields
            foreach ($this->guarded as $field) {
                unset($data[$field]);
            }
            return $data;
        }
        
        // Only keep fillable fields
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Execute raw SQL query
     */
    public function raw($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit database transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }
    
    /**
     * Rollback database transaction
     */
    public function rollback()
    {
        return $this->db->rollback();
    }
    
    /**
     * Get table name
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * Get primary key
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    
    /**
     * Soft delete (if soft delete column exists)
     */
    public function softDelete($id)
    {
        if ($this->hasSoftDelete()) {
            return $this->update($id, ['deleted_at' => date($this->dateFormat)]);
        }
        return $this->delete($id);
    }
    
    /**
     * Check if model has soft delete
     */
    protected function hasSoftDelete()
    {
        // Check if deleted_at column exists in fillable or not in guarded
        return in_array('deleted_at', $this->fillable) || !in_array('deleted_at', $this->guarded);
    }
    
    /**
     * Get records excluding soft deleted
     */
    public function withoutTrashed($where = '1=1', $params = [])
    {
        if ($this->hasSoftDelete()) {
            $where .= ' AND deleted_at IS NULL';
        }
        return $this->where($where, $params);
    }
    
    /**
     * Get only soft deleted records
     */
    public function onlyTrashed($where = '1=1', $params = [])
    {
        if ($this->hasSoftDelete()) {
            $where .= ' AND deleted_at IS NOT NULL';
        }
        return $this->where($where, $params);
    }
}