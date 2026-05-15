<?php
/**
 * views/seeker/messages.php
 */
require_once '../../app/controllers/SeekerController.php';

$controller = new SeekerController();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'send') {
    $controller->sendMessage();
}

$inbox = $controller->getInbox();
$activeContactId = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : null;
$conversation = [];

if ($activeContactId) {
    $conversation = $controller->getConversation($activeContactId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); display: flex; height: 80vh; }
        
        .inbox-list { width: 30%; border-right: 1px solid #ddd; overflow-y: auto; padding-right: 15px; }
        .chat-area { width: 70%; display: flex; flex-direction: column; padding-left: 20px; }
        
        .contact-card { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; display: block; text-decoration: none; color: inherit; }
        .contact-card:hover { background-color: #f9f9f9; }
        .contact-card.active { background-color: #e9ecef; }
        .unread-badge { background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; float: right; }
        
        .messages-list { flex-grow: 1; overflow-y: auto; padding-right: 10px; margin-bottom: 20px; }
        .msg-bubble { max-width: 70%; padding: 10px; border-radius: 15px; margin-bottom: 10px; clear: both; }
        .msg-sent { background-color: #007bff; color: white; float: right; border-bottom-right-radius: 0; }
        .msg-received { background-color: #f1f0f0; color: #333; float: left; border-bottom-left-radius: 0; }
        
        .message-form { display: flex; margin-top: auto; }
        .message-form input { flex-grow: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .message-form button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; margin-left: 10px; cursor: pointer; }
        
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; position: absolute; top: 10px; left: 20px; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        
        .time-sm { font-size: 10px; opacity: 0.7; display: block; margin-top: 5px; }
    </style>
</head>
<body style="padding-top: 60px;">

<div class="navbar">
    <a href="dashboard.php">← Back to Dashboard</a>
    <a href="messages.php">Messages</a>
</div>

<div class="container">
    <!-- Inbox List -->
    <div class="inbox-list">
        <h3>Inbox</h3>
        <?php if (empty($inbox)): ?>
            <p>No messages yet.</p>
        <?php else: ?>
            <?php foreach ($inbox as $contact): ?>
                <a href="messages.php?contact_id=<?= $contact['contact_id'] ?>" 
                   class="contact-card <?= ($activeContactId == $contact['contact_id']) ? 'active' : '' ?>">
                    <strong><?= htmlspecialchars($contact['contact_name']) ?></strong> 
                    <small>(<?= htmlspecialchars($contact['contact_role']) ?>)</small>
                    <?php if ($contact['unread_count'] > 0): ?>
                        <span class="unread-badge"><?= $contact['unread_count'] ?></span>
                    <?php endif; ?>
                    <p style="margin: 5px 0 0; font-size: 14px; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($contact['latest_body']) ?>
                    </p>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
        <?php if ($activeContactId): ?>
            <div class="messages-list" id="messages-list">
                <?php if (empty($conversation)): ?>
                    <p style="text-align: center; color: #888;">Start the conversation!</p>
                <?php else: ?>
                    <?php foreach ($conversation as $msg): ?>
                        <div class="msg-bubble <?= ($msg['sender_id'] == Session::get('user_id')) ? 'msg-sent' : 'msg-received' ?>">
                            <?= nl2br(htmlspecialchars($msg['body'])) ?>
                            <span class="time-sm"><?= date('M j, g:i a', strtotime($msg['sent_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form class="message-form" method="POST" action="messages.php">
                <input type="hidden" name="action" value="send">
                <input type="hidden" name="recipient_id" value="<?= $activeContactId ?>">
                <input type="text" name="body" placeholder="Type a message..." required autocomplete="off">
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #888;">
                <p>Select a contact to view conversation</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Auto-scroll to bottom of chat
    var msgList = document.getElementById('messages-list');
    if (msgList) {
        msgList.scrollTop = msgList.scrollHeight;
    }
</script>

</body>
</html>
