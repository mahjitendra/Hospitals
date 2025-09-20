<?php

/**
 * Notification Model
 * 
 * Handles notification data operations
 */
class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'title', 'message', 'type', 'priority', 'sender_id', 'recipient_id',
        'recipient_type', 'target_audience', 'delivery_method', 'scheduled_at',
        'sent_at', 'read_at', 'is_read', 'status', 'metadata'
    ];
    
    /**
     * Get notifications by recipient
     */
    public function getByRecipient($recipientId, $recipientType = 'user')
    {
        return $this->where(
            'recipient_id = :recipient_id AND recipient_type = :type',
            ['recipient_id' => $recipientId, 'type' => $recipientType],
            'created_at DESC'
        );
    }
    
    /**
     * Get unread notifications
     */
    public function getUnread($recipientId, $recipientType = 'user')
    {
        return $this->where(
            'recipient_id = :recipient_id AND recipient_type = :type AND is_read = :read',
            ['recipient_id' => $recipientId, 'type' => $recipientType, 'read' => 0],
            'created_at DESC'
        );
    }
    
    /**
     * Get notifications by type
     */
    public function getByType($type, $recipientId = null, $recipientType = 'user')
    {
        $where = 'type = :type';
        $params = ['type' => $type];
        
        if ($recipientId) {
            $where .= ' AND recipient_id = :recipient_id AND recipient_type = :recipient_type';
            $params['recipient_id'] = $recipientId;
            $params['recipient_type'] = $recipientType;
        }
        
        return $this->where($where, $params, 'created_at DESC');
    }
    
    /**
     * Send notification
     */
    public function send($data)
    {
        // Set default values
        $data['sent_at'] = now();
        $data['status'] = 'sent';
        
        $notificationId = $this->create($data);
        
        // Send via specified delivery method
        $this->deliverNotification($notificationId, $data);
        
        return $notificationId;
    }
    
    /**
     * Send bulk notifications
     */
    public function sendBulk($recipients, $data)
    {
        $this->beginTransaction();
        
        try {
            $notificationIds = [];
            
            foreach ($recipients as $recipient) {
                $notificationData = array_merge($data, [
                    'recipient_id' => $recipient['id'],
                    'recipient_type' => $recipient['type'] ?? 'user'
                ]);
                
                $notificationIds[] = $this->send($notificationData);
            }
            
            $this->commit();
            return $notificationIds;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Mark as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $notification = $this->find($notificationId);
        if (!$notification) {
            return false;
        }
        
        // Verify user can mark this notification as read
        if ($userId && $notification['recipient_id'] != $userId) {
            return false;
        }
        
        return $this->update($notificationId, [
            'is_read' => 1,
            'read_at' => now()
        ]);
    }
    
    /**
     * Mark all as read
     */
    public function markAllAsRead($recipientId, $recipientType = 'user')
    {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = :read_at 
                WHERE recipient_id = :recipient_id AND recipient_type = :type AND is_read = 0";
        
        return $this->db->query($sql, [
            'read_at' => now(),
            'recipient_id' => $recipientId,
            'type' => $recipientType
        ]);
    }
    
    /**
     * Get notification statistics
     */
    public function getStats($recipientId = null, $recipientType = 'user')
    {
        $where = '1=1';
        $params = [];
        
        if ($recipientId) {
            $where .= ' AND recipient_id = :recipient_id AND recipient_type = :type';
            $params['recipient_id'] = $recipientId;
            $params['type'] = $recipientType;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_notifications,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_notifications,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
                    SUM(CASE WHEN type = 'info' THEN 1 ELSE 0 END) as info_notifications,
                    SUM(CASE WHEN type = 'warning' THEN 1 ELSE 0 END) as warning_notifications,
                    SUM(CASE WHEN type = 'success'  THEN 1 ELSE 0 END) as success_notifications,
                    SUM(CASE WHEN type = 'error' THEN 1 ELSE 0 END) as error_notifications
                FROM {$this->table}
                WHERE {$where}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Deliver notification
     */
    private function deliverNotification($notificationId, $data)
    {
        $deliveryMethods = explode(',', $data['delivery_method'] ?? 'system');
        
        foreach ($deliveryMethods as $method) {
            $method = trim($method);
            
            try {
                switch ($method) {
                    case 'email':
                        $this->sendEmailNotification($data);
                        break;
                    case 'sms':
                        $this->sendSMSNotification($data);
                        break;
                    case 'push':
                        $this->sendPushNotification($data);
                        break;
                    case 'system':
                    default:
                        // Already stored in database
                        break;
                }
            } catch (Exception $e) {
                Logger::error("Notification delivery failed ({$method}): " . $e->getMessage());
            }
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($data)
    {
        // Get recipient email
        $email = $this->getRecipientEmail($data['recipient_id'], $data['recipient_type']);
        
        if ($email) {
            $mailer = new Mailer();
            $mailer->send($email, $data['title'], 'notification', [
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type']
            ]);
        }
    }
    
    /**
     * Send SMS notification
     */
    private function sendSMSNotification($data)
    {
        // Get recipient phone
        $phone = $this->getRecipientPhone($data['recipient_id'], $data['recipient_type']);
        
        if ($phone) {
            $sms = new SMS();
            $message = $data['title'] . ': ' . $data['message'];
            $sms->send($phone, $message);
        }
    }
    
    /**
     * Send push notification
     */
    private function sendPushNotification($data)
    {
        // Implementation for push notifications
        // This would integrate with services like Firebase, OneSignal, etc.
    }
    
    /**
     * Get recipient email
     */
    private function getRecipientEmail($recipientId, $recipientType)
    {
        switch ($recipientType) {
            case 'user':
                $user = $this->userModel->find($recipientId);
                return $user['email'] ?? null;
            case 'student':
                $student = $this->studentModel->find($recipientId);
                return $student['email'] ?? null;
            case 'faculty':
                $faculty = $this->facultyModel->find($recipientId);
                return $faculty['email'] ?? null;
            default:
                return null;
        }
    }
    
    /**
     * Get recipient phone
     */
    private function getRecipientPhone($recipientId, $recipientType)
    {
        switch ($recipientType) {
            case 'student':
                $student = $this->studentModel->find($recipientId);
                return $student['phone'] ?? null;
            case 'faculty':
                $faculty = $this->facultyModel->find($recipientId);
                return $faculty['phone'] ?? null;
            default:
                return null;
        }
    }
    
    /**
     * Clean old notifications
     */
    public function cleanOldNotifications($days = 30)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->db->delete($this->table, 'created_at < :cutoff_date AND is_read = 1', [
            'cutoff_date' => $cutoffDate
        ]);
    }
}