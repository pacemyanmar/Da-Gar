<?php
/**
 * SMS send/receive interface
 *
 */
namespace App\Services;

Interface SMSInterface {

    public function setApiUrl($api_url);

    public function setAccessToken($token);

    public function setSenderId($senderId);
    
    public function receive($request);

    public function send($request);
  
  }