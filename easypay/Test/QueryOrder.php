<?php
try {
    // 单独查询订单
    $queryOrder = new \Easypay\WePay\Api\QueryOrder([
        'merchant_id' => '',
        'merchant_key' => '',
        'gateway_url' => ''
    ]);
    // 查询出来订单数据
    $order = $queryOrder->setOrderNo('202012192037434699')->getOrderData();
    echo "<pre />";
    var_dump($order);
} catch (\Easypay\WePay\InvalidResponseException $e) {
    echo $e->getMessage();
}