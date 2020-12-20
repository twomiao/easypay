<?php
namespace Easypay\WePay;

class WePayNotify extends BasePay
{
    /**
     * 来自平台通知的数据
     * @var array $notifyData
     */
    protected $notifyData;
    /**
     * 配置文件
     * @var $config
     */
    protected $config = [];

    /**
     * 当前查询订单数据
     * @var $order
     */
    protected $order;

    const WEPAY_NOTIFY_SUCCESS = 1;

    public function __construct(array $config = [])
    {
        $this->notifyData = $this->getInputData();
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 生成签名
     * @return string
     */
    protected function buildSignature()
    {
        $signature = $this->notifyData['fxstatus'] . // 订单状态
            $this->config['merchant_id'] .  // 商户号
            $this->notifyData['fxddh'] .    // 平台返回商户提交的订单号
            $this->notifyData['fxfee'] .   // 支付的价格(单位：元)
            $this->config['merchant_key']; // 商户秘钥
        return md5($signature);
    }

    public function getNotifyData()
    {
        if ($this->verifySignature() === false) {
            throw new InvalidResponseException("Signature error.", 10003);
        }

        return $this->notifyData;
    }

    public function paymentLogic(callable $payment)
    {
        try {
            // 异步通知数据
            $notifyData = $this->getNotifyData();

            if (isset($notifyData['fxddh'])) {
                $queryOrder = new \Easypay\WePay\Api\QueryOrder($this->config);
                // 查询出来订单数据
                $this->order = $order = $queryOrder->setOrderNo($notifyData['fxddh'])->getOrderData();
                return $payment($notifyData, $order);
            }
            throw new InvalidResponseException("Missing order number.", 10004);
        } catch (InvalidResponseException $e) {
            throw $e;
        } finally {
            echo $this->notifyMsgToWeChat();
        }
    }

    // 验证签名
    protected function verifySignature()
    {
        if (isset($this->notifyData['fxsign'])) {
            $fxsign = $this->notifyData['fxsign'];

            return $this->buildSignature() === $fxsign;
        }
        return false;
    }

    // 发送给微信的消息
    public function notifyMsgToWeChat()
    {
        if (!is_null($this->notifyData) && $this->isSuccessful()) {
            return 'success';
        }

        return 'fail';
    }

    protected function getInputData()
    {
        $postData = $_REQUEST;
        if (is_array($postData)) {
            foreach ($postData as $name => $value) {
                $value = trim($value);
                if ($name === 'fxattch') // 附加说明
                {
                    $postData[$name] = $this->convertEncoding($value);
                } elseif ($name === 'fxdesc') { // 商品名称
                    $postData[$name] = $this->convertEncoding($value);
                }
            }
        }

        return [];
    }

    protected function isSuccessful()
    {
        if (isset($this->order['fxstatus']) && intval($this->order['fxstatus']) === static::WEPAY_NOTIFY_SUCCESS) {
            return true;
        }
        return false;
    }
}