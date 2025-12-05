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
                                    $sent_at = date('H:i', strtotime($lastMessage['thoiGianGui'] ?? 'now'));
                                    $previewText = strlen($lastMessage['noiDungText']) > 30 ? substr($lastMessage['noiDungText'], 0, 30) . '...' . ' ' . $sent_at : $lastMessage['noiDungText'].' '.$sent_at;
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
        ?>
        <div class="chat-window">
            <div class="chat-empty-state">
                <div class="empty-state-content">
                    <div class="empty-state-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <path d="M8 10h.01M12 10h.01M16 10h.01"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">Chưa chọn cuộc trò chuyện</h3>
                    <p class="empty-state-text">Hãy chọn một người bạn từ danh sách bên trái để bắt đầu trò chuyện</p>
                    <div class="empty-state-illustration">
                        <svg viewBox="0 0 200 150" fill="none">
                            <!-- Illustration -->
                            <circle cx="60" cy="50" r="20" fill="#e2e8f0"/>
                            <circle cx="140" cy="50" r="20" fill="#e2e8f0"/>
                            <path d="M70 50 Q100 30 130 50" stroke="#cbd5e1" stroke-width="2" fill="none"/>
                            <circle cx="100" cy="100" r="8" fill="#667eea"/>
                            <circle cx="115" cy="100" r="8" fill="#764ba2"/>
                            <circle cx="85" cy="100" r="8" fill="#a6c1ee"/>
                        </svg>
                    </div>
                    <div class="empty-state-tip">
                        <svg viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;margin-right:8px;">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <span>Mẹo: Bạn có thể tìm kiếm bạn bè mới từ trang <a href="home.php?page=timkiem" style="color:#667eea;text-decoration:none;font-weight:600;">Tìm kiếm</a></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        $user = $hoso->getProfile($uid);
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
                        $date_sent = date('d/m/Y', strtotime($msg['thoiGianGui'] ?? 'today'));

                        if ($date_sent !== $date) {
                            echo '<div class="date-separator" style="margin: 12px 0; text-align: center; color: #aaa;">';
                            echo $date_sent == $today ? 'Hôm nay' : ($date_sent == $yesterday ? 'Hôm qua' : $date_sent);
                            echo '</div>';
                            $date = $date_sent;
                        }

                        $isMe = $msg['maNguoiDung1'] == $_SESSION['uid'];
                        $senderId = $msg['maNguoiDung1'];
                        $messageId = $msg['maTinNhan'];
                        
                        echo '<div class="message-wrapper ' . ($isMe ? 'me' : 'other') . '" data-message-id="' . $messageId . '">';
                        echo '<div class="message ' . ($isMe ? 'sent' : 'received') . '" data-message-id="' . $messageId . '" data-sender-id="' . $senderId . '">';
                        echo '<div class="message-content">' . htmlspecialchars($msg['noiDungText']) . '</div>';
                        echo '<div class="message-time">' . date('H:i', strtotime($msg['thoiGianGui'] ?? 'now')) . '</div>';
                        echo '</div>';
                        echo '</div>';

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
                            if ($row['maNguoiDung1'] == $_SESSION['uid']) {
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

<script src="view/assets/js/message-report.js"></script>
<script>
     // Set user IDs and JWT token for JavaScript
        window.currentUserId = <?= isset($_SESSION['uid']) ? $_SESSION['uid'] : 'null' ?>;
        window.jwtToken = '<?= isset($_SESSION['jwt_token']) ? $_SESSION['jwt_token'] : '' ?>';
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