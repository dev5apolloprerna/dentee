<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AuthkeyWhatsAppService
{
    protected $authkey;
    protected $baseUrl;

    public function __construct()
    {
        $this->authkey = config('services.authkey.key');
        $this->baseUrl = config('services.authkey.url');
    }

    /**
     * Send Text Template
     */
    public function sendText($mobile, $wid, $bodyValues = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->authkey,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . 'requestjson.php', [
            "country_code" => "91",
            "mobile" => $mobile,
            "wid" => $wid,
            "type" => "text",
            "bodyValues" => $bodyValues
        ]);

        return $response->json();
    }

    /**
     * Send Media Template
     */
    public function sendMedia($mobile, $wid, $fileUrl, $bodyValues = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->authkey,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . 'requestjson.php', [
            "country_code" => "91",
            "mobile" => $mobile,
            "wid" => $wid,
            "type" => "media",
            "bodyValues" => $bodyValues,
            "headerValues" => [
                "headerFileName" => "Document",
                "headerData" => $fileUrl
            ]
        ]);

        return $response->json();
    }

    /**
     * Send Bulk Messages (v2 API)
     */
    public function sendBulk($wid, $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->authkey,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . 'requestjson_v2.0.php', [
            "version" => "2.0",
            "country_code" => "91",
            "wid" => $wid,
            "type" => "text",
            "data" => $data
        ]);

        return $response->json();
    }
}