<?php
try {
    // 单独查询订单
    $queryOrder = new \Easypay\WePay\Api\QueryOrder([
        'merchant_id' => '2020311',
        'merchant_key' => 'QBcvfTiNHAEHZaaYbeYRCPIFPdzGJWmd',
        'gateway_url' => 'http://apay.azhifu88.com/Pay',
        'is_debug' => true,
        'log_file' => 'QueryOrder.log'
    ]);
    // 查询出来订单数据
    $order = $queryOrder->setOrderNo('202012202341153246')->getOrderData();
    echo "<pre />";
    var_dump($order);
} catch (\Easypay\WePay\InvalidResponseException $e) {
    echo $e->getMessage();
}