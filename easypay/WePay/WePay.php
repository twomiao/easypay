<?php
namespace Easypay\WePay;

class WePay extends BasePay
{
    /**
     * 配置文件
     * @var array $config
     */
    protected $config = [
        'is_debug' => false,
    ];

    /**
     * 订单数据
     * @var array $item
     */
    protected $order = [];

    // 成功
    const WEPAY_STATUS_SUCCESS = 1;

    /**
     * WePay constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 创建订单
     * @param array $item
     */
    public function setOrderItem(array $item = [])
    {
        $order = array(
            'fxid' => $this->config['merchant_id'],  // 商务号
            'fxddh' => $item['order_no'],  // 商户订单号
            'fxdesc' =>  $this->convertEncoding($item['product_name']), // 商品名称
            'fxfee' => $item['order_price'], // 支付金额
            'fxnotifyurl' => $this->config['fxnotifyurl'], // 异步通知地址
            'fxbackurl' => $this->config['fxbackurl'], // 同步通知地址
            'fxpay' => $this->config['fxpay'],    // 请求类型 【微信话费：wxhf】【支付宝话费：zfbhf】
            'fxsmstyle' => 'sm',  // 扫码模式	否	用于扫码模式（sm），仅带sm接口可用，默认0返回扫码图片，为1则返回扫码跳转地址。
            'fxattch' => $this->convertEncoding($item['extra']), // 附加信息
            'fxip' => $item['client_ip'], // 客户端IP
        );
        $order['fxsign'] = $this->buildSignature($order);

        if ($this->config['is_debug'])
        {
            $this->recordLog($this->config['log_file'], "Send order data", $order);
        }

        $this->order = $order;
    }

    /**
     * 创建签名
     * @param $item
     * @return string
     */
    protected function buildSignature($item)
    {
        $signatureStr =
            $item['fxid'] .
            $item['fxddh'] .
            $item['fxfee'] .
            $item['fxnotifyurl'] .
            $this->config['merchant_key'];

        return md5($signatureStr);
    }

    // 调取支付接口
    public function goWePay()
    {
       $this->httpRequest($this->config['gateway_url'], 'POST', $this->order);

        if ($this->config['is_debug'])
        {
            $this->recordLog($this->config['log_file'], "Response data", $this->getPayloadAll());
        }

        if ($this->isSuccessful())
        {
            header('Location:' . $this->getPayload('payurl'));
            return;
        }

        throw new InvalidResponseException($this->__toString(), 10001);
    }

    protected function isSuccessful()
    {
        if (intval($this->getPayload('status')) === static::WEPAY_STATUS_SUCCESS)
        {
            return true;
        }
        return false;
    }
}