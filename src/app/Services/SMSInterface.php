<?php
/**
 * SMS send/receive interface
 *
 */
namespace App\Services;

Interface SMSInterface {

    public function setApiUrl($api_url);

    public function setAccessToken();
    
    public function receive($request);

    public function send($request, $model);
  
  }