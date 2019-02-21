<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\SmsAPIController;
use App\Repositories\ProjectRepository;
use App\Repositories\SmsLogRepository;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Akaunting\Setting\Facade as Settings;
use Maatwebsite\Excel\Facades\Excel;

class GenerateSMSLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smslog:generate {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sms log on csv file input';

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
        $file = $this->argument('file');
        $app_secret = Settings::get('app_secret');

        Excel::load($file, function ($reader) use ($app_secret) {
            $reader->each(function ($row) use ($app_secret) {
                $fake = Factory::create();
                $sms = [
                    'secret' => $app_secret,
                    'event' => 'incoming_message',
                    'service_id' => $fake->uuid,
                    'from_number' => $row->from,
                    'from_number_e164' => $row->from,
                    'to_number' => $row->to,
                    'content' => $row->message,
                    'noreply' => true
                ];

                $request = new \Illuminate\Http\Request();

                $request->replace($sms);
                $app = Container::getInstance();
                $sms_log = new SmsLogRepository($app);

                $project_instance = new ProjectRepository($app);

                $sms_api = new SmsAPIController($sms_log,$project_instance);
                $sms_api->telerivet($request);
            });
        });

    }
}
