<?php

namespace App\Console\Commands;

use App\Models\BulkSms;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Console\Command;
use Akaunting\Setting\Facade as Settings;
use Illuminate\Support\Facades\Log;
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
        $smsprovider = app(config('sms.providers.blueplanet.active'));

        $sms_list = BulkSms::where('status','new')->get();

        foreach($sms_list->chunk(20) as $chunked_sms) {
            foreach ($chunked_sms as $sms) {

                $message = str_replace("{{NAME}}", $sms->name, $sms->message);

                $smsresponse = $smsprovider->send(['message' => $message, 'to' => $sms->phone]);

                $response_body = json_decode($smsresponse->getBody(), true);

                if(array_key_exists('status', $response_body)) {
                    $sms->status = ($response_body['status'] === 0) ? "sent" : $response_body['error-text'];
                }

                if(array_key_exists('result_code', $response_body)) {
                    $sms->status = ($response_body['result_code'] === 1) ? "sent" : $response_body['result_name'];
                }
                $sms->save();

            }
            usleep(20 * 1000); // delay 20 milli seconds
        }

    }

}
