const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const mysql = require('mysql2');
const jwt = require('jsonwebtoken');
const dotenv = require('dotenv');

// Load environment variables from .env file
dotenv.config();

console.log('🔧 Environment Configuration:');
console.log('   JWT_SECRET_KEY:', process.env.JWT_SECRET_KEY || 'NOT LOADED - using fallback');
console.log('   DB_HOST:', process.env.DB_HOST || 'NOT LOADED');
console.log('   DB_NAME:', process.env.DB_NAME || 'NOT LOADED');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

// JWT Secret Key (phải giống với PHP)
const JWT_SECRET_KEY = process.env.JWT_SECRET_KEY || 'my_sercret_key_12345';
console.log('🔑 Using JWT_SECRET_KEY:', JWT_SECRET_KEY);

// Middleware
app.use(cors());
app.use(express.json());

// Database connection
const db = mysql.createConnection({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'dating_app_final'
});

db.connect((err) => {
  if (err) {
    console.error('❌ Database connection failed:', err);
    return;
  }
  console.log('✅ Connected to MySQL database:', process.env.DB_NAME || 'dating_app_final');
});

// Store online users and offline timers
const onlineUsers = new Map();
const offlineTimers = new Map(); // Store timers for delayed offline status

// Socket.IO connection handling
io.on('connection', (socket) => {
  console.log('User connected:', socket.id);

  // User joins chat
  socket.on('join', (data) => {
    // Hỗ trợ cả format cũ (chỉ userId) và format mới (object với token)
    let userId, token;
    
    if (typeof data === 'object') {
      userId = data.userId;
      token = data.token;
    } else {
      // Backward compatibility - format cũ
      userId = data;
      token = null;
    }
    
    const userIdStr = userId.toString();
    
    // REQUIRED: Verify JWT token
    if (!token) {
      console.log(`❌ No token provided for user ${userId} - connection rejected`);
      socket.emit('error', 'Authentication required');
      socket.disconnect();
      return;
    }
    
    try {
      const decoded = jwt.verify(token, JWT_SECRET_KEY);
      
      // Kiểm tra userId trong token khớp với userId gửi lên
      if (decoded.uid != userId) {
        console.log(`❌ Token mismatch for user ${userId}`);
        socket.emit('error', 'Invalid token');
        socket.disconnect();
        return;
      }
      
      console.log(`✅ JWT verified for user ${userId}`);
    } catch (err) {
      console.log(`❌ JWT verification failed for user ${userId}:`, err.message);
      socket.emit('error', 'Token verification failed');
      socket.disconnect();
      return;
    }
    
    // Clear any pending offline timer for this user
    if (offlineTimers.has(userIdStr)) {
      clearTimeout(offlineTimers.get(userIdStr));
      offlineTimers.delete(userIdStr);
      console.log(`Cleared offline timer for user ${userId}`);
    }
    
    // Check if user was already online (reconnection)
    const wasAlreadyOnline = onlineUsers.has(userIdStr);
    
    onlineUsers.set(userIdStr, socket.id);
    socket.userId = userIdStr;
    console.log(`User ${userId} joined with socket ${socket.id}`);
    console.log('Current online users:', Array.from(onlineUsers.keys()));
    
    // Send current online users list to the new user
    const currentOnlineUsers = Array.from(onlineUsers.keys());
    socket.emit('online_users_list', currentOnlineUsers);
    
    // Only broadcast user online status if they weren't already online
    if (!wasAlreadyOnline) {
      socket.broadcast.emit('user_online', userId);
      console.log(`Broadcasted user_online for ${userId}`);
    }
  });

  // Handle sending messages
  socket.on('send_message', async (data) => {
    const { sender_id, receiver_id, message } = data;
    
    console.log(`Message from ${sender_id} to ${receiver_id}: ${message}`);
    
    try {
      // Save message to database
      const query = 'INSERT INTO tinnhan (maNguoiDung1, maNguoiDung2, noiDungText, thoiGianGui) VALUES (?, ?, ?, NOW())';
      db.execute(query, [sender_id, receiver_id, message], (err, result) => {
        if (err) {
          console.error('Error saving message:', err);
          socket.emit('error', 'Failed to save message');
          return;
        }
        
        console.log('Message saved to database with ID:', result.insertId);
        
        // Create message object with timestamp
        const messageData = {
          id: result.insertId,
          sender_id: parseInt(sender_id),
          receiver_id: parseInt(receiver_id),
          message: message,
          timestamp: new Date().toISOString()
        };
        
        // Send message to receiver if online
        const receiverSocketId = onlineUsers.get(receiver_id.toString());
        console.log(`Looking for receiver ${receiver_id}, found socket: ${receiverSocketId}`);
        
        if (receiverSocketId) {
          console.log(`Sending message to receiver socket: ${receiverSocketId}`);
          io.to(receiverSocketId).emit('receive_message', messageData);
        } else {
          console.log(`Receiver ${receiver_id} is not online`);
        }
        
        // Send confirmation back to sender
        socket.emit('message_sent', messageData);
      });
      
    } catch (error) {
      console.error('Error handling message:', error);
      socket.emit('error', 'Failed to send message');
    }
  });
  // Handle typing indicators
  socket.on('typing', (data) => {
    const { receiver_id } = data;
    const receiverSocketId = onlineUsers.get(receiver_id.toString());
    if (receiverSocketId) {
      io.to(receiverSocketId).emit('user_typing', {
        sender_id: socket.userId
      });
    }
  });

  socket.on('stop_typing', (data) => {
    const { receiver_id } = data;
    const receiverSocketId = onlineUsers.get(receiver_id.toString());
    if (receiverSocketId) {
      io.to(receiverSocketId).emit('user_stop_typing', {
        sender_id: socket.userId
      });
    }
  });

  // Handle disconnection
  socket.on('disconnect', () => {
    console.log('User disconnected:', socket.id);
    
    if (socket.userId) {
      const userId = socket.userId;
      
      // Don't remove immediately, set a timer for grace period
      const offlineTimer = setTimeout(() => {
        // Check if user is still offline after grace period
        if (!onlineUsers.has(userId)) {
          console.log(`User ${userId} went offline after grace period`);
          // Broadcast user offline status
          io.emit('user_offline', userId);
          offlineTimers.delete(userId);
        }
      }, 10000); // 10 seconds grace period

      // Store the timer
      offlineTimers.set(userId, offlineTimer);
      
      // Remove from online users immediately but don't broadcast offline yet
      onlineUsers.delete(userId);
      console.log(`User ${userId} removed from online users (grace period started)`);
      console.log('Current online users:', Array.from(onlineUsers.keys()));
    }
  });
});

// API endpoints
app.get('/online-users', (req, res) => {
  const users = Array.from(onlineUsers.keys());
  res.json({ online_users: users });
});

app.get('/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Chat server running on port ${PORT}`);
});
