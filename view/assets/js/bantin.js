// Bantin page JavaScript - Post creation with multiple images and emoji picker

// Biến lưu trữ DataTransfer để quản lý files
let selectedFiles = new DataTransfer();

function resetModal() {
    // Reset form đúng cách
    const form = document.querySelector('#postModal form');
    if (form) form.reset();

    // Clear ảnh preview container
    document.getElementById("imagePreviewContainer").innerHTML = "";
    document.getElementById("imageInput").value = "";
    document.getElementById("addMoreInput").value = "";

    // Reset DataTransfer
    selectedFiles = new DataTransfer();

    // Ẩn emoji picker
    document.getElementById("emojiPickerContainer").style.display = "none";

    // Clear textarea
    const textarea = document.querySelector('textarea[name="newsContent"]');
    if (textarea) textarea.value = "";
}

function showFile(input) {
    const validImageTypes = ['image/jpeg', 'image/png'];
    const files = input.files;
    const container = document.getElementById("imagePreviewContainer");

    // Clear container trước
    container.innerHTML = "";

    // Reset DataTransfer
    selectedFiles = new DataTransfer();

    // Duyệt qua tất cả files được chọn
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        addFileToPreview(file);
    }

    // Thêm nút + để thêm ảnh mới
    addPlusButton();

    // Cập nhật input chính
    updateMainInput();
}

function addMoreFiles(input) {
    const files = input.files;

    // Thêm các file mới vào preview
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        addFileToPreview(file);
    }

    // Reset input thêm file
    input.value = "";

    // Thêm lại nút + (remove cái cũ trước)
    const oldPlusBtn = document.querySelector('.add-more-btn');
    if (oldPlusBtn) oldPlusBtn.remove();
    addPlusButton();

    // Cập nhật input chính
    updateMainInput();
}

function addFileToPreview(file) {
    const validImageTypes = ['image/jpeg', 'image/png'];
    const isValidType = file && validImageTypes.includes(file.type);
    const container = document.getElementById("imagePreviewContainer");

    // Thêm file vào DataTransfer (chỉ file hợp lệ để gửi lên server)
    if (isValidType) {
        selectedFiles.items.add(file);
    }

    // Tạo preview wrapper cho TẤT CẢ files
    const previewWrapper = document.createElement('div');
    previewWrapper.className = 'position-relative';
    previewWrapper.style.cssText = 'display: inline-block; margin: 4px;';

    if (isValidType) {
        // Hiển thị ảnh preview cho file hợp lệ
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.cssText = 'max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #22c55e;';
        img.alt = file.name;
        previewWrapper.appendChild(img);
    } else {
        // Hiển thị file name cho file không hợp lệ
        const fileBox = document.createElement('div');
        fileBox.style.cssText = 'width: 150px; height: 120px; border: 2px solid #ef4444; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fef2f2; color: #dc2626;';
        fileBox.innerHTML = `
            <i class="bi bi-file-earmark" style="font-size: 24px; margin-bottom: 4px;"></i>
            <span style="font-size: 10px; text-align: center; padding: 0 4px; word-break: break-all;">${file.name}</span>
            <span style="font-size: 9px; margin-top: 2px;">Không hỗ trợ</span>
        `;
        previewWrapper.appendChild(fileBox);
    }

    // Tạo nút xóa
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-danger btn-sm position-absolute';
    removeBtn.style.cssText = 'top: -8px; right: -8px; width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 1; z-index: 10;';
    removeBtn.innerHTML = '×';
    removeBtn.onclick = function () {
        // Xóa element khỏi DOM
        previewWrapper.remove();

        // Rebuild selectedFiles từ các ảnh còn lại
        rebuildFileList();
    };

    // Thêm vào wrapper
    previewWrapper.appendChild(removeBtn);
    container.appendChild(previewWrapper);
}

function addPlusButton() {
    const container = document.getElementById("imagePreviewContainer");

    // Tạo nút + để thêm ảnh
    const addBtn = document.createElement('div');
    addBtn.className = 'add-more-btn position-relative';
    addBtn.style.cssText = 'display: inline-block; margin: 4px; cursor: pointer;';

    const addBox = document.createElement('div');
    addBox.style.cssText = 'width: 150px; height: 150px; border: 2px dashed #94a3b8; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; color: #64748b; transition: all 0.2s;';
    addBox.innerHTML = `
        <i class="bi bi-plus-lg" style="font-size: 32px; margin-bottom: 8px;"></i>
        <span style="font-size: 12px; font-weight: 600;">Thêm ảnh</span>
    `;

    // Hover effect
    addBox.onmouseover = function () {
        this.style.borderColor = '#667eea';
        this.style.backgroundColor = '#f0f4ff';
        this.style.color = '#667eea';
    };
    addBox.onmouseout = function () {
        this.style.borderColor = '#94a3b8';
        this.style.backgroundColor = '#f8fafc';
        this.style.color = '#64748b';
    };

    // Click để mở file picker
    addBox.onclick = function () {
        document.getElementById('addMoreInput').click();
    };

    addBtn.appendChild(addBox);
    container.appendChild(addBtn);
}

function updateMainInput() {
    // Cập nhật input chính với files từ selectedFiles
    const mainInput = document.getElementById("imageInput");
    mainInput.files = selectedFiles.files;
}

function rebuildFileList() {
    // Rebuild selectedFiles từ DOM
    selectedFiles = new DataTransfer();
    const images = document.querySelectorAll('#imagePreviewContainer img');
    const input = document.getElementById("imageInput");

    // Từ các ảnh còn lại trong DOM, tìm file tương ứng
    const originalFiles = Array.from(input.files);

    images.forEach(img => {
        const altName = img.alt;
        const matchingFile = originalFiles.find(file => file.name === altName);
        if (matchingFile) {
            selectedFiles.items.add(matchingFile);
        }
    });

    // Cập nhật input
    input.files = selectedFiles.files;
}

// HÀM TOGGLE EMOJI PICKER - ĐƠN GIẢN
function toggleEmojiPicker() {
    const emojiContainer = document.getElementById("emojiPickerContainer");

    // Nếu đang ẩn thì hiển thị, nếu đang hiển thị thì ẩn
    if (emojiContainer.style.display === "none" || emojiContainer.style.display === "") {
        emojiContainer.style.display = "block";
    } else {
        emojiContainer.style.display = "none";
    }
}

// HÀM XỬ LÝ KHI CHỌN EMOJI - ĐƠN GIẢN
function handleEmojiPicker() {
    const emojiPicker = document.querySelector('emoji-picker');
    const textarea = document.querySelector('textarea[name="newsContent"]');

    // Kiểm tra cả 2 element có tồn tại không
    if (!emojiPicker || !textarea) {
        return;
    }

    // Lắng nghe sự kiện click emoji
    emojiPicker.addEventListener('emoji-click', function (event) {
        // Lấy emoji được chọn
        const selectedEmoji = event.detail.unicode;

        // Lấy vị trí con trỏ hiện tại trong textarea
        const cursorPosition = textarea.selectionStart;

        // Lấy text trước và sau vị trí con trỏ
        const textBefore = textarea.value.substring(0, cursorPosition);
        const textAfter = textarea.value.substring(cursorPosition);

        // Chèn emoji vào vị trí con trỏ
        textarea.value = textBefore + selectedEmoji + textAfter;

        // Đặt con trỏ sau emoji vừa chèn
        const newPosition = cursorPosition + selectedEmoji.length;
        textarea.selectionStart = newPosition;
        textarea.selectionEnd = newPosition;

        // Focus lại textarea
        textarea.focus();
    });
}

// CHẠY KHI TRANG ĐÃ TẢI XONG
document.addEventListener('DOMContentLoaded', function () {
    // Khởi tạo emoji picker
    handleEmojiPicker();
});

// ============================================
// HÀM XỬ LÝ LIKE BÀI ĐĂNG
// ============================================
function toggleLikePost(maBaiDang, button) {
    // Kiểm tra đăng nhập
    if (!window.currentUserId) {
        alert('Vui lòng đăng nhập để thích bài viết này');
        return;
    }

    console.log('toggleLikePost called for post:', maBaiDang);
    
    const icon = button.querySelector('i');
    const isLiked = icon.classList.contains('bi-heart-fill');
    
    // Toggle icon ngay lập tức để UX mượt
    if (isLiked) {
        icon.classList.remove('bi-heart-fill');
        icon.classList.add('bi-heart');
        button.classList.remove('liked');
    } else {
        icon.classList.remove('bi-heart');
        icon.classList.add('bi-heart-fill');
        button.classList.add('liked');
    }
    
    console.log('Sending request to:', 'api/like-post.php');
    console.log('Request body:', { maBaiDang: maBaiDang });
    
    // Gọi API
    fetch('api/like-post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            maBaiDang: maBaiDang
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Cập nhật số lượt thích
            const likeCountElement = document.getElementById('likeCount-' + maBaiDang);
            if (likeCountElement) {
                likeCountElement.textContent = data.likeCount;
            }
            
            // Đảm bảo trạng thái icon đúng với server
            if (data.isLiked) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                button.classList.add('liked');
            } else {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                button.classList.remove('liked');
            }
        } else {
            // Revert nếu có lỗi
            if (isLiked) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                button.classList.add('liked');
            } else {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                button.classList.remove('liked');
            }
            alert('Có lỗi xảy ra: ' + (data.message || 'Không thể thích bài viết'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        // Revert nếu có lỗi
        if (isLiked) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            button.classList.add('liked');
        } else {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            button.classList.remove('liked');
        }
        alert('Không thể kết nối đến server');
    });
}

// ============= COMMENT FUNCTIONS =============

// Toggle hiển thị form comment và danh sách comment
function toggleCommentSection(maBaiDang) {
    const commentSection = document.getElementById('commentSection-' + maBaiDang);
    
    if (!commentSection) {
        console.error('Comment section not found for post:', maBaiDang);
        return;
    }
    
    // Toggle display
    if (commentSection.style.display === 'none' || commentSection.style.display === '') {
        commentSection.style.display = 'block';
        // Load comments ngay khi mở - QUAN TRỌNG!
        console.log('Opening comment section, loading comments for post:', maBaiDang);
        loadComments(maBaiDang);
    } else {
        commentSection.style.display = 'none';
    }
}

// Load danh sách comment
function loadComments(maBaiDang) {
    console.log('Loading comments for post:', maBaiDang);
    
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'get',
            maBaiDang: maBaiDang
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Comments loaded:', data);
        if (data.success) {
            displayComments(maBaiDang, data.comments);
            // Cập nhật số lượng bình luận luôn
            updateCommentCount(maBaiDang, data.commentCount || data.comments.length);
        } else {
            console.error('Failed to load comments:', data.message);
            // Vẫn hiển thị placeholder
            const commentList = document.getElementById('commentList-' + maBaiDang);
            if (commentList) {
                commentList.innerHTML = '<p class="text-muted text-center py-3">Không thể tải bình luận</p>';
            }
        }
    })
    .catch(error => {
        console.error('Error loading comments:', error);
        const commentList = document.getElementById('commentList-' + maBaiDang);
        if (commentList) {
            commentList.innerHTML = '<p class="text-danger text-center py-3">Lỗi kết nối: ' + error.message + '</p>';
        }
    });
}

// Hiển thị danh sách comment
function displayComments(maBaiDang, comments) {
    const commentList = document.getElementById('commentList-' + maBaiDang);
    
    if (!commentList) {
        console.error('Comment list not found for post:', maBaiDang);
        return;
    }
    
    console.log('Displaying comments for post:', maBaiDang, 'Total:', comments ? comments.length : 0);
    
    if (!comments || comments.length === 0) {
        commentList.innerHTML = '<p class="text-muted text-center py-3">Chưa có bình luận nào</p>';
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        const avatar = comment.anhDaiDien || 'img/default.png';
        const timeAgo = getTimeAgo(comment.thoiGianTao);
        const isOwner = comment.maNguoiDung == currentUserId; // Cần set biến currentUserId trong view
        
        html += `
            <div class="comment-item mb-3" data-comment-id="${comment.maBinhLuan}">
                <div class="d-flex">
                    <img src="${avatar}" alt="${comment.hoTen}" class="rounded-circle me-2" width="40" height="40">
                    <div class="flex-grow-1">
                        <div class="bg-light rounded p-2">
                            <strong>${comment.hoTen}</strong>
                            <p class="mb-0">${escapeHtml(comment.noiDung)}</p>
                        </div>
                        <small class="text-muted ms-2">${timeAgo}</small>
                        ${isOwner ? `<a href="#" class="text-danger ms-2 small" onclick="deleteComment(${comment.maBinhLuan}, ${maBaiDang}); return false;">Xóa</a>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    commentList.innerHTML = html;
    console.log('Comments displayed successfully');
}

// Thêm comment mới
function addComment(maBaiDang) {
    const textarea = document.getElementById('commentInput-' + maBaiDang);
    
    if (!textarea) {
        console.error('Comment input not found for post:', maBaiDang);
        return;
    }
    
    const noiDung = textarea.value.trim();
    
    if (noiDung === '') {
        alert('Vui lòng nhập nội dung bình luận');
        return;
    }
    
    console.log('Adding comment to post:', maBaiDang, 'Content:', noiDung);
    
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'add',
            maBaiDang: maBaiDang,
            noiDung: noiDung
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Add comment response:', data);
        if (data.success) {
            // Clear textarea
            textarea.value = '';
            
            // LUÔN reload comments từ server để đảm bảo hiển thị đầy đủ
            loadComments(maBaiDang);
            
            // Cập nhật số lượng bình luận NGAY LẬP TỨC
            if (data.commentCount !== undefined) {
                updateCommentCount(maBaiDang, data.commentCount);
            }
            
            console.log('Comment added successfully!');
        } else {
            alert('Có lỗi xảy ra: ' + (data.message || 'Không thể thêm bình luận'));
            console.error('Failed to add comment:', data);
        }
    })
    .catch(error => {
        console.error('Error adding comment:', error);
        alert('Không thể kết nối đến server: ' + error.message);
    });
}

// Xóa comment
function deleteComment(maBinhLuan, maBaiDang) {
    if (!confirm('Bạn có chắc muốn xóa bình luận này?')) {
        return;
    }
    
    console.log('Deleting comment:', maBinhLuan);
    
    fetch('api/comment-post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'delete',
            maBaiDang: maBaiDang,
            maBinhLuan: maBinhLuan
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete comment response:', data);
        if (data.success) {
            // Reload comments
            loadComments(maBaiDang);
            
            // Update comment count - giảm 1
            const commentCountElement = document.getElementById('commentCount-' + maBaiDang);
            if (commentCountElement) {
                const currentCount = parseInt(commentCountElement.textContent) || 0;
                updateCommentCount(maBaiDang, Math.max(0, currentCount - 1));
            }
        } else {
            alert('Có lỗi xảy ra: ' + (data.message || 'Không thể xóa bình luận'));
        }
    })
    .catch(error => {
        console.error('Error deleting comment:', error);
        alert('Không thể kết nối đến server');
    });
}

// Utility function - Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Utility function - Update comment count
function updateCommentCount(maBaiDang, newCount) {
    const commentCountElement = document.getElementById('commentCount-' + maBaiDang);
    if (commentCountElement) {
        commentCountElement.textContent = newCount;
        console.log('Updated comment count for post', maBaiDang, 'to', newCount);
    } else {
        console.warn('Comment count element not found for post:', maBaiDang);
    }
}

// Utility function - Time ago
function getTimeAgo(datetime) {
    const now = new Date();
    const past = new Date(datetime);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffMins < 1) return 'Vừa xong';
    if (diffMins < 60) return diffMins + ' phút trước';
    if (diffHours < 24) return diffHours + ' giờ trước';
    if (diffDays < 7) return diffDays + ' ngày trước';
    
    return past.toLocaleDateString('vi-VN');
}