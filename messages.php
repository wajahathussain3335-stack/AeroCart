<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$my_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

// ==========================================
// 1. AJAX ENDPOINT: FETCH MESSAGES ASYNCHRONOUSLY
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'fetch_messages' && $receiver_id > 0) {
    // Mark incoming messages as read using Prepared Statement
    $update_stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    $update_stmt->bind_param("ii", $receiver_id, $my_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Fetch message history using Prepared Statement
    $msg_stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
    $msg_stmt->bind_param("iiii", $my_id, $receiver_id, $receiver_id, $my_id);
    $msg_stmt->execute();
    $messages_res = $msg_stmt->get_result();

    if ($messages_res->num_rows > 0) {
        while ($m = $messages_res->fetch_assoc()) {
            $is_my_msg = ($m['sender_id'] == $my_id);
            $justify = $is_my_msg ? 'justify-end' : 'justify-start';
            $bg = $is_my_msg ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white border text-gray-800 rounded-bl-none';
            
            echo '<div class="flex ' . $justify . ' items-center group">';
            if (!$is_my_msg) echo '<div class="flex items-center space-x-1">';
            
            echo '<div class="relative max-w-xs p-3 rounded-xl text-sm shadow-sm ' . $bg . '">' . htmlspecialchars($m['message_text']) . '</div>';
            
            // Individual message delete action link
            echo '<a href="messages.php?receiver_id=' . $receiver_id . '&delete_msg_id=' . $m['id'] . '" onclick="return confirm(\'Delete this message?\')" class="text-gray-400 hover:text-red-600 text-xs ml-2 mr-2 opacity-0 group-hover:opacity-100 transition font-bold">✕</a>';
            
            if (!$is_my_msg) echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-center text-gray-400 pt-10 text-sm">Say Hi! Start the conversation.</p>';
    }
    $msg_stmt->close();
    exit(); // Terminate execution for AJAX pull requests
}

// ==========================================
// 2. AJAX ENDPOINT: SEND MESSAGE ASYNCHRONOUSLY
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'send_message' && $receiver_id > 0 && isset($_POST['message_text'])) {
    $msg = trim($_POST['message_text']);
    if (!empty($msg)) {
        $send_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, is_read) VALUES (?, ?, ?, 0)");
        $send_stmt->bind_param("iis", $my_id, $receiver_id, $msg);
        if ($send_stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $send_stmt->close();
    }
    exit(); // Terminate execution for AJAX send requests
}

// ==========================================
// 3. STANDARD LINK ACTIONS (DELETION LOGIC) WITH PREPARED STATEMENTS
// ==========================================
if (isset($_GET['delete_msg_id'])) {
    $del_msg_id = intval($_GET['delete_msg_id']);
    $del_stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
    $del_stmt->bind_param("iii", $del_msg_id, $my_id, $my_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: messages.php?receiver_id=$receiver_id"); 
    exit();
}

if (isset($_GET['delete_chat_with'])) {
    $del_chat_user = intval($_GET['delete_chat_with']);
    $clear_stmt = $conn->prepare("DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
    $clear_stmt->bind_param("iiii", $my_id, $del_chat_user, $del_chat_user, $my_id);
    $clear_stmt->execute();
    $clear_stmt->close();
    header("Location: messages.php"); 
    exit();
}

// ==========================================
// 4. FETCH CONVERSATION SIDEBAR USERS (PREPARED STATEMENT)
// ==========================================
$sidebar_stmt = $conn->prepare("
    SELECT DISTINCT u.id, u.name,
    (SELECT COUNT(*) FROM messages m2 WHERE m2.sender_id = u.id AND m2.receiver_id = ? AND m2.is_read = 0) as user_unread_count
    FROM users u 
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id) 
    WHERE u.id != ?
");
$sidebar_stmt->bind_param("ii", $my_id, $my_id);
$sidebar_stmt->execute();
$users_list = $sidebar_stmt->get_result();

include('includes/header.php');
?>

<main class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl p-4 shadow-sm h-[500px] overflow-y-auto">
        <h2 class="font-bold text-lg mb-4 text-gray-800 border-b pb-2">Recent Chats 💬</h2>
        <div class="space-y-2">
            <?php if($users_list && $users_list->num_rows > 0): ?>
                <?php while($u = $users_list->fetch_assoc()): ?>
                    <a href="messages.php?receiver_id=<?php echo $u['id']; ?>" class="relative block p-3 rounded-lg font-semibold transition <?php echo $receiver_id == $u['id'] ? 'bg-blue-600 text-white' : ($u['user_unread_count'] > 0 ? 'bg-green-50 text-green-900 border border-green-200' : 'bg-gray-50 hover:bg-gray-100 text-gray-700'); ?>">
                        <?php echo htmlspecialchars($u['name']); ?>
                        <?php if($u['user_unread_count'] > 0 && $receiver_id != $u['id']): ?>
                            <span class="absolute right-3 top-3.5 bg-green-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">
                                <?php echo $u['user_unread_count']; ?> New
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-sm text-gray-400 text-center pt-10">No chats found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="md:col-span-2 bg-white border rounded-xl flex flex-col justify-between shadow-sm h-[500px]">
        <?php if($receiver_id > 0): ?>
            <div class="p-4 border-b bg-gray-50 rounded-t-xl font-bold text-gray-800 flex justify-between items-center">
                <span>Active Conversation</span>
                <div class="flex items-center space-x-3">
                    <a href="messages.php?delete_chat_with=<?php echo $receiver_id; ?>" onclick="return confirm('Are you sure you want to clear the entire chat history with this user?')" class="bg-red-50 text-red-600 hover:bg-red-100 text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm border border-red-200">
                        Clear Chat 🗑️
                    </a>
                </div>
            </div>
            
            <div id="chat-messages-container" class="p-4 overflow-y-auto flex-1 space-y-3 bg-gray-50/50">
                </div>

            <form id="ajax-chat-form" class="p-4 border-t flex gap-2">
                <input type="text" id="message-input-field" name="message_text" required placeholder="Type your message here..." class="flex-1 p-2 border rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" autocomplete="off">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-bold text-sm hover:bg-blue-700 transition">Send</button>
            </form>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                <span class="text-4xl mb-2">📥</span>
                <p class="text-sm font-medium">Select a user from the sidebar to start chatting or managing messages.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
const receiverId = <?php echo $receiver_id; ?>;
const chatBox = document.getElementById('chat-messages-container');
const chatForm = document.getElementById('ajax-chat-form');
const messageInput = document.getElementById('message-input-field');
let firstLoad = true;

// Function to fetch messages via API endpoint asynchronously
function fetchMessagesAsync() {
    if(receiverId === 0) return;
    
    fetch(`messages.php?receiver_id=${receiverId}&action=fetch_messages`)
        .then(response => response.text())
        .then(htmlOutput => {
            // Check if user is scrolled to bottom before updating text content
            const shouldScroll = chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 100;
            
            chatBox.innerHTML = htmlOutput;
            
            if(firstLoad || shouldScroll) {
                chatBox.scrollTop = chatBox.scrollHeight; // Force scroll to bottom view
                firstLoad = false;
            }
        })
        .catch(err => console.error("Real-time engine polling error: ", err));
}

// Handle Form Submit Event using Fetch API (No Page Reload!)
if(chatForm) {
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Stop native page reload routing
        
        const textValue = messageInput.value.trim();
        if(textValue === '') return;
        
        const formData = new FormData();
        formData.append('message_text', textValue);
        
        // Post message text securely to execution handler
        fetch(`messages.php?receiver_id=${receiverId}&action=send_message`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(status => {
            if(status === 'success') {
                messageInput.value = ''; // Clean input placeholder smoothly
                fetchMessagesAsync(); // Instantly refresh active window data
            } else {
                alert("Message transmission failed. Please retry.");
            }
        });
    });
}

// Initial engine load trigger and active interval cycle polling
if(receiverId > 0) {
    fetchMessagesAsync();
    setInterval(fetchMessagesAsync, 2500); // Polls every 2.5 seconds dynamically
}
</script>

<?php 
$sidebar_stmt->close();
include('includes/footer.php'); 
?>