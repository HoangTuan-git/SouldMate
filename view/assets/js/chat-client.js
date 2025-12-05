// Real-time chat client
class ChatClient {
  constructor() {
    this.socket = null;
    this.currentUserId = null;
    this.currentReceiverId = null;
    this.typingTimer = null;
    this.isTyping = false;
    
    // Cache DOM elements
    this.elements = {
      messagesContainer: null,
      chatBody: null,
      typingIndicator: null
    };
  }
  
  // Helper: Cache DOM elements
  cacheElements() {
    this.elements.messagesContainer = document.querySelector('.messages-container');
    this.elements.chatBody = document.querySelector('#chatBody');
    this.elements.typingIndicator = document.querySelector('#typingIndicator');
  }
  
  // Helper: Scroll chat to bottom
  scrollToBottom() {
    if (this.elements.chatBody) {
      this.elements.chatBody.scrollTop = this.elements.chatBody.scrollHeight;
    }
  }

  // Initialize socket connection
  init(userId) {
    this.currentUserId = userId;
    console.log('Initializing chat client for user:', userId);
    const socketUrl = 'http://localhost:3000';
    
    // Cache DOM elements
    this.cacheElements();
      
    this.socket = io(socketUrl, {
      transports: ['websocket', 'polling'],
      timeout: 20000,
      forceNew: true
    });
    
    this.setupSocketListeners();
  }
  
  // Setup all socket event listeners
  setupSocketListeners() {
    this.socket.on('connect', () => {
      console.log('✅ Connected to chat server');
      // Gửi join với JWT token
      this.socket.emit('join', {
        userId: this.currentUserId,
        token: window.jwtToken || ''
      });
    });

    this.socket.on('disconnect', () => {
      console.log('❌ Disconnected from chat server');
    });

    this.socket.on('connect_error', (error) => {
      console.error('❌ Connection error:', error);
      alert('Không thể kết nối đến server chat. Vui lòng kiểm tra lại!');
    });

    // Message events
    this.socket.on('receive_message', (data) => {
      if (this.currentReceiverId && data.sender_id == this.currentReceiverId) {
        this.displayMessage(data, false);
        this.playNotificationSound();
      }
    });

    // Message status events removed

    // Typing events
    this.socket.on('user_typing', (data) => {
      this.showTypingIndicator(data.sender_id);
    });

    this.socket.on('user_stop_typing', (data) => {
      this.hideTypingIndicator(data.sender_id);
    });

    // User status events
    this.socket.on('user_online', (userId) => {
      this.updateUserStatus(userId, true);
    });

    this.socket.on('user_offline', (userId) => {
      this.updateUserStatus(userId, false);
    });

    this.socket.on('online_users_list', (onlineUsers) => {
      this.initializeUserStatuses(onlineUsers);
    });

    this.socket.on('error', (error) => {
      console.error('Socket error:', error);
      alert('Có lỗi xảy ra khi gửi tin nhắn');
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
    
    // Display message immediately for sender with "sent" status
    this.displayMessage({
      sender_id: parseInt(this.currentUserId),
      receiver_id: parseInt(this.currentReceiverId),
      message: message.trim(),
      timestamp: new Date().toISOString(),
      status: 'sent'
    }, true);

    return true;
  }
  
  // Display message in chat
  displayMessage(data, isSent) {
    if (!this.elements.messagesContainer) return;

    // Check if we're in the correct chat room
    if (!isSent && this.currentReceiverId != data.sender_id) return;

    // Remove empty messages placeholder if exists
    const emptyMessages = document.querySelector('.empty-messages');
    if (emptyMessages) emptyMessages.remove();

    const messageWrapper = document.createElement('div');
    messageWrapper.className = `message-wrapper ${isSent ? 'me' : 'other'}`;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
    
    const messageContent = document.createElement('div');
    messageContent.className = 'message-content';
    messageContent.textContent = data.message;
    
    const messageTime = document.createElement('div');
    messageTime.className = 'message-time';
    messageTime.textContent = new Date().toLocaleTimeString('vi-VN', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
    
    messageDiv.appendChild(messageContent);
    messageDiv.appendChild(messageTime);
    
    messageWrapper.appendChild(messageDiv);
    this.elements.messagesContainer.appendChild(messageWrapper);

    this.scrollToBottom();
  }
  // Handle typing indicators
  startTyping() {
    if (this.isTyping || !this.currentReceiverId) return;
    
    this.isTyping = true;
    this.socket.emit('typing', { receiver_id: this.currentReceiverId });

    clearTimeout(this.typingTimer);
    this.typingTimer = setTimeout(() => this.stopTyping(), 3000);
  }

  stopTyping() {
    if (!this.isTyping || !this.currentReceiverId) return;
    
    this.isTyping = false;
    this.socket.emit('stop_typing', { receiver_id: this.currentReceiverId });
    clearTimeout(this.typingTimer);
    this.typingTimer = null;
  }

  showTypingIndicator(userId) {
    if (!this.elements.typingIndicator || this.currentReceiverId != userId) return;
    
    this.elements.typingIndicator.style.display = 'flex';
    this.scrollToBottom();
  }

  hideTypingIndicator(userId) {
    if (this.elements.typingIndicator) {
      this.elements.typingIndicator.style.display = 'none';
    }
  }

  // Initialize user statuses when receiving online users list
  initializeUserStatuses(onlineUsers) {
    // Get all unique user IDs from the page
    const allUserIds = new Set();
    document.querySelectorAll('[data-user-id]').forEach(element => {
      const userId = element.getAttribute('data-user-id');
      if (userId && userId !== 'null') {
        allUserIds.add(userId);
      }
    });
    
    // Set all users to offline first, then set online users to online
    allUserIds.forEach(userId => this.updateUserStatus(userId, false));
    onlineUsers.forEach(userId => this.updateUserStatus(userId.toString(), true));
  }

  updateUserStatus(userId, isOnline) {
    const statusConfig = [
      {
        selector: `.online-indicator[data-user-id="${userId}"]`,
        update: (el) => el.classList.toggle('active', isOnline)
      },
      {
        selector: `.chat-time[data-user-id="${userId}"], .status[data-user-id="${userId}"]`,
        update: (el) => {
          el.textContent = isOnline ? 'Đang hoạt động' : 'Ngoại tuyến';
          el.style.color = isOnline ? '#10b981' : '#94a3b8';
        }
      }
    ];
    
    statusConfig.forEach(({ selector, update }) => {
      document.querySelectorAll(selector).forEach(update);
    });
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
    if (this.socket) {
      this.socket.disconnect();
    }
  }
}

// Global chat client instance
let chatClient = null;

// Initialize chat when page loads
document.addEventListener('DOMContentLoaded', function() {
  if (typeof io === 'undefined') {
    console.error('❌ Socket.io library not loaded!');
    alert('Socket.io không được tải. Vui lòng kiểm tra kết nối internet!');
    return;
  }
  
  const userId = window.currentUserId?.toString();
  const receiverId = window.currentReceiverId?.toString();

  if (!userId) return;
  
  chatClient = new ChatClient();
  chatClient.init(userId);
  
  if (receiverId) {
    chatClient.setReceiver(receiverId);
  }
  
  // Handle form submission
  const chatForm = document.querySelector('#chatForm') || document.querySelector('.message-form');
  const messageInput = document.querySelector('#messageInput') || document.querySelector('.message-input');
  
  if (chatForm && messageInput) {
    chatForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const message = messageInput.value.trim();
      if (message && chatClient.sendMessage(message)) {
        messageInput.value = '';
        chatClient.stopTyping();
      }
    });
    
    messageInput.addEventListener('input', () => chatClient.startTyping());
    messageInput.addEventListener('blur', () => chatClient.stopTyping());
    
    // Auto-scroll to bottom on page load
    setTimeout(() => chatClient.scrollToBottom(), 100);
  }
});

// Handle page unload
window.addEventListener('beforeunload', function() {
  if (chatClient) {
    chatClient.disconnect();
  }
});

