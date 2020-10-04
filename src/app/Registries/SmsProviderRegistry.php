<?php

namespace App\Registries;

use App\Services\SMSInterface;

Class SmsProviderRegistry {
    protected $providers = [];

    function register ($name, SMSInterface $instance) {
        $this->providers[$name] = $instance;
        return $this;
      }
    
      function get($name) {
        if (in_array($name, $this->providers)) {
          return $this->providers[$name];
        } else {
          throw new Exception("Invalid SMS Provider");
        }
      }

}