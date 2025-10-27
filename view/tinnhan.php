<?php
if(!isset($_SESSION['uid'])){
    echo "<script>alert('Vui lòng đăng nhập để sử dụng chức năng này.');</script>";
    header("refresh:0.5;url=home.php?page=dangnhap");
    exit();
}
include_once("controller/cTinNhan.php");
include_once("controller/cNguoiDung.php");
    $chatController = new controlTinNhan();
    $ss_id = (int)$_SESSION['uid'];
    $friends = $chatController->getAllFriends($ss_id);
include_once(__DIR__ . "/../controller/cHoSo.php");
$hoso = new controlHoSo();
if (isset($_REQUEST['uid'])) $uid = $_REQUEST['uid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="view/assets/css/chat.css">
    <link rel="stylesheet" href="view/assets/css/chat-content.css">
    <style>
    /* Main chat container */
    .chat-main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        display: flex;
        gap: 20px;
        height: calc(100vh - 100px);
    }
    
    /* Chat list sidebar - 30% width */
    aside {
        width: 350px;
        min-width: 320px;
        flex-shrink: 0;
    }
    
    /* Chat content - takes remaining space */
    section.chat-content-container {
        flex: 1;
        min-width: 0;
    }
    
    /* Responsive for tablets */
    @media (max-width: 1024px) {
        .chat-main-container {
            padding: 15px;
            gap: 15px;
        }
        aside {
            width: 300px;
            min-width: 280px;
        }
    }
    
    /* Mobile: stack vertically or hide sidebar when chat is open */
    @media (max-width: 768px) {
        .chat-main-container {
            padding: 10px;
            gap: 0;
            height: calc(100vh - 80px);
        }
        
        aside {
            width: 100%;
            min-width: unset;
        }
        
        section.chat-content-container {
            width: 100%;
        }
        
        /* Hide chat list when viewing a conversation on mobile */
        aside.hide-on-mobile {
            display: none;
        }
        
        section.chat-content-container.show-on-mobile {
            display: block;
        }
    }
    .chat-list-container {
      background: #fff;
      border-radius: 16px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 12px rgba(15,23,42,.06);
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .chat-list-header {
      padding: 16px;
      border-bottom: 1px solid var(--border);
      background: #fafafa;
      flex-shrink: 0;
    }
    
    .chat-list-header h3 {
      margin: 0 0 4px;
      display: flex;
      align-items: center;
      color: #1e293b;
      font-size: 18px;
    }
    
    .chat-count {
      color: #64748b;
      font-size: 14px;
    }
    
    .chat-list {
      flex: 1;
      overflow-y: auto;
    }
    
    .chat-item {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      text-decoration: none;
      color: inherit;
      border-bottom: 1px solid #f1f5f9;
      transition: background 0.2s;
    }
    
    .chat-item:hover {
      background: #f8fafc;
    }
    
    .chat-item:last-child {
      border-bottom: none;
    }
    
    .chat-avatar {
      position: relative;
      margin-right: 12px;
    }
    
    .avatar-circle {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 600;
      font-size: 16px;
    }
    
    .online-indicator {
      position: absolute;
      bottom: 2px;
      right: 2px;
      width: 12px;
      height: 12px;
      background: #cbd5e1 !important; /* Mặc định offline - màu xám */
      border: 2px solid #fff;
      border-radius: 50%;
      transition: background 0.3s ease;
    }
    
    .online-indicator.active {
      background: #10b981 !important; /* Online - màu xanh */
    }
    .chat-info {
      flex: 1;
      min-width: 0;
    }
    
    .chat-name {
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 2px;
    }
    
    .chat-preview {
      color: #64748b;
      font-size: 14px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .chat-meta {
      text-align: right;
    }
    
    .chat-time {
      color: #94a3b8;
      font-size: 12px;
    }
    
    .empty-chat {
      text-align: center;
      padding: 48px 24px;
      color: #64748b;
    }
    
    .empty-icon {
      color: #cbd5e1;
      margin-bottom: 16px;
    }
    
    .empty-chat h4 {
      margin: 0 0 8px;
      color: #475569;
    }
    
    .empty-chat p {
      margin: 0 0 24px;
    }
    
    .btn-primary {
      display: inline-block;
      padding: 10px 20px;
      background: var(--fab);
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: background 0.2s;
    }
    
    .btn-primary:hover {
      background: #6d28d9;
    }
    
    /* Options Dropdown Styles */
    .options-container {
      position: relative;
    }
    
    .options-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
      min-width: 200px;
      z-index: 100;
      overflow: hidden;
      margin-top: 8px;
    }
    
    .options-header {
      padding: 12px 16px;
      background: #f8fafc;
      border-bottom: 1px solid var(--border);
    }
    
    .options-header h4 {
      margin: 0;
      font-size: 14px;
      font-weight: 600;
      color: #1e293b;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .options-list {
      padding: 8px 0;
    }
    
    .option-item {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      color: #475569;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
    }
    
    .option-item:hover {
      background: #f1f5f9;
      color: #1e293b;
    }
    
    .option-item svg {
      margin-right: 12px;
      flex-shrink: 0;
    }
    
    .option-item span {
      font-size: 14px;
      font-weight: 500;
    }
    
    /* Special styling for dangerous actions */
    .option-item:nth-child(2):hover, /* Block user */
    .option-item:nth-child(3):hover { /* Report user */
      background: #fef2f2;
      color: #dc2626;
    }
    
    .option-item:nth-child(2) svg, 
    .option-item:nth-child(3) svg {
      color: #ef4444;
    }
    
    /* Responsive for options dropdown */
    @media (max-width: 480px) {
      .options-dropdown {
        min-width: 180px;
        right: -10px;
      }
      
      .option-item {
        padding: 10px 12px;
      }
      
      .option-item span {
        font-size: 13px;
      }
      
      .options-header h4 {
        font-size: 13px;
      }
    }
    </style>
</head>
<body>
    <!-- Main chat container with flexbox layout -->
    <div class="chat-main-container">
        <!-- Chat list sidebar -->
        <aside class="chat-list-container <?php echo isset($uid) ? 'hide-on-mobile' : ''; ?>">
            <div class="chat-list-header">
                <h3><svg viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;margin-right:8px;"><path d="M4 4h16v12H7l-3 3V4z"/></svg>Tin nhắn</h3>
                <?php if(count($friends) > 0): ?>
                    <span class="chat-count"><?= count($friends) ?> cuộc trò chuyện</span>
                <?php endif; ?>
            </div>
            
            <div class="chat-list">
                <?php if(count($friends) > 0): ?>
                    <?php foreach($friends as $row): ?>
                        <a href="home.php?page=tinnhan&uid=<?= $row['maNguoiDung'] ?>" class="chat-item" data-user-id="<?= $row['maNguoiDung'] ?>">
                            <div class="chat-avatar">
                                <?php
                                echo '<img src="uploads/avatars/' . $row['avatar'] . '" class="avatar-circle" alt="">';
                                ?>
                                <div class="online-indicator" data-user-id="<?= $row['maNguoiDung'] ?>"></div>
                            </div>
                            <div class="chat-info">
                                <div class="chat-name"><?= htmlspecialchars($row['hoTen']) ?></div>
                                <?php
                                $lastMessageResult = $chatController->getLastMessage($_SESSION['uid'], $row['maNguoiDung']);
                                if ($lastMessageResult && $lastMessageResult->num_rows > 0) {
                                    $lastMessage = $lastMessageResult->fetch_assoc();
                                    $sent_at = date('H:i', strtotime($lastMessage['sent_at'] ?? 'now'));
                                    $previewText = strlen($lastMessage['msg']) > 30 ? substr($lastMessage['msg'], 0, 30) . '...' . ' ' . $sent_at : $lastMessage['msg'].' '.$sent_at;
                                    echo '<div class="chat-preview">' . htmlspecialchars($previewText) . '</div>';
                                } else {
                                    echo '<div class="chat-preview">Nhấn để bắt đầu trò chuyện</div>';
                                }
                                ?>
                            </div>
                            <div class="chat-meta">
                                <span class="chat-time" data-user-id="<?= $row['maNguoiDung'] ?>">Đang kiểm tra...</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-chat">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width:48px;height:48px;">
                                <path d="M4 4h16v12H7l-3 3V4z"/>
                            </svg>
                        </div>
                        <h4>Chưa có cuộc trò chuyện nào</h4>
                        <p>Hãy kết bạn để bắt đầu trò chuyện!</p>
                        <a href="home.php?page=timkiem" class="btn-primary">Tìm kiếm bạn bè</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Chat content area -->
        <section class="chat-content-container <?php echo isset($uid) ? 'show-on-mobile' : ''; ?>">
    <?php
    //if not have uid ask user to select a chat
    if (!isset($uid)) {
        echo '<div class="chat-window">';
        echo '<div class="chat-header-left">';
        echo '<h3>Chọn một cuộc trò chuyện</h3>';
        echo '</div>';
        echo '<div class="chat-body" id="chatBody" style="text-align:center; padding:50px;">';
        echo '<p>Vui lòng chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu nhắn tin.</p>';
        echo '</div>';
        echo '</div>';
    } else {
        $user = $hoso->checkHoSoExists($uid);
        $user = $user->fetch_assoc();
    ?>
        <div class="chat-window">
        <div class="chat-header">
            <div class="chat-header-left">
                <a href="home.php?page=tinnhan" class="back-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                    </svg>
                </a>
                <div class="chat-user-info">
                    <div class="chat-avatar-header">
                        <img src="uploads/avatars/<?= $user['avatar'] ?>" class="avatar-circle" alt="">
                        <div class="online-indicator" data-user-id="<?= $uid ?>"></div>
                    </div>
                    <div class="chat-user-details">
                        <h3><?= htmlspecialchars($user['hoTen']) ?></h3>
                        <span class="status" data-user-id="<?= $uid ?>"></span>
                    </div>
                </div>
            </div>
            <div class="chat-header-actions">
                <div class="options-container">
                    <button class="action-btn" title="Tùy chọn" onclick="toggleOptions()">
                        <svg viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                        </svg>
                    </button>

                    <div class="options-dropdown" id="moreOptions" style="display: none;">
                        <div class="options-header">
                            <h4><?= htmlspecialchars($user['hoTen']) ?></h4>
                        </div>
                        <div class="options-list">
                            <a href="home.php?page=profile&uid=<?= $uid ?>" class="option-item">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;">
                                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm8 8v-1a7 7 0 0 0-14 0v1z" />
                                </svg>
                                <span>Xem trang cá nhân</span>
                            </a>
                            <a href="home.php?page=block-user&uid=<?= $uid ?>" class="option-item" onclick="return confirmBlock();">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM4 12c0-4.42 3.58-8 8-8 1.85 0 3.55.63 4.9 1.69L5.69 16.9C4.63 15.55 4 13.85 4 12zm8 8c-1.85 0-3.55-.63-4.9-1.69L18.31 7.1C19.37 8.45 20 10.15 20 12c0 4.42-3.58 8-8 8z" />
                                </svg>
                                <span>Chặn người dùng</span>
                            </a>
                            <div class="option-item" onclick="showReportForm()">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;">
                                    <path d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3s.58-1.3 1.3-1.3 1.3.58 1.3 1.3-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                                </svg>
                                <span>Báo cáo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       
        </div>

        <div class="chat-body" id="chatBody">
            <div class="messages-container">
                <?php
                include_once("controller/cTinNhan.php");
                $p = new controlTinNhan();
                $messages = $p->getAllMessages($_SESSION['uid'], $uid);
                $lastMessage = $p->getLastMessage($_SESSION['uid'], $uid)->fetch_assoc();
                $date = '';

                if ($messages->num_rows > 0) {
                    while ($msg = $messages->fetch_assoc()) {
                        $today = date('d/m/Y', strtotime('today'));
                        $yesterday = date('d/m/Y', strtotime('yesterday'));
                        $date_sent = date('d/m/Y', strtotime($msg['sent_at'] ?? 'today'));

                        if ($date_sent !== $date) {
                            echo '<div class="date-separator" style="margin: 12px 0; text-align: center; color: #aaa;">';
                            echo $date_sent == $today ? 'Hôm nay' : ($date_sent == $yesterday ? 'Hôm qua' : $date_sent);
                            echo '</div>';
                            $date = $date_sent;
                        }

                        $isMe = $msg['maNguoiDung1'] == $_SESSION['uid'];
                        echo '<div class="message-wrapper ' . ($isMe ? 'me' : 'other') . '">';
                        echo '<div class="message ' . ($isMe ? 'sent' : 'received') . '">';
                        echo '<div class="message-content">' . htmlspecialchars($msg['noiDungText']) . '</div>';
                        echo '<div class="message-time">' . date('H:i', strtotime($msg['thoiGianGui'] ?? 'now')) . '</div>';
                        echo '</div>';
                        echo '</div>';

                        if ($isMe && $msg['maTinNhan'] == $lastMessage['maTinNhan']) {
                            echo '<div class="message-wrapper me"><span class="msg_status"></span></div>';
                        }
                    }
                } else {
                    echo '<div class="empty-messages">';
                    echo '<div class="empty-icon">';
                    echo '<svg viewBox="0 0 24 24" fill="currentColor" style="width:48px;height:48px;">';
                    echo '<path d="M4 4h16v12H7l-3 3V4z"/>';
                    echo '</svg>';
                    echo '</div>';
                    echo '<p>Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</p>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
                <span class="typing-text">Đang gõ...</span>
            </div>
        </div>
        <!-- check block status -->
        <?php
        if (isset($uid)) {
            $checkBlock = $row['trangThai'];
            if ($checkBlock == 'chan') {
        ?>
                <div class="chat-input">
                    <form action="#" method="post" id="chatForm" class="message-form" enctype="multipart/form-data">
                        <div class="input-container">
                            <?php
                            if ($result['maNguoiDung1'] == $_SESSION['uid']) {
                                echo '<input type="text" placeholder="Bạn đã chặn người dùng này. Không thể gửi tin nhắn." name="txtmessage" id="messageInput" autocomplete="off" class="message-input" disabled>';
                            } else {
                                echo '<input type="text" placeholder="Bạn đã bị chặn, không thể gửi tin nhắn." name="txtmessage" id="messageInput" autocomplete="off" class="message-input" disabled>';
                            }
                            ?>
                        </div>
                    </form>
                </div>
            <?php
            } else {
            ?>
                <div class="chat-input">
                    <form action="#" method="post" id="chatForm" class="message-form" enctype="multipart/form-data">
                        <div class="input-container">
                            <input type="text" placeholder="Nhập tin nhắn..." name="txtmessage" id="messageInput" autocomplete="off" class="message-input">
                            <input type="file" name="file_msg" id="file_msg" hidden>
                            <label for="file_msg" class="file-label"><i class="fa fa-paperclip" style="font-size: 32px;"></i></label>
                            <button type="submit" name="btn_msg" class="send-btn" title="Gửi tin nhắn">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
        <?php
            }
        } ?>
    <?php } ?>
        </div>
        </section>
    </div>
    <!-- End chat-main-container -->

<script>
     // Set user IDs and JWT token for JavaScript
        window.currentUserId = <?= isset($_SESSION['uid']) ? $_SESSION['uid'] : 'null' ?>;
        window.jwtToken = '<?= isset($_SESSION['jwt_token']) ? $_SESSION['jwt_token'] : '' ?>';

        // DEBUG: Log token để kiểm tra
        console.log('🔍 DEBUG - Current User ID:', window.currentUserId);
        console.log('🔍 DEBUG - JWT Token:', window.jwtToken);
        console.log('🔍 DEBUG - Token length:', window.jwtToken ? window.jwtToken.length : 0);

        <?php if (isset($uid)): ?>
            window.currentReceiverId = <?= $uid ?>;
        <?php endif; ?>

        // Options dropdown functions
        function toggleOptions() {
            const options = document.getElementById('moreOptions');
            if (options.style.display === 'none' || options.style.display === '') {
                options.style.display = 'block';
            } else {
                options.style.display = 'none';
            }
        }

        // Xác nhận chặn người dùng
        function confirmBlock() {
            return confirm('Bạn có chắc chắn muốn chặn người dùng này?');
        }

        // Hiển thị form báo cáo
        function showReportForm() {
            const reason = prompt('Vui lòng nhập lý do báo cáo:');
            if (reason && reason.trim()) {
                submitReport(<?= $uid ?>, reason.trim());
            }
            toggleOptions();
        }

        // Gửi báo cáo
        function submitReport(userId, reason) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'home.php?page=report-user';

            const uidInput = document.createElement('input');
            uidInput.type = 'hidden';
            uidInput.name = 'uid';
            uidInput.value = userId;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;

            form.appendChild(uidInput);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const optionsContainer = document.querySelector('.options-container');
            const dropdown = document.getElementById('moreOptions');

            if (optionsContainer && !optionsContainer.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
</script>

</body>
</html>