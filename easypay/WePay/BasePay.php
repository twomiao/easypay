<?php

namespace Easypay\WePay;

abstract class BasePay
{
    /**
     * 响应数据
     * @var string $payload
     */
    private $payload = '';

    protected $charsets = ["ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'];

    /**
     * @param $url
     * @param string $method
     * @param array $postData
     * @return $this
     */
    protected function httpRequest($url, $method = 'GET', $postData = array())
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $header = array(
            "User-Agent: $user_agent"
        );
        if (!empty($url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            if (strstr($url, 'https://')) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            }

            if (strtoupper($method) == 'POST') {
                $curlPost = is_array($postData) ? http_build_query($postData) : $postData;
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            }
            $payload = curl_exec($ch);

            if ($payload === false) {
                throw new \LogicException('Curl fail:' . curl_error($ch));
            }
            curl_close($ch);

            $data = [];
            if (is_string($payload)) {
                $data = json_decode($payload, true, 512);

                if (!is_array($data)) {
                    throw new \InvalidArgumentException("json error: ( { $payload } ), error_msg: " . json_last_error_msg());
                }
            }

            $this->payload = $data;
        }
        return $this;
    }

    // 请求是否成功
    abstract protected function isSuccessful();

    /**
     * 获取指定数据
     * @param string $name
     * @return mixed|string
     */
    protected function getPayload($name)
    {
        return isset($this->payload[$name]) ? $this->payload[$name] : '';
    }

    /**
     * 检测编码
     * @param $value
     * @return bool|false|mixed|string
     */
    private function detectEncoding($value)
    {
        return mb_detect_encoding($value, $this->charsets);
    }

    /**
     * 中文转换为UTF-8
     * @param $value
     * @param string $charset
     * @return false|string|string[]|null
     */
    protected function convertEncoding($value, $charset = 'UTF-8')
    {
        $fromEncoding = $this->detectEncoding($value);

        return mb_convert_encoding($value, $charset, $fromEncoding);
    }

    /**
     * 记录日志
     * @param $logFile
     * @param $title
     * @param $data
     */
    protected function recordLog($logFile, $title, $data)
    {
        $writeTime = date('Y-m-d H:i:s');

        if(is_array($data))
        {
            $msg = "{$writeTime} {$title}: ".json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $msg = "{$writeTime} {$title}: {$data}";
        }

        file_put_contents($logFile, $msg.PHP_EOL, FILE_APPEND);
    }

    /**
     * 获取全部数据
     * @return string
     */
    protected function getPayloadAll()
    {
        return $this->payload;
    }

    public function __toString()
    {
        if (is_array($this->payload) && !is_null($this->payload)) {
            return json_encode($this->payload, JSON_UNESCAPED_UNICODE);
        }

        return '';
    }
}