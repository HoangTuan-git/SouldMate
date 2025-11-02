/**
 * Message Report Feature - Context Menu
 * Hỗ trợ báo cáo tin nhắn cụ thể bằng:
 * - Right-click (Desktop)
 * - Long-press (Mobile)
 */

document.addEventListener('DOMContentLoaded', function() {
    initMessageReportFeature();
});

function initMessageReportFeature() {
    const messagesContainer = document.querySelector('.messages-container');
    if (!messagesContainer) return;

    let longPressTimer;
    let targetMessage = null;

    // ===== DESKTOP: Right-click context menu =====
    messagesContainer.addEventListener('contextmenu', function(e) {
        const messageElement = e.target.closest('.message.received');
        if (!messageElement) return;

        e.preventDefault(); // Ngăn menu context mặc định
        showMessageOptions(messageElement, e.clientX, e.clientY);
    });

    // ===== MOBILE: Long-press =====
    messagesContainer.addEventListener('touchstart', function(e) {
        const messageElement = e.target.closest('.message.received');
        if (!messageElement) return;

        targetMessage = messageElement;
        messageElement.classList.add('highlight');
        
        longPressTimer = setTimeout(function() {
            const touch = e.touches[0];
            showMessageOptions(messageElement, touch.clientX, touch.clientY);
        }, 500); // Long press 500ms
    });

    messagesContainer.addEventListener('touchend', function(e) {
        clearTimeout(longPressTimer);
        if (targetMessage) {
            targetMessage.classList.remove('highlight');
            targetMessage = null;
        }
    });

    messagesContainer.addEventListener('touchmove', function(e) {
        clearTimeout(longPressTimer);
        if (targetMessage) {
            targetMessage.classList.remove('highlight');
            targetMessage = null;
        }
    });
}

function showMessageOptions(messageElement, x, y) {
    // Xóa menu cũ nếu có
    const oldMenu = document.querySelector('.message-context-menu');
    if (oldMenu) oldMenu.remove();

    // Lấy thông tin tin nhắn
    const maTinNhan = messageElement.getAttribute('data-message-id');
    const maNguoiGui = messageElement.getAttribute('data-sender-id');
    const noiDung = messageElement.querySelector('.message-content').textContent;

    if (!maTinNhan || !maNguoiGui) {
        console.warn('Message ID or Sender ID not found');
        return;
    }

    // Tạo context menu
    const menu = document.createElement('div');
    menu.className = 'message-context-menu';
    menu.style.cssText = `
        position: fixed;
        left: ${x}px;
        top: ${y}px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        min-width: 200px;
        overflow: hidden;
    `;

    menu.innerHTML = `
        <div class="context-menu-item" data-action="report">
            <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;">
                <path d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3s.58-1.3 1.3-1.3 1.3.58 1.3 1.3-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
            </svg>
            <span style="color:#dc2626;font-weight:500;">Báo cáo tin nhắn</span>
        </div>
    `;

    document.body.appendChild(menu);

    // Đảm bảo menu không ra ngoài màn hình
    const rect = menu.getBoundingClientRect();
    if (rect.right > window.innerWidth) {
        menu.style.left = (x - rect.width) + 'px';
    }
    if (rect.bottom > window.innerHeight) {
        menu.style.top = (y - rect.height) + 'px';
    }

    // Xử lý click vào menu item
    const menuItem = menu.querySelector('[data-action="report"]');
    menuItem.addEventListener('click', function() {
        reportThisMessage(maTinNhan, maNguoiGui, noiDung);
        menu.remove();
    });

    // Đóng menu khi click ra ngoài
    setTimeout(() => {
        document.addEventListener('click', closeContextMenu);
        document.addEventListener('touchstart', closeContextMenu);
    }, 100);

    function closeContextMenu(e) {
        if (!menu.contains(e.target)) {
            menu.remove();
            document.removeEventListener('click', closeContextMenu);
            document.removeEventListener('touchstart', closeContextMenu);
        }
    }
}

function reportThisMessage(maTinNhan, maNguoiGui, noiDung) {
    // Hiển thị prompt lý do
    const reason = prompt('Vui lòng nhập lý do báo cáo tin nhắn này:');
    if (reason === null) return; // User cancelled

    if (reason && reason.trim()) {
        submitMessageReport(maTinNhan, maNguoiGui, noiDung, reason.trim());
    } else {
        alert('Vui lòng nhập lý do báo cáo');
    }
}

function submitMessageReport(maTinNhan, maNguoiGui, noiDung, reason) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'home.php?page=report-message';

    const inputs = {
        'uid': maNguoiGui,
        'messageId': maTinNhan,
        'messageContent': noiDung,
        'reason': reason
    };

    Object.keys(inputs).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = inputs[key];
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/'/g, "\\'");
}
