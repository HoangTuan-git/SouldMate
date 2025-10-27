<?php
if (!isset($_SESSION['uid'])) {
  echo "<script>alert('Bạn cần đăng nhập để xem đề xuất.')</script>";
  header("refresh:0;url=home.php?page=dangnhap");
}
?>

<body>
  <?php
  include_once("model/mdexuat.php");
  $p = new Mdexuat();

  // Xử lý nút thích TRƯỚC khi load data
  if (isset($_POST['btnthich'])) {
    $uid1 = $_SESSION['uid'];
    $uid2 = $_POST['btnthich'];

    // Kiểm tra nếu đã thích rồi thì không thêm nữa
    // if (!$p->HasLiked($currentUserId, $likedUserId)) {
    $p->InsertUser($uid1, $uid2, 'like', $uid1);
    // } else {
    //   echo "<script>alert('Bạn đã thích người dùng này trước đó rồi!')</script>";
    // }
  }

  $khuvuc = $p->GetAllKhuVuc();
  $users  = $p->GetAllUserByDeXuat();
  $selected = $_POST['region'] ?? '';
  ?>
  <div class="wrap">
    <!-- Bộ lọc -->
    <form class="dx-controls" action="home.php?page=dexuat" method="post">
      <select class="dx-dd" name="region">
        <option value="">-- Chọn khu vực --</option>
        <?php while ($r = $khuvuc->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($r['ID']) ?>" <?= ($selected == $r['ID'] ? ' selected' : '') ?>>
            <?= htmlspecialchars($r['TenTP']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <button class="dx-apply" type="submit" name="btn">Áp dụng</button>
      <button class="dx-reset" type="button" onclick="location.href='home.php?page=dexuat'">Đặt lại</button>
    </form>

    <!-- Stack thẻ -->
    <?php
    if (isset($_POST['btn']) && $selected !== '') {
      echo '<div class="cards" id="cardContainer">';
      echo '<div class="empty">Tạm thời hết đề xuất phù hợp.</div>';
      $regionid = $selected;
      $count = 0;
      while ($u = $users->fetch_assoc()) {
        if ($regionid === '' || $u['region_id'] == $regionid) {
          $count++;
          $src = "img/" . $u['avatar'];
    ?>
          <form method="post">
            <div class="card">
              <img class="photo" src="<?= htmlspecialchars($src ?? 'img/default.png') ?>" alt="avatar">
              <div class="fade"></div>
              <span class="badge"><?= htmlspecialchars($u['TenTP']) ?></span>
              <div class="info">
                <div class="name"><?= htmlspecialchars($u['name']) ?>, <?= (int)$u['age'] ?></div>
                <div class="meta">Khu vực: <?= htmlspecialchars($u['TenTP']) ?></div>
              </div>
              <div class="actions">
                <button class="btn-circle btn-skip" type="button" title="Bỏ qua">✕</button>
                <button class="btn-circle btn-like" type="submit" name='btnthich' value="<?= htmlspecialchars($u['user_id']) ?>" title="Thích">♥</button>
              </div>
            </div>
          </form>
    <?php
        }
      }
      if ($count === 0) {
        // không có thẻ nào, bật trạng thái rỗng bằng JS ở dưới
      }
    } else {
      echo '<div class="cards is-empty" id="cardContainer">';
      echo '<div class="empty">Hãy chọn bộ lọc và nhấn "Áp dụng" để xem đề xuất.</div>';
    }
    ?>
  </div>
  </div>

  <script src="view/assets/js/dexuat.js"></script>
</body>

</html>