<?php

try {
    $wePayNotify = new \Easypay\WePay\WePayNotify([
        'merchant_id' => '',
        'merchant_key' => '',
        'gateway_url' => '',
        'is_debug' => true,
        'log_file' => 'WePayNotify.log'
    ]);

    /**
     * @var $notifyData 通知数据
     * @var $orderData 查询出来的订单数据
     */
    $wePayNotify->paymentLogic(function ($notifyData, $orderData) {
        // todo 业务逻辑
        // 记录日志。。。
        // $notifyData

    });

} catch (\Easypay\WePay\InvalidResponseException $e) {
    // 调试
    file_put_contents("pay_err.log", $e->getMessage().":".$e->getCode() . PHP_EOL, FILE_APPEND);
}