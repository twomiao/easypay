<?php
try {
    $config = array(
        "fxnotifyurl" => "http://www.xiaxiantech.com/index.php/index/test",
        'fxbackurl' => 'http://www.baidu.com/index.php',
        'fxpay' => 'wxhf', // wxsm
        'merchant_key' => 'QBcvfTiNHAEHZaaYbeYRCPIFPdzGJWmd',
        'merchant_id' => '2020311',
        'gateway_url' => 'http://apay.azhifu88.com/Pay',
        'is_debug' => true,
        'log_file' => 'WePay.log'
    );

    $wePay = new \Easypay\WePay\WePay($config);
    $order = array(
        "order_no" => date('YmdHis') . mt_rand(1000, 9999),
        "product_name" => 'payTest',
        "order_price" => 30,
        "client_ip" => '118.112.56.13',
        'extra' => 'payTest'
    );
    // 保存订单数数据
    $wePay->setOrderItem($order);

    // 跳转付款页面
    $wePay->goWePay();
} catch (\Easypay\WePay\InvalidResponseException $e) {
    echo $e->getMessage();
}