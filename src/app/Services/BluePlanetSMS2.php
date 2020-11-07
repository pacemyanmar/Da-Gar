<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApiUrlErrorException;
use App\Exceptions\TokenErrorException;
use App\Exceptions\SenderIdErrorException;

Class BluePlanetSMS2 implements SMSInterface {

    protected $api_url;

    protected $username;

    protected $password;

    protected $sender_id;

    public function setApiUrl($api_url){
        $this->api_url = $api_url;

        if(!$this->api_url) {
            throw new ApiUrlErrorException();
        }
        return $this;
    }

    public function setUsername($username){
        $this->username = $username;

        return $this;
    }

    public function setAccessToken($token){
        $this->password = $token; // Settings::get('boom_api_key');
        if(!$this->password) {
            throw new TokenErrorException();
        }
        return $this;
    }

    public function setSenderId($senderId) {
        $this->sender_id = $senderId; // Settings::get('sender_id', 'PACE');
        if(!$this->sender_id) {
            throw new SenderIdErrorException();
        }
        return $this;
    }
    
    public function receive($request){}

    public function sendBatch($sms_model)
    {
        $client = new Client();
        $provider = [
            'url' => $this->api_url,
            'u' => $this->username,
            'p' => $this->password,
            'k' => $this->sender_id,
            't' => $this->sender_id,
        ];

        $requests = function () use ($client, $sms_model, $provider) {
            foreach($sms_model as $sms) {
                yield function() use ($client, $provider, $sms) {
                    $message = str_replace("{{NAME}}", $sms->name, $sms->message);
                    $query = [
                        'u' => $provider['u'],
                        'p' => $provider['p'],
                        'k' => $provider['k'],
                        't' => $provider['t'],
                        'callerid' => preg_replace('/^(\+959|09)/','959',ltrim($sms->phone)),
                        'm' => $message,
                        'uni' => true
                    ];
                    Log::debug($this->api_url);
                    return $client->requestAsync('GET', $this->api_url, [
                        'headers' => [
                            'Accept' => 'application/json'
                        ],
                        'query' => $query
                    ]);
                };
            }
        };

        $pool = new Pool($client, $requests());
        $promise = $pool->promise();

// Force the pool of requests to complete.
        $promise->wait();
    }

    public function send($request){
        $client = new Client();
        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create();
        $stack->push($history);
        /**
         * - "u" string for username
         * - "p" string for password
         * - "k" string for keyword ( brand name )
         * - "t" string for title ( brand name )
         * - "m" string for message
         * - "uni" boolean for unicode true/false
         * - "callerid" phone number
         */
        $query = [
            'u' => $this->username,
            'p' => $this->password,
            'k' => $this->sender_id,
            't' => $this->sender_id,
            'callerid' => preg_replace('/^(\+959|09)/','959',ltrim($request['to'])),
            'm' => $request['message'],
            'uni' => true
        ];

        Log::debug($this->api_url);

        $promise = $client->requestAsync('GET', $this->api_url, [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'handler' => $stack,
            'query' => $query
        ]);

            $promise->then(
                function (ResponseInterface $res) {
                    $http_status = $res->getStatusCode();
                    $response_body = json_decode($res->getBody(), true);
                    return $res;
                },
                function (RequestException $e) {
                    $error_msg = $e->getMessage();
                    $request_method = $e->getRequest()->getMethod();
                }
            );
            return $promise->wait();
    }

}