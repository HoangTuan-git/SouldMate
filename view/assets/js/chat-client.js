// Real-time chat client
class ChatClient {
  constructor() {
    this.socket = null;
    this.currentUserId = null;
    this.currentReceiverId = null;
    this.typingTimer = null;
    this.isTyping = false;
    this.isOnline = false;
    this.onlineUsers = new Set();
    this.lastStatusUpdate = new Map(); // Track last update time for each user
    this.statusChangeTimers = new Map(); // Timers for delayed status changes
  }

  // Initialize socket connection
  init(userId) {
    this.currentUserId = userId;
    console.log('🚀 Initializing chat client for user:', userId);
    
    // Get JWT token from window
    const jwtToken = window.jwtToken || null;
    
    console.log('🔍 DEBUG - JWT Token from window:', jwtToken);
    console.log('🔍 DEBUG - Token type:', typeof jwtToken);
    console.log('🔍 DEBUG - Token length:', jwtToken ? jwtToken.length : 0);
    
    if (!jwtToken || jwtToken === '' || jwtToken === 'null') {
      console.error('❌ No JWT token found! Cannot connect to chat server.');
      console.error('❌ Token value:', jwtToken);
      alert('Không tìm thấy JWT token. Vui lòng đăng nhập lại!');
      return;
    }
    
    console.log('✅ JWT token found, connecting to chat server...');
    
    // Use localhost for development
    const socketUrl = 'http://localhost:3000';
    console.log('🔌 Connecting to:', socketUrl);
      
    this.socket = io(socketUrl, {
      transports: ['websocket', 'polling'],
      timeout: 20000,
      forceNew: true
    });
    
    this.socket.on('connect', () => {
      console.log('✅ Connected to chat server successfully');
      // Send JWT token with join event
      this.socket.emit('join', {
        userId: userId,
        token: jwtToken
      });
    });

    this.socket.on('disconnect', () => {
      console.log('❌ Disconnected from chat server');
    });

    this.socket.on('connect_error', (error) => {
      console.error('❌ Connection error:', error);
      alert('Không thể kết nối đến server chat. Vui lòng kiểm tra lại!');
    });

    // Listen for incoming messages
    this.socket.on('receive_message', (data) => {
      console.log('Received message:', data);
      // Only display if message is from current chat partner
      if (this.currentReceiverId && data.sender_id == this.currentReceiverId) {
        this.displayMessage(data, false);
        this.playNotificationSound();
      }
    });

    // Listen for message sent confirmation
    this.socket.on('message_sent', (data) => {
      // Message was successfully sent and saved to database

      console.log('Message sent successfully:', data);
    });

    // Listen for typing indicators
    this.socket.on('user_typing', (data) => {
      console.log('User typing:', data);
      this.showTypingIndicator(data.sender_id);
    });

    this.socket.on('user_stop_typing', (data) => {
      console.log('User stop typing:', data);
      this.hideTypingIndicator(data.sender_id);
    });

    // Listen for user online/offline status
    this.socket.on('user_online', (userId) => {
      console.log('User online:', userId);
      if (!this.onlineUsers) this.onlineUsers = new Set();
      
      // Clear any pending offline timer
      if (this.statusChangeTimers.has(userId.toString())) {
        clearTimeout(this.statusChangeTimers.get(userId.toString()));
        this.statusChangeTimers.delete(userId.toString());
        console.log(`Cleared pending offline timer for user ${userId}`);
      }
      
      this.onlineUsers.add(userId.toString());
      this.updateUserStatus(userId, true);
    });

    this.socket.on('user_offline', (userId) => {
      console.log('User offline:', userId);
      if (this.onlineUsers) this.onlineUsers.delete(userId.toString());
      
      // Add small delay to smooth out rapid online/offline changes
      const delayTimer = setTimeout(() => {
        this.updateUserStatus(userId, false);
        this.statusChangeTimers.delete(userId.toString());
      }, 500); // 500ms delay for smooth transition
      
      this.statusChangeTimers.set(userId.toString(), delayTimer);
    });

    // Listen for initial online users list
    this.socket.on('online_users_list', (onlineUsers) => {
      console.log('Received online users list:', onlineUsers);
      this.initializeUserStatuses(onlineUsers);
    });

    this.socket.on('error', (error) => {
      console.error('Socket error:', error);
      alert('Có lỗi xảy ra khi gửi tin nhắn');
    });
    this.socket.on('message_seen', (data) => {
    console.log('Tin nhắn đã xem bởi:', data.by);
  // Update UI: đổi icon thành ✓✓ xanh
});
  }

  // Khi mở chat
  openChat(receiverId) {
    this.setReceiver(receiverId);
    this.socket.emit('mark_as_read', { 
      sender_id: receiverId, 
      receiver_id: this.currentUserId 
    });
  }
  
  // Set current chat receiver
  setReceiver(receiverId) {
    this.currentReceiverId = receiverId;
    console.log('Set receiver to:', receiverId);
  }

  // Send message
  sendMessage(message) {
    if (!this.socket || !this.currentReceiverId || !message.trim()) {
      console.log('Cannot send message - missing socket, receiver, or empty message');
      return false;
    }

    const messageData = {
      sender_id: parseInt(this.currentUserId),
      receiver_id: parseInt(this.currentReceiverId),
      message: message.trim()
    };

    console.log('Sending message:', messageData);
    this.socket.emit('send_message', messageData);
    
    // Display message immediately for sender
    this.displayMessage({
      sender_id: parseInt(this.currentUserId),
      receiver_id: parseInt(this.currentReceiverId),
      message: message.trim(),
      timestamp: new Date().toISOString()
    }, true);

    return true;
  }
  
  // Display message in chat
  displayMessage(data, isSent) {
    const messagesContainer = document.querySelector('.messages-container');
    if (!messagesContainer) return;

    // Check if we're in the correct chat room
    if (!isSent && this.currentReceiverId != data.sender_id) {
      console.log('Message not for current chat room');
      return;
    }

    // Remove empty messages placeholder if exists
    const emptyMessages = document.querySelector('.empty-messages');
    if (emptyMessages) {
      emptyMessages.remove();
    }

    const messageWrapper = document.createElement('div');
    messageWrapper.className = `message-wrapper ${isSent ? 'me' : 'other'}`;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
    
    const messageContent = document.createElement('div');
    messageContent.className = 'message-content';
    messageContent.textContent = data.message;
    
    const messageTime = document.createElement('div');
    messageTime.className = 'message-time';
    const now = new Date();
    messageTime.textContent = now.toLocaleTimeString('vi-VN', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
    
    messageDiv.appendChild(messageContent);
    messageDiv.appendChild(messageTime);
    messageWrapper.appendChild(messageDiv);
    messagesContainer.appendChild(messageWrapper);

    // Scroll to bottom
    const chatBody = document.querySelector('#chatBody');
    if (chatBody) {
      chatBody.scrollTop = chatBody.scrollHeight;
    }
  }
  
  // Handle typing indicators
  startTyping() {
    if (!this.isTyping && this.currentReceiverId) {
      this.isTyping = true;
      this.socket.emit('typing', { receiver_id: this.currentReceiverId });
    }

    // Clear existing timer
    if (this.typingTimer) {
      clearTimeout(this.typingTimer);
    }

    // Set new timer to stop typing after 3 seconds
    this.typingTimer = setTimeout(() => {
      this.stopTyping();
    }, 3000);
  }

  stopTyping() {
    if (this.isTyping && this.currentReceiverId) {
      this.isTyping = false;
      this.socket.emit('stop_typing', { receiver_id: this.currentReceiverId });
    }
    
    if (this.typingTimer) {
      clearTimeout(this.typingTimer);
      this.typingTimer = null;
    }
  }

  showTypingIndicator(userId) {
    const chatBody = document.querySelector('#chatBody');
    if (!chatBody) return;

    // Only show typing indicator for current chat partner
    if (this.currentReceiverId != userId) {
      return;
    }

    // Show typing indicator
    const typingIndicator = document.querySelector('#typingIndicator');
    if (typingIndicator) {
      typingIndicator.style.display = 'flex';
      
      // Auto-scroll to bottom
      chatBody.scrollTop = chatBody.scrollHeight;
    }
  }

  hideTypingIndicator(userId) {
    const typingIndicator = document.querySelector('#typingIndicator');
    if (typingIndicator) {
      typingIndicator.style.display = 'none';
    }
  }

  // Initialize user statuses when receiving online users list
  initializeUserStatuses(onlineUsers) {
    console.log('🔄 Initializing user statuses...');
    
    // Store online users in a Set for faster lookup
    this.onlineUsers = new Set(onlineUsers.map(id => id.toString()));
    
    // Get all unique user IDs from the page
    const allUserIds = new Set();
    
    // Find all elements with data-user-id attribute
    document.querySelectorAll('[data-user-id]').forEach(element => {
      const userId = element.getAttribute('data-user-id');
      if (userId && userId !== 'null') {
        allUserIds.add(userId);
      }
    });
    
    console.log('Found user IDs on page:', Array.from(allUserIds));
    console.log('Online users from server:', onlineUsers);
    
    // Update statuses directly without resetting to avoid flicker
    allUserIds.forEach(userId => {
      const isOnline = this.onlineUsers.has(userId);
      this.updateUserStatus(userId, isOnline);
    });
    
    console.log('✅ User statuses initialized');
  }

  updateUserStatus(userId, isOnline) {
    // Rate limiting: only update if status actually changed or enough time passed
    const now = Date.now();
    const lastUpdate = this.lastStatusUpdate.get(userId);
    const timeSinceLastUpdate = now - (lastUpdate || 0);
    
    // Skip if same status was set recently (within 1 second)
    if (lastUpdate && timeSinceLastUpdate < 1000) {
      const currentElements = document.querySelectorAll(`.online-indicator[data-user-id="${userId}"]`);
      const currentlyOnline = currentElements.length > 0 && currentElements[0].classList.contains('active');
      if (currentlyOnline === isOnline) {
        console.log(`⏭️ Skipping redundant status update for user ${userId}`);
        return;
      }
    }
    
    console.log(`🔄 Updating status for user ${userId}: ${isOnline ? 'online' : 'offline'}`);
    this.lastStatusUpdate.set(userId, now);
    
    // Update all online indicators for this user
    const onlineIndicators = document.querySelectorAll(`.online-indicator[data-user-id="${userId}"]`);
    onlineIndicators.forEach(indicator => {
      if (isOnline) {
        indicator.classList.add('active');
      } else {
        indicator.classList.remove('active');
      }
    });
    
    // Update all status text elements for this user (chat-time in list)
    const chatTimeElements = document.querySelectorAll(`.chat-time[data-user-id="${userId}"]`);
    chatTimeElements.forEach(timeElement => {
      timeElement.textContent = isOnline ? 'Đang hoạt động' : 'Ngoại tuyến';
      timeElement.style.color = isOnline ? '#10b981' : '#94a3b8';
    });
    
    // Update status text in chat header
    const statusElements = document.querySelectorAll(`.status[data-user-id="${userId}"]`);
    statusElements.forEach(statusElement => {
      statusElement.textContent = isOnline ? 'Đang hoạt động' : 'Ngoại tuyến';
      statusElement.style.color = isOnline ? '#10b981' : '#94a3b8';
    });
    
    console.log(`✅ Updated ${onlineIndicators.length} indicators, ${chatTimeElements.length} time elements, ${statusElements.length} status elements`);
  }

  playNotificationSound() {
    // Simple notification sound
    try {
      const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmEcCEOa3+/JcSEFKIvO8tyJOgcZaqPl5KdKFAhDlNn0unIcBj2K2OvEbSEHJIjL7t+ROgcaYaLj6aJOEw5Um9vtrWUeMw3OwlV8dgBnvhYx');
      audio.play().catch(e => console.log('Could not play notification sound'));
    } catch (e) {
      console.log('Notification sound not available');
    }
  }

  // Disconnect from server
  disconnect() {
    // Clear typing timer
    if (this.typingTimer) {
      clearTimeout(this.typingTimer);
    }
    
    // Clear all status change timers
    this.statusChangeTimers.forEach(timer => clearTimeout(timer));
    this.statusChangeTimers.clear();
    
    if (this.socket) {
      console.log('Disconnecting from chat server...');
      this.socket.disconnect();
      this.socket = null;
    }
  }
}

// Global chat client instance
let chatClient = null;

// Initialize chat when page loads
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Chat client script loaded');
  
  // Check if Socket.io is available
  if (typeof io === 'undefined') {
    console.error('❌ Socket.io library not loaded!');
    alert('Socket.io không được tải. Vui lòng kiểm tra kết nối internet!');
    return;
  }
  
  console.log('✅ Socket.io library loaded successfully');
  
  // Get user ID from PHP session
  const userId = window.currentUserId ? window.currentUserId.toString() : null;
  const receiverId = window.currentReceiverId ? window.currentReceiverId.toString() : null;

  console.log('User ID:', userId, 'Receiver ID:', receiverId);
  
  if (userId && userId !== 'null') {
    console.log('🔄 Initializing chat client...');
    chatClient = new ChatClient();
    chatClient.init(userId);
    
    if (receiverId) {
      chatClient.setReceiver(receiverId);
    }
    
    // Handle form submission
    const chatForm = document.querySelector('#chatForm') || document.querySelector('.message-form');
    const messageInput = document.querySelector('#messageInput') || document.querySelector('.message-input');
    
    if (chatForm && messageInput) {
      chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (message && chatClient.sendMessage(message)) {
          messageInput.value = '';
          chatClient.stopTyping();
        }
      });
      
      // Handle typing indicators
      messageInput.addEventListener('input', function() {
        chatClient.startTyping();
      });
      
      messageInput.addEventListener('blur', function() {
        chatClient.stopTyping();
      });
      
      // Auto-scroll to bottom on page load
      const chatBody = document.querySelector('#chatBody');
      if (chatBody) {
        setTimeout(() => {
          chatBody.scrollTop = chatBody.scrollHeight;
        }, 100);
      }
    }
  }
});

// Handle page unload
window.addEventListener('beforeunload', function() {
  if (chatClient) {
    chatClient.disconnect();
  }
});

