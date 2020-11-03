<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\SmsAPIController;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Repositories\SmsLogRepository;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Akaunting\Setting\Facade as Settings;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;
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

        $reader = Reader::createFromPath($file, 'r');
        $reader->setHeaderOffset(0);
        $count = count($reader);
        $total = $count;
        Log::debug('Count: '. $count);
        $offset = 0;
        $limit = 1000;
        $gap = $limit;
        while($count){

            if($offset === $gap) {
                Log::debug('Offset: '. $offset);
                Log::debug('Gap: '.$gap);
                Log::debug('total: '.$total);
                if(($total - $gap) < 1000){
                    $limit = $total - $gap;
                }

                $stmt = (new Statement())
                    ->offset($offset)
                    ->limit($limit);
                $records = $stmt->process($reader);

                log::debug('Records: '. count($records));
                Log::debug('Limit: '.$limit);
                foreach($records as $data) {
                    Log::debug($data);
                    $sms = [
                        'secret' => $app_secret,
                        'event' => 'incoming_message',
                        'service_id' => $data['Message ID'],
                        'from_number' => $data['From'],
                        'from_number_e164' => $data['From'],
                        'to_number' => $data['To'],
                        'content' => $data['Message'],
                        'noreply' => true
                    ];

                    $request = new \Illuminate\Http\Request();

                    $request->replace($sms);
                    $app = Container::getInstance();
                    $sms_log = new SmsLogRepository($app);

                    $project_instance = new ProjectRepository($app);

                    $user = User::whereUsername('telerivet')->firstOrFail();

                    $sms_api = new SmsAPIController($sms_log,$project_instance);
                    $sms_api->telerivet($request, $user);
                }

                if(($total - $gap) < 1000){
                    break;
                }
                $gap = $gap + 1000;
            }

            $offset++;
            $count--;
        }

    }
}
