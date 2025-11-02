<?php
/**
 * MoMo Payment Helper Class
 * Xử lý tích hợp thanh toán MoMo
 */

class MoMoHelper
{
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $endpoint;

    public function __construct()
    {
        require_once(__DIR__ . '/../config/momo.config.php');
        
        $this->partnerCode = MOMO_PARTNER_CODE;
        $this->accessKey = MOMO_ACCESS_KEY;
        $this->secretKey = MOMO_SECRET_KEY;
        $this->endpoint = MOMO_ENDPOINT;
    }

    /**
     * Tạo chữ ký (signature) cho request
     * Thứ tự field phải theo alphabet như MoMo yêu cầu
     */
    private function createSignature($data)
    {
        // Quan trọng: Thứ tự phải chính xác theo MoMo documentation
        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . $data['amount'] .
                   "&extraData=" . $data['extraData'] .
                   "&ipnUrl=" . $data['ipnUrl'] .
                   "&orderId=" . $data['orderId'] .
                   "&orderInfo=" . $data['orderInfo'] .
                   "&partnerCode=" . $this->partnerCode .
                   "&redirectUrl=" . $data['redirectUrl'] .
                   "&requestId=" . $data['requestId'] .
                   "&requestType=" . $data['requestType'];

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);
        
        // Debug log (xóa khi production)
        error_log("MoMo Signature Debug:");
        error_log("Raw Hash: " . $rawHash);
        error_log("Signature: " . $signature);
        
        return $signature;
    }

    /**
     * Tạo request thanh toán MoMo
     * 
     * @param string $orderId - Mã đơn hàng
     * @param int $amount - Số tiền
     * @param string $orderInfo - Thông tin đơn hàng
     * @param array $extraData - Dữ liệu bổ sung
     * @return array - Response từ MoMo
     */
    public function createPaymentRequest($orderId, $amount, $orderInfo, $extraData = [])
    {
        $requestId = time() . "";
        $extraDataEncoded = !empty($extraData) ? base64_encode(json_encode($extraData)) : "";

        $requestData = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "SoulMatch",
            'storeId' => "SoulMatch",
            'requestId' => $requestId,
            'amount' => (string)$amount, // Convert to string
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => MOMO_RETURN_URL,
            'ipnUrl' => MOMO_NOTIFY_URL,
            'lang' => MOMO_LANG,
            'extraData' => $extraDataEncoded,
            'requestType' => MOMO_REQUEST_TYPE,
            'autoCapture' => true
        ];

        // Tạo signature
        $requestData['signature'] = $this->createSignature($requestData);

        // Debug log
        error_log("MoMo Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT));

        // Gửi request đến MoMo
        $response = $this->sendRequest($this->endpoint, $requestData);
        
        // Debug response
        error_log("MoMo Response: " . json_encode($response, JSON_PRETTY_PRINT));

        return $response;
    }

    /**
     * Gửi request HTTP đến MoMo API
     */
    private function sendRequest($url, $data)
    {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        
        // SSL settings
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);

        if ($curlError) {
            error_log("CURL Error: " . $curlError);
            return [
                'resultCode' => 99,
                'message' => 'Lỗi kết nối: ' . $curlError
            ];
        }

        if ($httpCode == 200 && $result) {
            return json_decode($result, true);
        }

        error_log("HTTP Error Code: " . $httpCode);
        error_log("Response: " . $result);
        
        return [
            'resultCode' => 99,
            'message' => 'Lỗi kết nối đến MoMo (HTTP ' . $httpCode . ')',
            'error' => $result
        ];
    }

    /**
     * Xác thực signature từ MoMo callback
     */
    public function verifySignature($data)
    {
        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . $data['amount'] .
                   "&extraData=" . $data['extraData'] .
                   "&message=" . $data['message'] .
                   "&orderId=" . $data['orderId'] .
                   "&orderInfo=" . $data['orderInfo'] .
                   "&orderType=" . $data['orderType'] .
                   "&partnerCode=" . $data['partnerCode'] .
                   "&payType=" . $data['payType'] .
                   "&requestId=" . $data['requestId'] .
                   "&responseTime=" . $data['responseTime'] .
                   "&resultCode=" . $data['resultCode'] .
                   "&transId=" . $data['transId'];

        $expectedSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return ($expectedSignature === $data['signature']);
    }

    /**
     * Kiểm tra trạng thái thanh toán
     * resultCode = 0: Thành công
     * resultCode khác: Thất bại
     */
    public function isPaymentSuccess($resultCode)
    {
        return ($resultCode == 0);
    }

    /**
     * Tạo mã đơn hàng unique
     */
    public static function generateOrderId($userId, $packageId)
    {
        return 'ORD_' . $userId . '_' . $packageId . '_' . time();
    }
}
