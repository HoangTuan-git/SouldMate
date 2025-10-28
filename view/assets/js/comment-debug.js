// Comment System - Debug Version

function toggleCommentSection(maBaiDang) {
    console.log('[Toggle] Called for post:', maBaiDang);
    const section = document.getElementById('commentSection-' + maBaiDang);
    if (!section) {
        console.error('[Toggle] Section not found for post:', maBaiDang);
        return;
    }
    
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        console.log('[Toggle] Showing section, loading comments...');
        loadComments(maBaiDang);
    } else {
        section.style.display = 'none';
        console.log('[Toggle] Hiding section');
    }
}

function loadComments(maBaiDang) {
    console.log('[Load] Loading comments for post:', maBaiDang);
    
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get', maBaiDang: maBaiDang})
    })
    .then(r => {
        console.log('[Load] Response status:', r.status);
        return r.json();
    })
    .then(data => {
        console.log('[Load] Response data:', data);
        console.log('[Load] Comments array:', data.comments);
        console.log('[Load] Comments count:', data.comments ? data.comments.length : 0);
        
        if (data.success) {
            displayComments(maBaiDang, data.comments);
        } else {
            console.error('[Load] Failed:', data.message);
        }
    })
    .catch(e => {
        console.error('[Load] Error:', e);
    });
}

function displayComments(maBaiDang, comments) {
    console.log('[Display] Called for post:', maBaiDang);
    console.log('[Display] Comments to display:', comments);
    
    const list = document.getElementById('commentList-' + maBaiDang);
    if (!list) {
        console.error('[Display] List element not found for post:', maBaiDang);
        return;
    }
    
    console.log('[Display] List element found:', list);
    
    if (!comments || comments.length === 0) {
        console.log('[Display] No comments, showing empty message');
        list.innerHTML = '<p class="text-muted text-center py-3">Chưa có bình luận</p>';
        return;
    }
    
    console.log('[Display] Rendering', comments.length, 'comments');
    
    let html = '';
    comments.forEach((c, index) => {
        console.log('[Display] Comment', index, ':', c);
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
    console.log('[Display] HTML set, length:', html.length);
}

function addComment(maBaiDang) {
    console.log('[Add] Called for post:', maBaiDang);
    const input = document.getElementById('commentInput-' + maBaiDang);
    if (!input) {
        console.error('[Add] Input not found');
        return;
    }
    
    const noiDung = input.value.trim();
    if (!noiDung) {
        alert('Nhập nội dung bình luận');
        return;
    }
    
    console.log('[Add] Content:', noiDung);
    
    const currentCountEl = document.getElementById('commentCount-' + maBaiDang);
    const currentCount = currentCountEl ? parseInt(currentCountEl.textContent) : 0;
    console.log('[Add] Current count:', currentCount);
    
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', maBaiDang: maBaiDang, noiDung: noiDung})
    })
    .then(r => r.json())
    .then(data => {
        console.log('[Add] Response:', data);
        if (data.success) {
            input.value = '';
            if (data.comments && data.comments.length > 0) {
                console.log('[Add] Displaying new comments');
                displayComments(maBaiDang, data.comments);
            } else {
                console.log('[Add] Reloading comments');
                loadComments(maBaiDang);
            }
            updateCommentCount(maBaiDang, currentCount + 1);
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(e => {
        console.error('[Add] Error:', e);
        alert('Không kết nối được');
    });
}

function updateCommentCount(maBaiDang, count) {
    console.log('[Update] Setting count for post', maBaiDang, 'to', count);
    const el = document.getElementById('commentCount-' + maBaiDang);
    if (el) {
        el.textContent = count;
        console.log('[Update] Count updated');
    } else {
        console.error('[Update] Count element not found');
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

console.log('[Comment.js] Loaded successfully');
