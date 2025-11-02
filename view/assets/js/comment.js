// Comment System - Clean & Working

function toggleCommentSection(maBaiDang) {
    const section = document.getElementById('commentSection-' + maBaiDang);
    if (!section) return;
    
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        loadComments(maBaiDang);
    } else {
        section.style.display = 'none';
    }
}

function loadComments(maBaiDang) {
    console.log('Loading comments for post:', maBaiDang);
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get', maBaiDang: maBaiDang})
    })
    .then(r => r.json())
    .then(data => {
        console.log('Load comments response:', data);
        if (data.success) {
            displayComments(maBaiDang, data.comments);
            // KHÔNG cập nhật số lượng khi load, giữ nguyên số trên UI
        } else {
            console.error('Failed to load comments:', data.message);
        }
    })
    .catch(e => console.error('Load error:', e));
}

function displayComments(maBaiDang, comments) {
    const list = document.getElementById('commentList-' + maBaiDang);
    if (!list) {
        console.error('Comment list element not found for post:', maBaiDang);
        return;
    }
    
    console.log('Displaying', comments ? comments.length : 0, 'comments for post:', maBaiDang);
    
    if (!comments || comments.length === 0) {
        list.innerHTML = '<p class="text-muted text-center py-3">Chưa có bình luận</p>';
        return;
    }
    
    let html = '';
    comments.forEach(c => {
        const avatar = c.anhDaiDien || 'img/default.png';
        const time = formatTime(c.thoiGianTao);
        html += `
            <div class="comment-item mb-3">
                <div class="d-flex">
                    <img src="${avatar}" alt="${escapeHtml(c.hoTen)}" class="rounded-circle me-2" width="40" height="40" onerror="this.src='img/default.png'">
                    <div class="flex-grow-1">
                        <div class="bg-light rounded p-2">
                            <strong>${escapeHtml(c.hoTen)}</strong>
                            <p class="mb-0">${escapeHtml(c.noiDung)}</p>
                        </div>
                        <small class="text-muted ms-2">${time}</small>
                    </div>
                </div>
            </div>
        `;
    });
    list.innerHTML = html;
    console.log('Comments displayed successfully');
}

function addComment(maBaiDang) {
    const input = document.getElementById('commentInput-' + maBaiDang);
    if (!input) return;
    
    const noiDung = input.value.trim();
    if (!noiDung) {
        alert('Nhập nội dung bình luận');
        return;
    }
    
    // Lấy số lượng comment hiện tại trước khi gửi
    const currentCountEl = document.getElementById('commentCount-' + maBaiDang);
    const currentCount = currentCountEl ? parseInt(currentCountEl.textContent) : 0;
    
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', maBaiDang: maBaiDang, noiDung: noiDung})
    })
    .then(r => r.json())
    .then(data => {
        console.log('Add comment response:', data);
        if (data.success) {
            input.value = '';
            // Hiển thị comments mới
            if (data.comments && data.comments.length > 0) {
                displayComments(maBaiDang, data.comments);
            } else {
                loadComments(maBaiDang);
            }
            // Cập nhật số lượng = số hiện tại + 1
            updateCommentCount(maBaiDang, currentCount + 1);
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(e => {
        console.error('Add comment error:', e);
        alert('Không kết nối được');
    });
}

function updateCommentCount(maBaiDang, count) {
    const el = document.getElementById('commentCount-' + maBaiDang);
    if (el) {
        el.textContent = count;
        console.log('Updated comment count for post', maBaiDang, 'to', count);
    } else {
        console.error('Comment count element not found for post:', maBaiDang);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(datetime) {
    const now = new Date();
    const past = new Date(datetime);
    const diff = Math.floor((now - past) / 1000);
    
    if (diff < 60) return 'Vừa xong';
    if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
    if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
    if (diff < 604800) return Math.floor(diff / 86400) + ' ngày trước';
    return past.toLocaleDateString('vi-VN');
}
