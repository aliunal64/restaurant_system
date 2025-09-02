<?php 
include 'header.php';

// Handle message deletion
if ($_POST && isset($_POST['delete_message'])) {
    $message_id = (int)$_POST['message_id'];
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$message_id]);
    set_success_message('Message deleted successfully!');
    header('Location: messages.php');
    exit();
}

// Get all messages
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 fw-bold mb-4">Messages</h1>
            
            <?php display_success_message(); ?>
            <?php display_error_message(); ?>
            
            <?php if (empty($messages)): ?>
            <div class="text-center py-5">
                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">No messages yet</h3>
                <p class="text-muted">Customer messages will appear here.</p>
            </div>
            <?php else: ?>
            
            <div class="row">
                <?php foreach ($messages as $message): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?php echo htmlspecialchars($message['subject']); ?></h6>
                            <small class="text-muted"><?php echo date('M j, Y', strtotime($message['created_at'])); ?></small>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?><br>
                                <strong>Email:</strong> 
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </a>
                            </div>
                            <div class="message-content">
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-reply"></i> Reply
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" name="delete_message" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
