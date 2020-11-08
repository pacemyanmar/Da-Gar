<?php

namespace App\Console\Commands;

use Akaunting\Setting\Facade as Settings;
use App\Http\Controllers\API\SmsAPIController;
use App\Models\Project;
use App\Models\SurveyResult;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Repositories\SmsLogRepository;
use App\Traits\LogicalCheckTrait;
use Carbon\Carbon;
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

    private $sample;

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

        $reader = Reader::createFromPath($file, 'r');
        $reader->setHeaderOffset(0);
        $count = count($reader);
        $total = $count;
        Log::debug('Total: '. $total);
        $increment = 0;
        $offset = 0;
        $limit = 100;
        $gap = 100;
        while($count){
            $last = ($total - $gap) ;
//            Log::debug('Last:'. $last);
//            Log::debug('count:' .$count);

            if($last === $count) {
                if($limit > $count){
                    $limit = $count;
                }

                $stmt = (new Statement())
                    ->offset($offset)
                    ->limit($limit);
                $records = $stmt->process($reader);
                $this->importData($project, $records);

                Log::debug('Previous gap: '. $gap);
                $gap = $gap + $limit;
                $offset = $offset + $increment;
                Log::debug('Current Offset: '. $offset);
                Log::debug('New Gap: '.$gap);
                log::debug('Records: '. count($records));
                Log::debug('Limit: '.$limit);
                Log::debug('Count : '.$count);
            }

            if( $gap > $total ) {
                //$this->importData($project, $records);
//                Log::debug('Last Offset: '. $offset);
//                Log::debug('Last Count : '. $last);
//                Log::debug('Gap: '.$gap);
//                log::debug('Records: '. count($records));
//                Log::debug('Limit: '.$limit);
//                Log::debug('Count : '.$count);
                break;
            }

            $increment++;
            $count--;

        }
    }

    protected function importData($project,$records)
    {
        $dbname = $project->dbname;
        foreach($records as $data) {
            foreach($project->sections as $section) {
                $section_no = $section->sort + 1;
                $section_table = $dbname . '_s' . $section->sort;

                $sample = $project->samplesList->where('project_id', $project->id)->where('sample_data_id', $data['psid'])->where('form_id', 1)->where('frequency', 1) ->first();

                $sample->setRelatedTable($section_table);
                $data_columns = [
                    "sample_type" => 1,
                    "section".$section->sort."status" => (!empty($data['status'.$section_no]))?$data['status'.$section_no]:0,
                    "user_id" => 1
                ];
                $unique_inputs = $section->inputs->pluck('inputid')->unique();
                $datamapped = [];
                array_walk($data, function($value,$key) use (&$datamapped) {
                    $mapped_key = preg_replace('/[^0-9a-zA-Z]+/','', $key);
                    if(preg_match('/[^0-9]$/', $mapped_key)) {
                        $mapped_key = $mapped_key.'r';
                    }
                    $datamapped[$mapped_key] = $value;
                });
                //Log::debug($datamapped);
                $input_array = array_flip($unique_inputs->toArray());
                //Log::debug($input_array);
                $results = [];

                array_walk($input_array, function(&$value,  $key) use ($datamapped) {
                    $value = (array_key_exists($key, $datamapped))?$datamapped[$key]:"";
                });

                $final_results = array_merge($data_columns, $input_array);
                //Log::debug($final_results);
                $sample->channel_time = Carbon::now();
                $sample->channel = 'sms';
                $sample->save();



                $surveyResult = $sample->resultWithTable()->first();

                if (empty($surveyResult)) {

                    $surveyResult = new SurveyResult();

                }
                $surveyResult->setTable($section_table);
                $surveyResult->sample()->associate($sample);
                $surveyResult->forceFill(array_filter($final_results));

                $surveyResult->save();
            }

        }
    }
}
