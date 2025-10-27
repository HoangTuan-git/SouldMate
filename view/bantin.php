<?php
include_once("controller/cBanTin.php");
$cBanTin = new cBanTin();
if (isset($_POST['postNews'])) {
    $p = $cBanTin->cAddTinTuc($_SESSION['uid'], $_POST['newsContent'], $_FILES['newsImages'], $_POST['privacy']);
    switch ($p) {
        case '1':
            echo '<script>alert("Lỗi: Bạn phải nhập nội dung hoặc chọn file để đăng!")</script>';
            break;
        case '2':
            echo '<script>alert("Lỗi: Kích thước ảnh quá lớn (tối đa 2MB)!")</script>>';
            break;
        case '3':
            echo '<script>alert("Lỗi: Định dạng ảnh không được hỗ trợ (chỉ chấp nhận PNG/JPEG)!")</script>';
            break;
        case '4':
            echo '<script>alert("Lỗi: Ảnh không phù hợp!")</script>';
            break;
        case '5':
            echo '<script>alert("Đăng bản tin thành công!")</script>';
            break;
        default:
            echo '<script>alert("Đã xảy ra lỗi không xác định!")</script>';
            break;
    }
    header("refresh:0.5;url=home.php?page=bantin");
}
// Hàm tính thời gian đã đăng
function timeAgo($datetime)
{
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
<!-- Emoji Picker CDN (module) -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<div class="main-container mt-2">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <!-- Sử dụng container tùy chỉnh thay vì col-lg-6 -->
            <div class="feed-container mx-auto ">
                <!-- Post Input Card -->
                <div class="post-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <?php
                            include_once("controller/cHoSo.php");
                            $cHoSo = new controlHoSo();

                            $checkProfile = isset($_SESSION['uid'])? $cHoSo->checkHoSoExists($_SESSION['uid']) : false;

                            if (isset($_SESSION['uid']) && $checkProfile):
                                //load avatar from session
                                $src = 'uploads/avatars/' . ($_SESSION['avatar'] ?? 'default.png');
                            ?>

                                <img src="<?= htmlspecialchars($src) ?>"
                                    alt="Avatar"
                                    class="avatar-circle me-3">
                                <div class="post-input flex-fill"
                                    data-bs-toggle="modal"
                                    data-bs-target="#postModal"
                                    role="button" onclick="resetModal()">
                                    <?= htmlspecialchars($_SESSION['email']) ?> ơi, bạn đang nghĩ gì thế?
                                </div>
                            <?php else: ?>
                                <div class="post-input flex-fill text-center"
                                    onclick="window.location.href='home_test.php?page=dangnhap'"
                                    role="button">
                                    Đăng nhập và tạo hồ sơ để đăng bài tương tác
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($_SESSION['uid'])): ?>
                            <hr class="my-3">
                            <div class="row text-center g-2" style="font-size: 14px;" onclick="resetModal()">
                                <div class="col-6">
                                    <button class="btn btn-light w-100 py-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#postModal">
                                        <i class="bi bi-image text-success me-2"></i>
                                        <span>Ảnh</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-light w-100 py-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#postModal">
                                        <i class="bi bi-emoji-smile text-warning me-2"></i>
                                        <span>Emoji</span>
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- News Feed -->
                <?php
                $posts = $cBanTin->cGetAllTinTuc();
                if ($posts && $posts->num_rows > 0):
                    while ($post = $posts->fetch_assoc()):
                        $images = !empty($post['noiDungAnh']) ? explode(',', $post['noiDungAnh']) : [];
                        $imageCount = count($images);

                        // Xác định class cho grid layout
                        $gridClass = 'images-1';
                        if ($imageCount == 2) $gridClass = 'images-2';
                        elseif ($imageCount == 3) $gridClass = 'images-3';
                        elseif ($imageCount == 4) $gridClass = 'images-4';
                        elseif ($imageCount >= 5) $gridClass = 'images-5-plus';
                ?>
                        <div class="feed-post-card">
                            <!-- Post Header -->
                            <div class="feed-post-header">
                                <div class="feed-post-avatar-wrapper">
                                    <img src="img/<?php echo $post['avatar'] ?? 'default.png'; ?>"
                                        alt="Avatar"
                                        class="feed-post-avatar verified"
                                        onerror="this.src='img/default.png'">
                                    <div class="feed-post-user-info">
                                        <h6 class="feed-post-username"><?php echo htmlspecialchars($post['hoTen']); ?></h6>
                                        <p class="feed-post-time"><?php echo timeAgo($post['ngayTao']); ?></p>
                                    </div>
                                </div>
                                <button class="feed-post-menu-btn">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                            </div>

                            <!-- Post Content -->
                            <div class="feed-post-content">
                                <?php if (!empty($post['noiDungText'])): ?>
                                    <p class="feed-post-text"><?php echo nl2br(htmlspecialchars($post['noiDungText'])); ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Post Images -->
                            <?php if ($imageCount > 0): ?>
                                <div class="feed-post-images <?php echo $gridClass; ?>">
                                    <?php
                                    $displayImages = array_slice($images, 0, 5); // Hiển thị tối đa 5 ảnh
                                    foreach ($displayImages as $index => $image):
                                        if (empty(trim($image))) continue;
                                        $isLast = ($index === 4 && $imageCount > 5);
                                    ?>
                                        <div class="feed-post-image-item">
                                            <img src="img/<?php echo trim($image); ?>"
                                                alt="Post image"
                                                loading="lazy">
                                            <?php if ($isLast): ?>
                                                <div class="feed-post-image-overlay">
                                                    +<?php echo $imageCount - 5; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Post Stats -->
                            <div class="feed-post-stats">
                                <div class="feed-post-stats-inner">
                                    <div class="feed-post-reactions">
                                        <i class="bi bi-heart-fill feed-post-reaction-icon text-danger"></i>
                                        <span class="feed-post-reaction-count"><?php echo $post['soLuotThich'] ?? 0; ?></span>
                                    </div>
                                    <div>
                                        <span class="feed-post-comment-count">
                                            <?php echo $post['soBinhLuan'] ?? 0; ?> bình luận
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Actions -->
                            <div class="feed-post-actions">
                                <button class="feed-post-action-btn">
                                    <i class="bi bi-heart"></i>
                                    <span>Thích</span>
                                </button>
                                <button class="feed-post-action-btn">
                                    <i class="bi bi-chat"></i>
                                    <span>Bình luận</span>
                                </button>
                                <button class="feed-post-action-btn">
                                    <i class="bi bi-share"></i>
                                    <span>Chia sẻ</span>
                                </button>
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
            </div>
        </div>
    </div>
</div>

<!-- Post Modal -->
<div class="modal fade" id="postModal" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="postModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Tạo bản tin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetModal()"></button>
                </div>

                <div class="modal-body">
                    <!-- User Info -->
                    <div class="d-flex align-items-center mb-4">
                        <img src="<?= htmlspecialchars($src ?? 'img/default.png') ?>"
                            alt="Avatar"
                            class="avatar-circle avatar-lg me-3">
                        <div>
                            <h6 class="mb-0 fw-bold">
                                <?= isset($_SESSION['uid']) ? htmlspecialchars($_SESSION['email']) : 'Người dùng' ?>
                            </h6>
                            <small class="text-muted">
                                <select name="privacy" id="privacySelect">
                                    <option value="public">🌍 Công khai</option>
                                    <option value="friends">👫 Bạn bè</option>
                                </select>
                            </small>
                        </div>
                    </div>

                    <!-- Content Input -->
                    <div class="mb-4">
                        <textarea name="newsContent"
                            class="form-control border-0 fs-5"
                            style="resize: none; min-height: 120px;"
                            placeholder="Bạn đang nghĩ gì?"></textarea>
                    </div>

                    <!-- Media Upload Section -->
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-medium">Thêm vào bản tin của bạn</span>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <label class="btn btn-outline-success btn-sm flex-fill" for="imageInput">
                                <i class="bi bi-image me-2"></i>Hình ảnh
                            </label>
                            <input type="file"
                                id="imageInput"
                                name="newsImages[]"
                                class="d-none" multiple onchange="showFile(this)">
                            <!-- Input ẩn để thêm ảnh mới -->
                            <input type="file"
                                id="addMoreInput"
                                class="d-none" multiple onchange="addMoreFiles(this)">
                            <button type="button" class="btn btn-outline-warning btn-sm flex-fill"
                                onclick="toggleEmojiPicker()">
                                <i class="bi bi-emoji-smile me-2"></i>Cảm xúc
                            </button>
                        </div>
                        <!-- THÊM EMOJI PICKER CONTAINER -->
                        <div id="emojiPickerContainer" class="mb-3" style="display: none;">
                            <emoji-picker></emoji-picker>
                        </div>
                    </div>
                    <!-- Hiển thị hình ảnh -->
                    <div id="previewSection" class="mb-3">
                        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2"></div>
                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="submit" name="postNews" class="btn btn-gradient btn-lg w-100 rounded-pill">
                        <i class="bi bi-send me-2"></i>Đăng bản tin
                    </button>
                </div>

            </form>
        </div>
    </div>
    <script src="view/assets/js/bantin.js"></script>
</div>