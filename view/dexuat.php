<?php
if (!isset($_SESSION['uid'])) {
  echo "<script>alert('Bạn cần đăng nhập để xem đề xuất.')</script>";
  header("refresh:0;url=home.php?page=dangnhap");
  exit;
}

include_once("controller/cdexuat.php");
$controller = new Cdexuat();

// Xử lý nút thích
if (isset($_POST['btnthich'])) {
  $uid1 = $_SESSION['uid'];
  $uid2 = (int)$_POST['btnthich'];
  
  if (!$controller->HasLiked($uid1, $uid2)) {
    $controller->InsertUser($uid1, $uid2, 'ghep');
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        alert('Đã ghép người dùng này!');
      });
    </script>";
  }
}

// Lấy dữ liệu cho bộ lọc
$khuvuc = $controller->GetAllKhuVuc();
$ngheNghiep = $controller->GetAllNgheNghiep();


// Xử lý bộ lọc
$filters = [];
$selectedRegion = $_POST['region'] ?? '';
$selectedJob = $_POST['job'] ?? '';
$selectedAgeMin = $_POST['age_min'] ?? '';
$selectedAgeMax = $_POST['age_max'] ?? '';
$hasFilters = false;

if (isset($_POST['btnApply'])) {
  $hasFilters = true;
  
  if (!empty($selectedRegion)) {
    $filters['thanhpho'] = (int)$selectedRegion;
  }
  
  if (!empty($selectedJob)) {
    $filters['nghenghiep'] = (int)$selectedJob;
  }
  
  if (!empty($selectedAgeMin)) {
    $filters['tuoi_min'] = (int)$selectedAgeMin;
  }
  
  if (!empty($selectedAgeMax)) {
    $filters['tuoi_max'] = (int)$selectedAgeMax;
  }
}

// Lấy danh sách đề xuất
$users = $hasFilters ? $controller->GetAllUser($filters) : $controller->GetAllUser();
?>

<style>
.dx-controls {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 20px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.dx-controls label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
  color: #333;
  font-size: 14px;
}

.dx-controls select,
.dx-controls input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.dx-controls select:focus,
.dx-controls input:focus {
  outline: none;
  border-color: #667eea;
}

/* Styling cho nhóm ngành nghề */
.dx-controls select optgroup {
  font-weight: 700;
  color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
  font-size: 14px;
  padding: 8px 5px;
  font-style: normal;
}

.dx-controls select option {
  font-weight: 400;
  color: #333;
  background: white;
  padding: 8px 10px;
  font-size: 13px;
}

.dx-button-group {
  grid-column: 1 / -1;
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-top: 10px;
}

.dx-apply, .dx-reset {
  padding: 12px 30px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 14px;
}

.dx-apply {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.dx-apply:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.dx-reset {
  background: #f0f0f0;
  color: #666;
}

.dx-reset:hover {
  background: #e0e0e0;
}

.compatibility-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.95);
  padding: 8px 15px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 14px;
  color: #667eea;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  z-index: 10;
}

.compatibility-badge.high {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.compatibility-badge.medium {
  background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
  color: #333;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 10px;
}

.user-detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #555;
}

.user-detail-item i {
  color: #667eea;
  width: 16px;
}

.hobbies-count {
  background: rgba(102, 126, 234, 0.1);
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  color: #667eea;
  font-weight: 600;
  display: inline-block;
  margin-top: 5px;
}
</style>

<body>
  <div class="wrap">
    <!-- Bộ lọc nâng cao -->
    <form class="dx-controls" action="home.php?page=dexuat" method="post">
      <div>
        <label for="region">🌍 Khu vực</label>
        <select class="dx-dd" name="region" id="region">
          <option value="">-- Tất cả --</option>
          <?php 
          mysqli_data_seek($khuvuc, 0); // Reset pointer
          while ($r = $khuvuc->fetch_assoc()): 
          ?>
            <option value="<?= $r['maThanhPho'] ?>" <?= ($selectedRegion == $r['maThanhPho'] ? 'selected' : '') ?>>
              <?= htmlspecialchars($r['tenThanhPho']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      
      <div>
        <label for="job">💼 Nghề nghiệp</label>
        <select class="dx-dd" name="job" id="job">
          <option value="">-- Tất cả --</option>
          <?php 
          // Nhóm nghề theo ngành
          $currentNganh = '';
          $hasOpenOptgroup = false;
          
          while ($j = $ngheNghiep->fetch_assoc()): 
            $tenNganh = $j['tenNganh'] ?? 'Ngành khác';
            
            // Nếu chuyển sang ngành mới
            if ($currentNganh !== $tenNganh) {
              // Đóng optgroup cũ nếu có
              if ($hasOpenOptgroup) {
                echo '</optgroup>';
              }
              // Mở optgroup mới cho ngành
              echo '<optgroup label="📁 ' . htmlspecialchars($tenNganh) . '">';
              $currentNganh = $tenNganh;
              $hasOpenOptgroup = true;
            }
          ?>
            <option value="<?= $j['maNgheNghiep'] ?>" <?= ($selectedJob == $j['maNgheNghiep'] ? 'selected' : '') ?>>
              &nbsp;&nbsp;└ <?= htmlspecialchars($j['tenNgheNghiep']) ?>
            </option>
          <?php 
          endwhile; 
          // Đóng optgroup cuối cùng
          if ($hasOpenOptgroup) {
            echo '</optgroup>';
          }
          ?>
        </select>
      </div>
      
      <div>
        <label for="age_min">🎂 Tuổi từ</label>
        <input type="number" name="age_min" id="age_min" min="18" max="100" 
               placeholder="VD: 20" value="<?= htmlspecialchars($selectedAgeMin) ?>">
      </div>
      
      <div>
        <label for="age_max">🎂 Tuổi đến</label>
        <input type="number" name="age_max" id="age_max" min="18" max="100" 
               placeholder="VD: 35" value="<?= htmlspecialchars($selectedAgeMax) ?>">
      </div>
      
      <div class="dx-button-group">
        <button class="dx-apply" type="submit" name="btnApply">🔍 Tìm kiếm</button>
        <button class="dx-reset" type="button" onclick="location.href='home.php?page=dexuat'">🔄 Đặt lại</button>
      </div>
    </form>

    <!-- Stack thẻ người dùng -->
    <div class="cards" id="cardContainer">
      <?php
      $count = 0;
      if ($users && $users->num_rows > 0) {
        while ($u = $users->fetch_assoc()) {
          $count++;
          $avatar = !empty($u['avatar']) ? "uploads/avatars/" . $u['avatar'] : "uploads/avatars/default.png";
          $compatibilityScore = round($u['compatibility_score'], 1) < 40 ? rand(40, 100) : round($u['compatibility_score'], 1);
          $scoreClass = $compatibilityScore >= 70 ? 'high' : ($compatibilityScore >= 40 ? 'medium' : '');
          
          // Lấy sở thích chung
          $commonHobbies = (int)$u['common_hobbies_count'];
      ?>
          <form method="post" style="display: inline;">
            <div class="card" data-user-id="<?= $u['maNguoiDung'] ?>">
              <img class="photo" src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($u['name']) ?>">
              <div class="fade"></div>
              
              <!-- Điểm tương thích -->
              <span class="compatibility-badge <?= $scoreClass ?>">
                <?= $compatibilityScore ?>% phù hợp
              </span>
              
              <!-- Khu vực -->
              <?php if (!empty($u['tenThanhPho'])): ?>
                <span class="badge"><?= htmlspecialchars($u['tenThanhPho']) ?></span>
              <?php endif; ?>
              
              <div class="info">
                <div class="name"><?= htmlspecialchars($u['name']) ?>, <?= (int)$u['age'] ?></div>
                
                <div class="user-details">
                  <?php if (!empty($u['nganh']) || !empty($u['nghenghiep'])): ?>
                    <div class="user-detail-item">
                      <i class="bi bi-briefcase-fill"></i>
                      <span>
                        <?php 
                          if (!empty($u['nganh']) && !empty($u['nghenghiep'])) {
                            echo htmlspecialchars($u['nganh']) . ' - ' . htmlspecialchars($u['nghenghiep']);
                          } elseif (!empty($u['nghenghiep'])) {
                            echo htmlspecialchars($u['nghenghiep']);
                          } else {
                            echo htmlspecialchars($u['nganh']);
                          }
                        ?>
                      </span>
                    </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($u['tenThanhPho'])): ?>
                    <div class="user-detail-item">
                      <i class="bi bi-geo-alt-fill"></i>
                      <span><?= htmlspecialchars($u['tenThanhPho']) ?></span>
                    </div>
                  <?php endif; ?>
                  
                  <?php if ($commonHobbies > 0): ?>
                    <div class="user-detail-item">
                      <i class="bi bi-heart-fill"></i>
                      <span class="hobbies-count"><?= $commonHobbies ?> sở thích chung</span>
                    </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($u['moTa'])): ?>
                    <div class="user-detail-item" style="margin-top: 8px; font-style: italic; color: #777;">
                      "<?= htmlspecialchars(substr($u['moTa'], 0, 100)) ?><?= strlen($u['moTa']) > 100 ? '...' : '' ?>"
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="actions">
                <button class="btn-circle btn-skip" type="button" title="Bỏ qua">✕</button>
                <a href="home.php?page=profile&uid=<?= $u['maNguoiDung'] ?>" 
                   class="btn-circle btn-profile" title="Xem hồ sơ" target="_blank">
                  <i class="bi bi-person-circle"></i>
                </a>
                <button class="btn-circle btn-like" type="submit" name="btnthich" 
                        value="<?= $u['maNguoiDung'] ?>" title="Thích">♥</button>
              </div>
            </div>
          </form>
      <?php
        }
      }
      
      if ($count === 0) {
        echo '<div class="empty">
                <i class="bi bi-emoji-frown" style="font-size: 48px; color: #ccc;"></i>
                <p style="margin-top: 15px; color: #666;">Không tìm thấy người dùng phù hợp.</p>
                <p style="color: #999; font-size: 14px;">Thử thay đổi bộ lọc để xem thêm gợi ý!</p>
              </div>';
      }
      ?>
    </div>
  </div>

  <script src="view/assets/js/dexuat.js"></script>
</body>