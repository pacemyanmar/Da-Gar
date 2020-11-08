<?php

namespace App\Console\Commands;

use Akaunting\Setting\Facade as Settings;
use App\Http\Controllers\API\SmsAPIController;
use App\Models\Project;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Repositories\SmsLogRepository;
use App\Traits\LogicalCheckTrait;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportResults extends Command
{
    use LogicalCheckTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:import {code} {file}';

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

        $project = Project::where('unique_code', $this->argument('code'))->first();
        $dbname = $project->dbname;

        $reader = Reader::createFromPath($file, 'r');
        $reader->setHeaderOffset(0);
        $count = count($reader);
        $total = $count;
        Log::debug('Count: '. $count);
        $offset = 0;
        $limit = 1000;
        $gap = $limit;
        while($count){

            if($total < $limit) {
                $limit = $total;
            }

            if($offset === $gap) {
                Log::debug('Offset: '. $offset);
                Log::debug('Gap: '.$gap);
                Log::debug('total: '.$total);
                if(($total - $gap) < 1000){
                    $limit = $total - $gap;
                }

                if(($total - $gap) < 1000){
                    break;
                }
                $gap = $gap + 1000;
            }
            $stmt = (new Statement())
                ->offset($offset)
                ->limit($limit);
            $records = $stmt->process($reader);

            log::debug('Records: '. count($records));
            Log::debug('Limit: '.$limit);
            $results = [];
            foreach($records as $data) {
                $results[$data['psid']] = $data;

            }

            foreach($project->sections as $section) {
                $section_no = $section->sort + 1;
                $section_table = $dbname . '_s' . $this->section->sort;
                //dd($section->inputs->pluck('inputid')->unique());
                $savedResult = $this->saveResults($section_table);
            }


            $offset++;
            $count--;
        }
    }
}
