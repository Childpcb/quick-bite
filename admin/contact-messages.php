<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

// Mark as read
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: contact-messages.php');
    exit();
}

// Delete message
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: contact-messages.php?deleted=1');
    exit();
}

// Send reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $messageId = (int)$_POST['message_id'];
    $replyMessage = trim($_POST['reply_message'] ?? '');
    
    if (!empty($replyMessage)) {
        // Get original message
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        $original = $stmt->fetch();
        
        if ($original) {
            // Save reply to database
            $stmt = $pdo->prepare("
                UPDATE contact_messages 
                SET reply = ?, replied_at = NOW(), status = 'read' 
                WHERE id = ?
            ");
            $stmt->execute([$replyMessage, $messageId]);
            
            // Try to send email
            $to = $original['email'];
            $subject = "Re: " . $original['subject'];
            $emailBody = "
            <html>
            <head><title>Reply from Quick Bite</title></head>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Reply from Quick Bite</h2>
                <p><strong>Your message:</strong></p>
                <blockquote style='background: #f0f0f0; padding: 10px; border-left: 4px solid #6C63FF;'>
                    " . nl2br(htmlspecialchars($original['message'])) . "
                </blockquote>
                <p><strong>Our reply:</strong></p>
                <div style='background: #f9fafb; padding: 15px; border-radius: 8px;'>
                    " . nl2br(htmlspecialchars($replyMessage)) . "
                </div>
                <hr>
                <p>Best regards,<br>Quick Bite Team</p>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: Quick Bite <alisonnaftali2003@gmail.com>\r\n";
            
            @mail($to, $subject, $emailBody, $headers);
            
            header('Location: contact-messages.php?replied=1');
            exit();
        }
    }
}

// View single message
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$viewMessage = null;

if ($viewId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$viewId]);
    $viewMessage = $stmt->fetch();
}

// Get all messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

$success = isset($_GET['deleted']) ? 'Message deleted successfully!' : 
           (isset($_GET['replied']) ? 'Reply sent successfully!' : '');
?>
<?php require_once __DIR__ . '/../includes/admin-header.php'; ?>
<?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>

<div class="ml-0 md:ml-64 pt-20 pb-12 px-4 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Contact Messages</h1>
                <p class="text-gray-500 mt-1">View and reply to customer messages</p>
            </div>
            <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg shadow-sm">
                Total: <?php echo count($messages); ?> messages
            </span>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- ===== VIEW SINGLE MESSAGE WITH REPLY FORM ===== -->
        <?php if ($viewMessage): ?>
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border-l-4 border-indigo-500">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Message Details</h2>
                        <p class="text-sm text-gray-500">From: <?php echo htmlspecialchars($viewMessage['name']); ?> (<?php echo htmlspecialchars($viewMessage['email']); ?>)</p>
                        <p class="text-sm text-gray-500">Date: <?php echo date('M d, Y h:i A', strtotime($viewMessage['created_at'])); ?></p>
                    </div>
                    <a href="contact-messages.php" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </a>
                </div>
                
                <!-- Subject -->
                <div class="mb-4">
                    <p class="text-sm text-gray-500">Subject:</p>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($viewMessage['subject']); ?></p>
                </div>
                
                <!-- Original Message -->
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">Message:</p>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($viewMessage['message']); ?></p>
                    </div>
                </div>
                
                <!-- Previous Reply (if exists) -->
                <?php if (!empty($viewMessage['reply'])): ?>
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Your Previous Reply:</p>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($viewMessage['reply']); ?></p>
                            <p class="text-xs text-gray-500 mt-2">Replied: <?php echo date('M d, Y h:i A', strtotime($viewMessage['replied_at'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Reply Form -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">
                        <i class="fas fa-reply text-indigo-600"></i> Reply to this message
                    </h3>
                    <form method="POST" action="">
                        <input type="hidden" name="message_id" value="<?php echo $viewMessage['id']; ?>">
                        <textarea name="reply_message" rows="4" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3"
                                  placeholder="Write your reply here..."><?php echo htmlspecialchars($viewMessage['reply'] ?? ''); ?></textarea>
                        <div class="flex gap-3">
                            <button type="submit" name="reply" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                                <i class="fas fa-paper-plane mr-2"></i> Send Reply
                            </button>
                            <a href="contact-messages.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- ===== MESSAGES TABLE ===== -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($messages)): ?>
                            <?php $counter = 1; foreach ($messages as $msg): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo $counter++; ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($msg['name']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($msg['email']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars(substr($msg['subject'], 0, 40)) . (strlen($msg['subject']) > 40 ? '...' : ''); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $msg['status'] === 'unread' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                        <?php if (!empty($msg['reply'])): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700 ml-1">Replied</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        <?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <!-- VIEW BUTTON -->
                                            <a href="?view=<?php echo $msg['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700" title="View Message">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- MARK AS READ -->
                                            <?php if ($msg['status'] === 'unread'): ?>
                                                <a href="?mark_read=1&id=<?php echo $msg['id']; ?>" 
                                                   class="text-green-600 hover:text-green-700" title="Mark as Read">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <!-- DELETE -->
                                            <a href="?delete=1&id=<?php echo $msg['id']; ?>" 
                                               onclick="return confirm('Delete this message?')" 
                                               class="text-red-600 hover:text-red-700" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <div class="text-4xl mb-2">📭</div>
                                    <p>No messages found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>