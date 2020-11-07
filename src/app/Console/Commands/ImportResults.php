<?php

namespace App\Console\Commands;

use Akaunting\Setting\Facade as Settings;
use App\Http\Controllers\API\SmsAPIController;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Repositories\SmsLogRepository;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import reported data from CSV';

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
