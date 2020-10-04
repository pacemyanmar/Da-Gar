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
use App\Registries\SmsProviderRegistry;

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
        $smsprovider = app('blueplanet');

        $sms_list = BulkSms::all();

        foreach($sms_list as $sms) {

            if($sms->status == 'new') {

                $message = str_replace("{{NAME}}", $sms->name, $sms->message);

                $smsresponse = $smsprovider->send(['message' => $message, 'to' => $sms->phone]);

                $response_body = json_decode($smsresponse->getBody(), true);

                $sms->status = ($response_body['status'] === 0)?"sent":$response_body['error-text'];
                $sms->save();
            }
        }
    }

}
