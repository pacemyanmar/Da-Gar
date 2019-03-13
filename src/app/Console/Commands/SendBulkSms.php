<?php

namespace App\Console\Commands;

use App\Models\BulkSms;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Console\Command;
use Akaunting\Setting\Facade as Settings;
use Psr\Http\Message\ResponseInterface;


class SendBulkSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulksms:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To send sms in batch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sms_list = BulkSms::all();

        foreach($sms_list as $sms) {
            $this->sendToBoom($sms);
        }
    }

    public function sendToBoom($sms)
    {
        if($sms->status == 'new') {
            $client = new Client();

            $message = $sms->message; // m
            $api_key = Settings::get('boom_api_key'); // p
            // hardcoded since this is provided by BOOM SMS
            $keyword = 'PA'; // k
            $user = 'PACE'; // u
            $title = 'PACE'; // t

            $container = [];
            $history = Middleware::history($container);

            $stack = HandlerStack::create();
            $stack->push($history);

            $form_params = ['handler' => $stack, 'form_params' => [
                'k' => $keyword,
                'u' => $user,
                'p' => $api_key,
                'm' => $message,
                't' => $title,
                'callerid' => $sms->phone
            ]
            ];
            $promise = $client->requestAsync('POST', 'http://apiv2.blueplanet.com.mm/mptsdp/bizsendsmsapi.php', $form_params);

            $promise->then(
                function (ResponseInterface $res) use ($sms) {
                    $http_status = $res->getStatusCode();
                    $response_body = json_decode($res->getBody(), true);

                    $sms->status = ($response_body['result_name'])?$response_body['result_name']:$response_body['result_status'];
                    $sms->save();
                    return $res;
                },
                function (RequestException $e) {
                    $error_msg = $e->getMessage();
                    $request_method = $e->getRequest()->getMethod();
                }
            );
            $response = $promise->wait();
        }
        return;

    }

}
