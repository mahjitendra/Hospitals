<?php

/**
 * Logging System Class
 * 
 * Handles application logging with multiple levels and outputs
 */
class Logger
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    
    private static $instance = null;
    private $logPath;
    private $maxFileSize;
    private $maxFiles;
    
    private function __construct()
    {
        $this->logPath = __DIR__ . '/../../storage/logs/';
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->maxFiles = 10;
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log emergency message
     */
    public static function emergency($message, $context = [])
    {
        self::getInstance()->log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * Log alert message
     */
    public static function alert($message, $context = [])
    {
        self::getInstance()->log(self::ALERT, $message, $context);
    }
    
    /**
     * Log critical message
     */
    public static function critical($message, $context = [])
    {
        self::getInstance()->log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = [])
    {
        self::getInstance()->log(self::ERROR, $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = [])
    {
        self::getInstance()->log(self::WARNING, $message, $context);
    }
    
    /**
     * Log notice message
     */
    public static function notice($message, $context = [])
    {
        self::getInstance()->log(self::NOTICE, $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = [])
    {
        self::getInstance()->log(self::INFO, $message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = [])
    {
        self::getInstance()->log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log activity
     */
    public static function activity($action, $description = null, $data = null, $user = null)
    {
        $context = [
            'action' => $action,
            'description' => $description,
            'data' => $data,
            'user_id' => $user['id'] ?? null,
            'user_name' => $user['name'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null
        ];
        
        self::getInstance()->writeToFile('activity.log', self::INFO, "Activity: {$action}", $context);
    }
    
    /**
     * Log security event
     */
    public static function security($event, $message, $context = [])
    {
        $context = array_merge($context, [
            'event' => $event,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        self::getInstance()->writeToFile('security.log', self::WARNING, "Security: {$event} - {$message}", $context);
    }
    
    /**
     * Log database query
     */
    public static function query($sql, $params = [], $executionTime = null)
    {
        if (config('app.debug', false)) {
            $context = [
                'sql' => $sql,
                'params' => $params,
                'execution_time' => $executionTime,
                'memory_usage' => memory_get_usage(true)
            ];
            
            self::getInstance()->writeToFile('database.log', self::DEBUG, 'Database Query', $context);
        }
    }
    
    /**
     * Main logging method
     */
    public function log($level, $message, $context = [])
    {
        $this->writeToFile('application.log', $level, $message, $context);
        
        // Also write to specific log files for certain levels
        if (in_array($level, [self::ERROR, self::CRITICAL, self::EMERGENCY, self::ALERT])) {
            $this->writeToFile('error.log', $level, $message, $context);
        }
    }
    
    /**
     * Write log entry to file
     */
    private function writeToFile($filename, $level, $message, $context = [])
    {
        $filepath = $this->logPath . $filename;
        
        // Rotate log file if it's too large
        $this->rotateLogFile($filepath);
        
        // Format log entry
        $logEntry = $this->formatLogEntry($level, $message, $context);
        
        // Write to file
        file_put_contents($filepath, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Format log entry
     */
    private function formatLogEntry($level, $message, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        
        // Basic log format
        $entry = "[{$timestamp}] {$levelUpper}: {$message}";
        
        // Add context if provided
        if (!empty($context)) {
            $entry .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        return $entry;
    }
    
    /**
     * Rotate log file if it exceeds max size
     */
    private function rotateLogFile($filepath)
    {
        if (!file_exists($filepath)) {
            return;
        }
        
        if (filesize($filepath) < $this->maxFileSize) {
            return;
        }
        
        // Rotate existing files
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $oldFile = $filepath . '.' . $i;
            $newFile = $filepath . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == $this->maxFiles - 1) {
                    unlink($oldFile); // Delete oldest file
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Move current file to .1
        rename($filepath, $filepath . '.1');
    }
    
    /**
     * Get log entries
     */
    public function getLogEntries($filename = 'application.log', $lines = 100)
    {
        $filepath = $this->logPath . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $entries = [];
        $file = new SplFileObject($filepath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $entries[] = $this->parseLogEntry($line);
            }
            $file->next();
        }
        
        return array_reverse($entries);
    }
    
    /**
     * Parse log entry
     */
    private function parseLogEntry($line)
    {
        $pattern = '/^\[([^\]]+)\] ([A-Z]+): (.+)$/';
        
        if (preg_match($pattern, $line, $matches)) {
            $contextStart = strpos($matches[3], ' {');
            
            if ($contextStart !== false) {
                $message = substr($matches[3], 0, $contextStart);
                $contextJson = substr($matches[3], $contextStart + 1);
                $context = json_decode($contextJson, true) ?: [];
            } else {
                $message = $matches[3];
                $context = [];
            }
            
            return [
                'timestamp' => $matches[1],
                'level' => strtolower($matches[2]),
                'message' => $message,
                'context' => $context
            ];
        }
        
        return [
            'timestamp' => '',
            'level' => 'unknown',
            'message' => $line,
            'context' => []
        ];
    }
    
    /**
     * Clear log file
     */
    public function clearLog($filename = 'application.log')
    {
        $filepath = $this->logPath . $filename;
        
        if (file_exists($filepath)) {
            return file_put_contents($filepath, '') !== false;
        }
        
        return true;
    }
    
    /**
     * Get log file size
     */
    public function getLogSize($filename = 'application.log')
    {
        $filepath = $this->logPath . $filename;
        
        if (file_exists($filepath)) {
            return filesize($filepath);
        }
        
        return 0;
    }
    
    /**
     * Get all log files
     */
    public function getLogFiles()
    {
        $files = [];
        $iterator = new DirectoryIterator($this->logPath);
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'log') {
                $files[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime()
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * Archive old logs
     */
    public function archiveLogs($days = 30)
    {
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $iterator = new DirectoryIterator($this->logPath);
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'log' && $file->getMTime() < $cutoffTime) {
                $archivePath = $this->logPath . 'archive/';
                
                if (!is_dir($archivePath)) {
                    mkdir($archivePath, 0755, true);
                }
                
                $archiveFile = $archivePath . $file->getFilename() . '.' . date('Y-m-d', $file->getMTime()) . '.gz';
                
                // Compress and move file
                $data = file_get_contents($file->getPathname());
                file_put_contents($archiveFile, gzencode($data));
                unlink($file->getPathname());
            }
        }
    }
}