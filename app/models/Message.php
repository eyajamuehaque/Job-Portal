<?php
/**
 * app/models/Message.php
 * Handles database operations for the 'messages' table.
 */

class Message {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Send a message
     */
    public function send($sender_id, $recipient_id, $body, $application_id = null) {
        $sql = "INSERT INTO messages (sender_id, recipient_id, body, application_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            $application_id = empty($application_id) ? null : $application_id;
            mysqli_stmt_bind_param($stmt, "iisi", $sender_id, $recipient_id, $body, $application_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get conversation between two users
     */
    public function getConversation($user1_id, $user2_id) {
        $sql = "SELECT m.*, u1.name as sender_name, u2.name as recipient_name 
                FROM messages m
                JOIN users u1 ON m.sender_id = u1.id
                JOIN users u2 ON m.recipient_id = u2.id
                WHERE (m.sender_id = ? AND m.recipient_id = ?) 
                   OR (m.sender_id = ? AND m.recipient_id = ?)
                ORDER BY m.sent_at ASC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiii", $user1_id, $user2_id, $user2_id, $user1_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $messages;
        }
        return [];
    }

    /**
     * Get inbox summary (list of contacts with latest message)
     */
    public function getInboxSummary($user_id) {
        $sql = "SELECT 
                    CASE 
                        WHEN sender_id = ? THEN recipient_id 
                        ELSE sender_id 
                    END as contact_id,
                    u.name as contact_name,
                    u.role as contact_role,
                    MAX(sent_at) as last_message_time,
                    (SELECT body FROM messages WHERE 
                        (sender_id = ? AND recipient_id = contact_id) OR 
                        (sender_id = contact_id AND recipient_id = ?) 
                     ORDER BY sent_at DESC LIMIT 1) as latest_body,
                    SUM(CASE WHEN recipient_id = ? AND is_read = 0 THEN 1 ELSE 0 END) as unread_count
                FROM messages m
                JOIN users u ON u.id = CASE WHEN sender_id = ? THEN recipient_id ELSE sender_id END
                WHERE sender_id = ? OR recipient_id = ?
                GROUP BY contact_id, contact_name, contact_role
                ORDER BY last_message_time DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $summary = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $summary;
        }
        return [];
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($recipient_id, $sender_id) {
        $sql = "UPDATE messages SET is_read = 1 WHERE recipient_id = ? AND sender_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $recipient_id, $sender_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
?>
