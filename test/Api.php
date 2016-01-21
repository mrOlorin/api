<?php

class Api
{

    private $token = '';

    public function __construct($base)
    {
        if (substr($base, -1) !== '/') {
            $base .= '/';
        }
        $this->base_url = $base;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    private function getCurl($url)
    {
        if ($this->token) {
            $url .= ((parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&') . 'token=' . $this->token;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_URL, $this->base_url . $url);
        return $ch;
    }

    private function exec($ch)
    {
        return curl_exec($ch);
    }

    public function get($url, array $data = [])
    {
        return $this->exec($this->getCurl($url . '?' . http_build_query($data)));
    }

    public function post($url, array $data = null)
    {
        $ch = $this->getCurl($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($data));
        return $this->exec($ch);
    }

    public function put($url, array $data = null)
    {
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        $ch = $this->getCurl($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        return curl_exec($ch);
    }

    public function delete($url, array $data = null)
    {
        $ch = $this->getCurl($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($ch);
    }

}
