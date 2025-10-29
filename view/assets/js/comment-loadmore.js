// Comment system with pagination
const commentState = {}; // Lưu trạng thái comments của từng post

function initCommentState(postId) {
    if (!commentState[postId]) {
        commentState[postId] = {
            offset: 0,
            limit: 5,
            hasMore: false,
            totalCount: 0
        };
    }
}

function toggleCommentSection(postId) {
    console.log('[Toggle] Called for post:', postId);
    
    const section = document.getElementById(`commentSection-${postId}`);
    if (!section) {
        console.error('[Toggle] Section not found for post:', postId);
        return;
    }
    
    if (section.style.display === 'none') {
        console.log('[Toggle] Showing section, loading comments...');
        section.style.display = 'block';
        initCommentState(postId);
        loadComments(postId, true); // true = reset (load from beginning)
    } else {
        console.log('[Toggle] Hiding section');
        section.style.display = 'none';
    }
}

async function loadComments(postId, reset = false) {
    console.log('[Load] Loading comments for post:', postId, 'Reset:', reset);
    
    initCommentState(postId);
    
    if (reset) {
        commentState[postId].offset = 0;
    }
    
    try {
        const response = await fetch('api/comment-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get',
                maBaiDang: postId,
                limit: commentState[postId].limit,
                offset: commentState[postId].offset
            })
        });

        console.log('[Load] Response status:', response.status);
        const data = await response.json();
        console.log('[Load] Response data:', data);

        if (data.success) {
            if (reset) {
                displayComments(postId, data.comments);
            } else {
                appendComments(postId, data.comments);
            }
            
            // Update state
            commentState[postId].totalCount = data.totalCount || 0;
            commentState[postId].offset += data.comments.length;
            commentState[postId].hasMore = commentState[postId].offset < commentState[postId].totalCount;
            
            // Show/hide "Load more" button
            updateLoadMoreButton(postId);
            
            console.log('[Load] State updated:', commentState[postId]);
        }
    } catch (error) {
        console.error('[Load] Error:', error);
    }
}

function loadMoreComments(postId) {
    console.log('[LoadMore] Loading more comments for post:', postId);
    loadComments(postId, false); // false = append (don't reset)
}

function displayComments(postId, comments) {
    console.log('[Display] Displaying comments for post:', postId, 'Count:', comments.length);
    
    const commentList = document.getElementById(`commentList-${postId}`);
    if (!commentList) {
        console.error('[Display] Comment list not found');
        return;
    }
    
    if (comments.length === 0) {
        commentList.innerHTML = '<p class="text-muted text-center py-3">Chưa có bình luận</p>';
        return;
    }
    
    commentList.innerHTML = comments.map(comment => createCommentHTML(comment, postId)).join('');
}

function appendComments(postId, comments) {
    console.log('[Append] Appending comments for post:', postId, 'Count:', comments.length);
    
    const commentList = document.getElementById(`commentList-${postId}`);
    if (!commentList) {
        console.error('[Append] Comment list not found');
        return;
    }
    
    if (comments.length === 0) return;
    
    // Remove "no comments" message if exists
    const emptyMsg = commentList.querySelector('.text-muted');
    if (emptyMsg) {
        emptyMsg.remove();
    }
    
    // Append new comments
    const newCommentsHTML = comments.map(comment => createCommentHTML(comment, postId)).join('');
    commentList.insertAdjacentHTML('beforeend', newCommentsHTML);
}

function createCommentHTML(comment, postId) {
    const currentUserId = window.currentUserId || 0;
    
    // Get post owner from data attribute
    const section = document.getElementById(`commentSection-${postId}`);
    const postOwnerId = section ? parseInt(section.getAttribute('data-post-owner')) : 0;
    
    const isOwner = currentUserId == comment.maNguoiDung;
    const isPostOwner = currentUserId == postOwnerId;
    
    const canDelete = isOwner || isPostOwner;
    const deleteBtn = canDelete ? 
        `<button class="btn btn-sm btn-link text-danger p-0 ms-auto" onclick="deleteComment(${comment.maBinhLuan}, ${postId})" title="Gỡ bình luận">
            Gỡ bình luận
        </button>` : '';
    
    // Fix avatar path
    let avatarPath = comment.avatar || 'img/default.png';
    if (avatarPath === 'default.png') {
        avatarPath = 'img/default.png';
    } else if (avatarPath && !avatarPath.includes('/') && !avatarPath.startsWith('img/')) {
        avatarPath = 'uploads/avatars/' + avatarPath;
    }
    
    const userName = comment.hoTen || 'User';
    
    return `
        <div class="comment-item mb-3" id="comment-${comment.maBinhLuan}">
            <div class="d-flex align-items-start gap-2">
                <img src="${avatarPath}" alt="${userName}" class="comment-avatar rounded-circle" style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0;">
                <div class="comment-content flex-grow-1">
                    <div class="comment-bubble bg-light p-3 rounded-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="flex-grow-1">
                                <strong class="comment-author text-primary d-block">${userName}</strong>
                                <small class="comment-time text-muted">${formatCommentTime(comment.thoiGianTao)}</small>
                            </div>
                            ${deleteBtn}
                        </div>
                        <p class="comment-text mb-0 mt-2">${escapeHtml(comment.noiDung)}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function updateLoadMoreButton(postId) {
    const container = document.getElementById(`loadMoreContainer-${postId}`);
    if (!container) return;
    
    const state = commentState[postId];
    if (state.hasMore) {
        container.style.display = 'block';
        const btn = container.querySelector('button');
        if (btn) {
            const remaining = state.totalCount - state.offset;
            btn.innerHTML = `<i class="bi bi-arrow-down-circle"></i> Tải thêm (còn ${remaining} bình luận)`;
        }
    } else {
        container.style.display = 'none';
    }
}

async function addComment(postId) {
    console.log('[Add] Adding comment for post:', postId);
    
    const input = document.getElementById(`commentInput-${postId}`);
    const noiDung = input.value.trim();
    
    if (!noiDung) {
        console.log('[Add] Empty comment, aborting');
        return;
    }
    
    try {
        const response = await fetch('api/comment-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                maBaiDang: postId,
                noiDung: noiDung
            })
        });

        const data = await response.json();
        console.log('[Add] Response:', data);

        if (data.success) {
            input.value = '';
            
            // Reset and reload comments from beginning
            loadComments(postId, true);
            
            // Update comment count
            const currentCount = parseInt(document.getElementById(`commentCount-${postId}`)?.textContent || 0);
            updateCommentCount(postId, currentCount + 1);
        } else {
            alert(data.message || 'Không thể thêm bình luận');
        }
    } catch (error) {
        console.error('[Add] Error:', error);
        alert('Lỗi khi thêm bình luận');
    }
}

async function deleteComment(commentId, postId) {
    if (!confirm('Bạn có chắc muốn xóa bình luận này?')) {
        return;
    }
    
    try {
        const response = await fetch('api/comment-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                maBinhLuan: commentId,
                maBaiDang: postId
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Remove comment from DOM
            const commentElement = document.getElementById(`comment-${commentId}`);
            if (commentElement) {
                commentElement.remove();
            }
            
            // Update count
            const currentCount = parseInt(document.getElementById(`commentCount-${postId}`)?.textContent || 0);
            updateCommentCount(postId, Math.max(0, currentCount - 1));
            
            // Update state
            if (commentState[postId]) {
                commentState[postId].totalCount = Math.max(0, commentState[postId].totalCount - 1);
                commentState[postId].offset = Math.max(0, commentState[postId].offset - 1);
                updateLoadMoreButton(postId);
            }
        } else {
            alert(data.message || 'Không thể xóa bình luận');
        }
    } catch (error) {
        console.error('[Delete] Error:', error);
        alert('Lỗi khi xóa bình luận');
    }
}

function updateCommentCount(postId, count) {
    const countElement = document.getElementById(`commentCount-${postId}`);
    if (countElement) {
        countElement.textContent = count;
    }
}

function formatCommentTime(timeStr) {
    if (!timeStr) return '';
    
    const commentTime = new Date(timeStr);
    const now = new Date();
    const diffMs = now - commentTime;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Vừa xong';
    if (diffMins < 60) return `${diffMins} phút trước`;
    if (diffHours < 24) return `${diffHours} giờ trước`;
    if (diffDays < 7) return `${diffDays} ngày trước`;
    
    return commentTime.toLocaleDateString('vi-VN');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
