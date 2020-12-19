<?php

namespace Easypay\WePay\Api;

use Easypay\WePay\BasePay;
use Easypay\WePay\InvalidResponseException;

/**
 * 查询平台订单
 * Class QueryOrder
 * @package Easypay\WePay\Api
 */
class QueryOrder extends BasePay
{
    protected $data = [];
    protected $config = [];
    private $gatewayUrl;

    const ORDER_QUERY_ACTION = 'orderquery';   // 平台订单查询接口
    const ORDER_PAY_STATUS = 1; // 支付状态【1正常支付】【0支付异常】

    public function __construct(array $config)
    {
        if (!array_key_exists('merchant_key', $config)) {
            throw new \InvalidArgumentException("Merchant_key missing parameter.");
        }
        if (!array_key_exists('merchant_id', $config)) {
            throw new \InvalidArgumentException("Merchant_id missing parameter.");
        }
        if (!array_key_exists('gateway_url', $config)) {
            throw new \InvalidArgumentException("Gateway_url missing parameter.");
        }

        $this->gatewayUrl = $config['gateway_url'];
        $this->config['merchant_key'] = $config['merchant_key']; // 商户秘钥
        $this->config['merchant_id'] = $config['merchant_id']; // 商户ID
    }

    // 注意：这里是平台订单号，并发内部创建
    public function setOrderNo($orderNo)
    {
        $this->data = array(
            'fxid' => $this->config['merchant_id'],
            'fxddh' => $orderNo,
            'fxaction' => static::ORDER_QUERY_ACTION,
        );

        $this->data['fxsign'] = $this->buildSignature();
        return $this;
    }

    protected function buildSignature()
    {
        $sign = $this->config['merchant_id'] .
                $this->data['fxddh'] .
                $this->data['fxaction'] .
                $this->config['merchant_key'];

        return md5($sign);
    }

    public function getOrderData()
    {
        // 订单数据
        $orderData = $this->httpRequest($this->gatewayUrl, 'POST', $this->data);
        if ($this->isSuccessful()) {
            return $orderData;
        }

        throw new InvalidResponseException($this->__toString(),10002);
    }

    protected function isSuccessful()
    {
        if (intval($this->getPayload('fxstatus')) === static::ORDER_PAY_STATUS) {
            return true;
        }
        return false;
    }
}












