<?php
/**
 * SMS send/receive interface
 *
 */
namespace App\Services;

Interface SMSInterface {

    public function setApiUrl($api_url);

    public function setClientKey($client_key);
    
    public function receive($request);

    public function send($request);
  
  }