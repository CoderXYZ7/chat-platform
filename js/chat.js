document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const chatroomId = document.getElementById('chatroomId').value;
    const userId = document.getElementById('userId').value;
    const chatMessages = document.getElementById('chatMessages');
    
    // Load messages when page loads
    loadMessages();
    
    // Set up polling to check for new messages every 3 seconds
    setInterval(loadMessages, 3000);
    
    // Handle message form submission
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            
            if (message !== '') {
                sendMessage(message);
                messageInput.value = '';
            }
        });
    }
    
    // Load messages from the server
    function loadMessages() {
        fetch(`get_messages.php?chatroom=${chatroomId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.messages);
                } else {
                    console.error('Error loading messages:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }
    
    // Send a message to the server
    function sendMessage(message) {
        const formData = new FormData();
        formData.append('chatroom_id', chatroomId);
        formData.append('user_id', userId);
        formData.append('message', message);
        
        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages(); // Reload messages after sending
            } else {
                console.error('Error sending message:', data.message);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
        });
    }
    
    // Display messages in the chat
    function displayMessages(messages) {
        // Only update if we have new messages
        if (!messages || messages.length === 0) {
            chatMessages.innerHTML = '<div class="no-messages">No messages yet. Be the first to send a message!</div>';
            return;
        }
        
        // Clear loading message
        chatMessages.innerHTML = '';
        
        // Display messages in reverse order (newest at the bottom)
        messages.reverse().forEach(msg => {
            const messageElement = document.createElement('div');
            messageElement.className = `message ${msg.user_id == userId ? 'message-own' : ''}`;
            
            const header = document.createElement('div');
            header.className = 'message-header';
            
            const username = document.createElement('span');
            username.className = 'message-username';
            username.textContent = msg.username;
            
            const time = document.createElement('span');
            time.className = 'message-time';
            time.textContent = formatTime(msg.created_at);
            
            header.appendChild(username);
            header.appendChild(time);
            
            const content = document.createElement('div');
            content.className = 'message-content';
            content.textContent = msg.message;
            
            messageElement.appendChild(header);
            messageElement.appendChild(content);
            
            chatMessages.appendChild(messageElement);
        });
        
        // Scroll to the bottom of the chat
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Format timestamp for display
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
});