<?php

namespace App\Helpers;

class WebTraveler
{
    protected $url;

    protected $data;

    protected $postRequest = 0;

    public static function factory()
    {
        $class = static::class;
        return new $class;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function activePost()
    {
        $this->postRequest = 1;
        return $this;
    }

    public function request()
    {
        $curl = curl_init($this->url);
        if ($this->postRequest == 1) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        }
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curl,CURLOPT_HEADER,true);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
