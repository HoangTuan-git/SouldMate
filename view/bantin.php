<?php
// ==============================
// XỬ LÝ ĐĂNG BẢN TIN MỚI (submit form modal)
// ==============================
include_once("controller/cBanTin.php");
$cBanTin = new cBanTin();

// ==============================
// XỬ LÝ XÓA BÀI VIẾT
// ==============================
if (isset($_POST['deletePost']) && isset($_POST['postId'])) {
    if (!isset($_SESSION['uid'])) {
        echo '<script>alert("Bạn cần đăng nhập để thực hiện thao tác này!")</script>';
    } else {
        $postId = intval($_POST['postId']);
        $result = $cBanTin->cDeleteTinTuc($postId, $_SESSION['uid']);
        
        if ($result) {
            echo '<script>alert("Xóa bài viết thành công!")</script>';
            header("Location: home.php?page=bantin");
            exit();
        } else {
            echo '<script>alert("Không thể xóa bài viết. Bạn không có quyền hoặc bài viết không tồn tại!")</script>';
        }
    }
}

if (isset($_POST['postNews'])) {
    // Gửi bản tin mới
    $p = $cBanTin->cAddTinTuc(
        $_SESSION['uid'],
        $_POST['newsContent'],
        $_FILES['newsImages'],
        $_POST['privacy']
    );
    switch ($p) {
        case '1':
            echo '<script>alert("Lỗi: Bạn phải nhập nội dung hoặc chọn file để đăng!")</script>';
            break;
        case '2':
            echo '<script>alert("Lỗi: Kích thước ảnh quá lớn (tối đa 2MB)!")</script>';
            break;
        case '3':
            echo '<script>alert("Lỗi: Định dạng ảnh không được hỗ trợ (chỉ chấp nhận PNG/JPEG)!")</script>';
            break;
        case '4':
            echo '<script>alert("Lỗi: Ảnh không phù hợp!")</script>';
            break;
        case '5':
            echo '<script>alert("Đăng bản tin thành công!")</script>';
            // Redirect chuẩn để tránh trùng đăng khi reload
            header("Location: home.php?page=bantin");
            exit();
        default:
            echo '<script>alert("Đã xảy ra lỗi không xác định!")</script>';
            break;
    }
}

// ==============================
// HÀM XỬ LÝ HIỂN THỊ THỜI GIAN (ví dụ: 2 phút trước)
// ==============================
function timeAgo($datetime) {
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 0) return 'Vừa xong';
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
    if ($diff < 2592000) return floor($diff / 604800) . ' tuần trước';
    if ($diff < 31536000) return floor($diff / 2592000) . ' tháng trước';
    return floor($diff / 31536000) . ' năm trước';
}
?>
<!-- Thư viện Emoji Picker (dùng cho chọn emoji khi đăng bài) -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<div class="main-container mt-2">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <!-- Khu vực đăng bài mới (ô nhập trên cùng, click sẽ mở modal) -->
            <div class="feed-container mx-auto ">
                <div class="post-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <?php
                            // Lấy thông tin hồ sơ người dùng để hiển thị avatar và tên
                            include_once("controller/cHoSo.php");
                            $cHoSo = new controlHoSo();
                            $checkProfile = isset($_SESSION['uid']) ? $cHoSo->checkHoSoExists($_SESSION['uid']) : false;

                            if (isset($_SESSION['uid']) && $checkProfile):
                                // Lấy avatar từ DB
                                $avatar = 'img/default.png';
                                $profileResult = $cHoSo->getProfile($_SESSION['uid']);
                                if ($profileResult && $profileResult->num_rows > 0) {
                                    $profile = $profileResult->fetch_assoc();
                                    if (!empty($profile['avatar']) && $profile['avatar'] !== 'default.png' && file_exists(__DIR__ . '/../uploads/avatars/' . $profile['avatar'])) {
                                        $avatar = 'uploads/avatars/' . $profile['avatar'];
                                    }
                                }
                                $src = $avatar;
                            ?>
                                <!-- Ô nhập bài viết, hiển thị avatar và tên, click sẽ mở modal đăng bài -->
                                <img src="<?= htmlspecialchars($src) ?>" alt="Avatar" class="avatar-circle me-3">
                                <div class="post-input flex-fill" role="button" onclick="openPostModalAndFocus()">
                                    <?= htmlspecialchars($_SESSION['email']) ?> ơi, bạn đang nghĩ gì thế?
                                </div>
                            <?php else: ?>
                                <!-- Nếu chưa đăng nhập, hiển thị thông báo -->
                                <div class="post-input flex-fill text-center" onclick="window.location.href='home_test.php?page=dangnhap'" role="button">
                                    Đăng nhập và tạo hồ sơ để đăng bài tương tác
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($_SESSION['uid'])): ?>
                        <!-- Nút mở modal đăng bài: Ảnh, Emoji -->
                            <hr class="my-3">
                            <div class="row text-center g-2" style="font-size: 14px;" onclick="resetModal()">
                                <div class="col-6">
                                    <button class="btn btn-light w-100 py-2" data-bs-toggle="modal" data-bs-target="#postModal">
                                        <i class="bi bi-image text-success me-2"></i>
                                        <span>Ảnh</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-light w-100 py-2" data-bs-toggle="modal" data-bs-target="#postModal">
                                        <i class="bi bi-emoji-smile text-warning me-2"></i>
                                        <span>Emoji</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Danh sách bản tin -->
                <!-- Script: Khi click ô nhập trên cùng sẽ mở modal và focus vào textarea -->
                <script>
                function openPostModalAndFocus() {
                    var modal = new bootstrap.Modal(document.getElementById('postModal'));
                    modal.show();
                    setTimeout(function() {
                        var textarea = document.querySelector('#postModal textarea[name="newsContent"]');
                        if (textarea) textarea.focus();
                    }, 300);
                }
                </script>
                <?php
                // Lấy danh sách tất cả bản tin
                $posts = $cBanTin->cGetAllTinTuc();

                // Controller kiểm tra trạng thái like
                include_once("controller/cTuongTac.php");
                $cTuongTac = new cTuongTac();

                if ($posts && $posts->num_rows > 0):
                    while ($post = $posts->fetch_assoc()):
                        // Xử lý ảnh bài viết
                        $images = !empty($post['noiDungAnh']) ? explode(',', $post['noiDungAnh']) : [];
                        $imageCount = count($images);

                        // Xác định class cho layout ảnh
                        $gridClass = 'images-1';
                        if ($imageCount == 2) $gridClass = 'images-2';
                        elseif ($imageCount == 3) $gridClass = 'images-3';
                        elseif ($imageCount == 4) $gridClass = 'images-4';
                        elseif ($imageCount >= 5) $gridClass = 'images-5-plus';

                        // Kiểm tra người dùng đã like bài này chưa
                        $userLiked = false;
                        if (isset($_SESSION['uid'])) {
                            $userLiked = $cTuongTac->CheckLikeStatus($_SESSION['uid'], $post['maBaiDang']);
                        }
                ?>


                            <div class="feed-post-card">
                                <!-- ===== Header bài viết: avatar, tên, thời gian ===== -->
                                <div class="feed-post-header">
                                    <div class="feed-post-avatar-wrapper">
                                        <?php
                                        // Lấy avatar của người đăng bài
                                        $avatarPath = 'img/default.png';
                                        if (!empty($post['avatar']) && $post['avatar'] !== 'default.png' && file_exists(__DIR__ . '/../uploads/avatars/' . $post['avatar'])) {
                                            $avatarPath = 'uploads/avatars/' . $post['avatar'];
                                        }
                                        ?>
                                        <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" class="feed-post-avatar verified" onerror="this.src='img/default.png'">
                                        <div class="feed-post-user-info">
                                            <h6 class="feed-post-username"><?php echo htmlspecialchars($post['hoTen']); ?></h6>
                                            <p class="feed-post-time"><?php echo timeAgo($post['ngayTao']); ?></p>
                                        </div>
                                    </div>
                                    <div class="feed-post-menu-container">
                                        <button class="feed-post-menu-btn" onclick="togglePostMenu(<?= $post['maBaiDang'] ?>)">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <div class="feed-post-dropdown" id="postMenu-<?= $post['maBaiDang'] ?>" style="display: none;">
                                            <?php if ($post['maNguoiDung'] == $_SESSION['uid']): ?>
                                                <button class="dropdown-item text-danger" onclick="deletePost(<?= $post['maBaiDang'] ?>)">
                                                    <i class="bi bi-trash"></i> Xóa bài viết
                                                </button>
                                            <?php else: ?>
                                                <button class="dropdown-item text-danger" onclick="reportPost(<?= $post['maBaiDang'] ?>, <?= $post['maNguoiDung'] ?>)">
                                                    <i class="bi bi-exclamation-triangle"></i> Báo cáo bài viết
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- ===== Nội dung bài viết ===== -->
                                <div class="feed-post-content">
                                    <?php if (!empty($post['noiDungText'])): ?>
                                        <p class="feed-post-text"><?php echo nl2br(htmlspecialchars($post['noiDungText'])); ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- ===== Hình ảnh bài viết (nếu có) ===== -->
                                <?php if ($imageCount > 0): ?>
                                    <div class="feed-post-images <?php echo $gridClass; ?>">
                                        <?php
                                        $displayImages = array_slice($images, 0, 5); // Hiển thị tối đa 5 ảnh
                                        foreach ($displayImages as $index => $image):
                                            if (empty(trim($image))) continue;
                                            $isLast = ($index === 4 && $imageCount > 5);
                                        ?>
                                            <div class="feed-post-image-item">
                                                <img src="img/<?php echo trim($image); ?>" alt="Post image" loading="lazy">
                                                <?php if ($isLast): ?>
                                                    <div class="feed-post-image-overlay">
                                                        +<?php echo $imageCount - 5; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- ===== Thống kê like & bình luận ===== -->
                                <div class="feed-post-stats">
                                    <div class="feed-post-stats-inner">
                                        <div class="feed-post-reactions">
                                            <i class="bi bi-heart-fill feed-post-reaction-icon text-danger"></i>
                                            <span class="feed-post-reaction-count" id="likeCount-<?php echo $post['maBaiDang']; ?>"><?php echo $post['soLuotThich'] ?? 0; ?></span>
                                        </div>
                                        <div>
                                            <span class="feed-post-comment-count" id="commentCount-<?php echo $post['maBaiDang']; ?>">
                                                <?php echo $post['soBinhLuan'] ?? 0; ?>
                                            </span> bình luận
                                        </div>
                                    </div>
                                </div>

                                <!-- ===== Các nút thao tác: Like, Bình luận, Chia sẻ ===== -->
                                <div class="feed-post-actions">
                                    <button class="feed-post-action-btn <?php echo $userLiked ? 'liked' : ''; ?>" onclick="toggleLikePost(<?php echo $post['maBaiDang']; ?>, this)">
                                        <i class="bi bi-heart<?php echo $userLiked ? '-fill' : ''; ?>"></i>
                                        <span>Thích</span>
                                    </button>
                                    <button class="feed-post-action-btn" onclick="toggleCommentSection(<?php echo $post['maBaiDang']; ?>)">
                                        <i class="bi bi-chat"></i>
                                        <span>Bình luận</span>
                                    </button>
                                    <button class="feed-post-action-btn">
                                        <i class="bi bi-share"></i>
                                        <span>Chia sẻ</span>
                                    </button>
                                </div>

                                <!-- ===== Khu vực bình luận ===== -->
                                <div id="commentSection-<?php echo $post['maBaiDang']; ?>" class="comment-section" data-post-owner="<?php echo $post['maNguoiDung']; ?>" style="display: none;">
                                    <div class="comment-form mb-3">
                                        <div class="d-flex gap-2">
                                            <!-- Ô nhập bình luận + nút gửi -->
                                            <div class="flex-grow-1">
                                                <textarea 
                                                    id="commentInput-<?php echo $post['maBaiDang']; ?>" 
                                                    class="form-control" 
                                                    rows="2" 
                                                    placeholder="Viết bình luận..."
                                                    onkeypress="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); addComment(<?php echo $post['maBaiDang']; ?>); }"
                                                ></textarea>
                                            </div>
                                            <button class="send-comment-btn align-self-end" onclick="addComment(<?php echo $post['maBaiDang']; ?>)" style="border: none; background: #1a355b; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                                                <i class="bi bi-send" style="color: #fff; font-size: 1.2rem;"></i>
                                            </button>
                                            <style>
                                            .send-comment-btn:hover {
                                                background: #274a7d;
                                                box-shadow: 0 2px 8px rgba(26,53,91,0.15);
                                            }
                                            </style>
                                        </div>
                                    </div>
                                    <div id="commentList-<?php echo $post['maBaiDang']; ?>" class="comment-list">
                                        <!-- Danh sách bình luận sẽ được load bằng JS -->
                                    </div>
                                    <div id="loadMoreContainer-<?php echo $post['maBaiDang']; ?>" class="text-center mt-2" style="display: none;">
                                        <button class="btn btn-sm btn-outline-primary" onclick="loadMoreComments(<?php echo $post['maBaiDang']; ?>)">
                                            <i class="bi bi-arrow-down-circle"></i> Tải thêm bình luận
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endwhile;
                    else:
                    ?>
                        <div class="feed-empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Chưa có bài viết nào</p>
                        </div>
                    <?php endif; ?>

<!-- Modal Đăng Bản Tin-->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đăng Bản Tin -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="postModalLabel">Tạo bản tin mới</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <textarea name="newsContent" class="form-control border-0 fs-5" style="resize: none; min-height: 120px;" placeholder="Bạn đang nghĩ gì?"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Hình ảnh</label>
                <input type="file" name="newsImages[]" class="form-control" multiple>
              </div>
              <div class="mb-3">
                <label class="form-label">Quyền riêng tư</label>
                <select name="privacy" class="form-select">
                  <option value="public">🌍 Công khai</option>
                  <option value="friends">👫 Bạn bè</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" name="postNews" class="btn btn-gradient btn-lg rounded-pill">
                <i class="bi bi-send me-2"></i>Đăng bản tin
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Kết thúc modal đăng bản tin -->

    <style>
    /* Post Menu Dropdown Styles */
    .feed-post-menu-container {
        position: relative;
    }

    .feed-post-menu-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .feed-post-menu-btn:hover {
        background: #f1f5f9;
    }

    .feed-post-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        min-width: 200px;
        z-index: 1000;
        overflow: hidden;
        margin-top: 4px;
    }

    .feed-post-dropdown .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 12px 16px;
        background: none;
        border: none;
        text-align: left;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 14px;
        color: #475569;
    }

    .feed-post-dropdown .dropdown-item:hover {
        background: #f8fafc;
    }

    .feed-post-dropdown .dropdown-item.text-danger {
        color: #dc2626;
    }

    .feed-post-dropdown .dropdown-item.text-danger:hover {
        background: #fef2f2;
    }

    .feed-post-dropdown .dropdown-item i {
        font-size: 16px;
    }
    </style>

    <script>
    // Toggle post menu dropdown
    function togglePostMenu(postId) {
        const menu = document.getElementById('postMenu-' + postId);
        const allMenus = document.querySelectorAll('.feed-post-dropdown');
        
        // Close all other menus
        allMenus.forEach(m => {
            if (m !== menu) m.style.display = 'none';
        });
        
        // Toggle current menu
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }

    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.feed-post-menu-container')) {
            document.querySelectorAll('.feed-post-dropdown').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });

    // Report post function
    function reportPost(postId, postOwnerId) {
        const reason = prompt('Vui lòng nhập lý do báo cáo bài viết này:');
        if (reason && reason.trim()) {
            submitPostReport(postId, postOwnerId, reason.trim());
        }
        togglePostMenu(postId); // Close menu
    }

    // Submit post report
    function submitPostReport(postId, postOwnerId, reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'home.php?page=report-post';

        const postIdInput = document.createElement('input');
        postIdInput.type = 'hidden';
        postIdInput.name = 'postId';
        postIdInput.value = postId;

        const ownerIdInput = document.createElement('input');
        ownerIdInput.type = 'hidden';
        ownerIdInput.name = 'ownerId';
        ownerIdInput.value = postOwnerId;

        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;

        form.appendChild(postIdInput);
        form.appendChild(ownerIdInput);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }


    // Delete post function
    function deletePost(postId) {
        if (confirm('Bạn có chắc chắn muốn xóa bài viết này?\nHành động này không thể hoàn tác!')) {
            // Tạo form để submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            // Thêm postId
            const postIdInput = document.createElement('input');
            postIdInput.type = 'hidden';
            postIdInput.name = 'postId';
            postIdInput.value = postId;
            form.appendChild(postIdInput);
            
            // Thêm deletePost flag
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'deletePost';
            deleteInput.value = '1';
            form.appendChild(deleteInput);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
        
        // Close menu
        togglePostMenu(postId);
    }
    </script>

        
