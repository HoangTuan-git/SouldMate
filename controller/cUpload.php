<?php
class CUpload
{
    /**
     * Xử lý toàn bộ validate + đổi tên + move + kiểm duyệt
     * Chỉ nhận $image (mảng từ $_FILES)
     * 
     * Trả về:
     *   - ['success' => true,  'hinhs' => 'ten1,ten2,...']
     *   - ['success' => false, 'error' => '2'|'3'|'4']  // giữ mã lỗi cũ
     */
    public function processImagesOnly($image)
    {
        $hinhs = "";

        for ($i = 0; $i < count($image["name"]); $i++) {
            if ($image['name'][$i] != "") {

                // 1) Kiểm tra kích thước
                if ($image['size'][$i] > 2 * 1024 * 1024) {
                    return ['success' => false, 'error' => '2'];
                }

                // 2) Kiểm tra định dạng
                if ($image['type'][$i] != 'image/jpeg' && $image['type'][$i] != 'image/png') {
                    return ['success' => false, 'error' => '3'];
                }

                // 3) KIỂM DUYỆT TRƯỚC trên file tạm, chưa move
                $tmpPath = $image['tmp_name'][$i];

                $user   = "1287100329";
                $secret = "JUKFmiMnayN6D8MJFMA8H8My5JfYzd4h";

                $params = [
                    'media'      => new CurlFile($tmpPath),
                    'models'     => 'nudity,offensive,weapon,self-harm',
                    'api_user'   => $user,
                    'api_secret' => $secret
                ];

                $ch = curl_init('https://api.sightengine.com/1.0/check.json');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                $response = curl_exec($ch);
                curl_close($ch);

                $output = json_decode($response, true);

                if (!isset($output['status']) || $output['status'] !== 'success') {
                    // chưa move, nên không cần unlink
                    return ['success' => false, 'error' => '4']; // API lỗi
                }

                // --- nudity ---
                if (isset($output['nudity'])) {
                    $nudity = $output['nudity'];
                    if (($nudity['raw'] ?? 0) > 0.5 || ($nudity['partial'] ?? 0) > 0.5) {
                        return ['success' => false, 'error' => '4'];
                    }
                }
                // --- weapon ---
                if (isset($output['weapon']['classes'])) {
                    $weapon = $output['weapon']['classes'];
                    if (($weapon['firearm'] ?? 0) > 0.5 || ($weapon['knife'] ?? 0) > 0.5) {
                        return ['success' => false, 'error' => '4'];
                    }
                }
                // --- offensive ---
                if (isset($output['offensive'])) {
                    foreach ($output['offensive'] as $label => $prob) {
                        if ($prob > 0.8 && $label !== 'prob') {
                            return ['success' => false, 'error' => '4'];
                        }
                    }
                }
                // --- self-harm ---
                if (isset($output['self-harm'])) {
                    if (($output['self-harm']['prob'] ?? 0) > 0.5) {
                        return ['success' => false, 'error' => '4'];
                    }
                }

                // 4) ĐÃ PASS KIỂM DUYỆT ⇒ MỚI move_uploaded_file
                $n = pathinfo($image['name'][$i]);
                $n['filename'] = $_SESSION['uid'] . "_" . $_SESSION['email'] . "_img_" . time();
                $folder = 'img/';

                // Giữ nguyên cách ghép tên theo code cũ (dù không “đẹp”)
                $hinh    = $n['filename'] . $n['dirname'] . $n['extension'];
                $newname = $folder . $hinh;

                // đảm bảo thư mục tồn tại
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                if (!move_uploaded_file($tmpPath, $newname)) {
                    return ['success' => false, 'error' => '4']; // thất bại khi move
                }
            } else {
                $hinh = '';
            }

            ($i == 0) ? $hinhs = $hinh : $hinhs = $hinhs . "," . $hinh;
        }

        return ['success' => true, 'hinhs' => $hinhs];
    }
}
