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
use App\Services\SMSInterface;

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

    public function sendToBoom($sms, SMSInterface $smsprovider)
    {
        if($sms->status == 'new') {

            $message = $sms->message; // m
            $to = $sms->phone;
            $smsprovider->send(['message' => $message, 'to' => $to]);
            
        }
        return;

    }

}
