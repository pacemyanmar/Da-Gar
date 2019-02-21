<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Sample;
use App\Models\SampleData;
use Illuminate\Bus\Queueable;

class GenerateSample
{
    use Queueable;

    private $project;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Sample $sampleInstance, SampleData $sampleData)
    {
        // SampleData is detail informations about sample
        $samplables = $sampleData->setTable($this->project->dbname.'_samples')->get();

        foreach ($samplables as $data) {
            $samples = [];
            for($f = 1; $f <= $this->project->frequencies; $f++) {
                for ($i = 1; $i <= $this->project->copies; $i++) {
                    //$form_id = sprintf("%02d", $i);
                    //$samples[] = new Sample(['form_id' => $i, 'project_id' => $this->project->id, 'sample_data_type' => $this->project->dblink]);
                    if (!empty($data->id))
                        $sample = $sampleInstance->firstOrNew(['sample_data_id' => (int)$data->id, 'form_id' => $i, 'frequency' => $f, 'project_id' => $this->project->id, 'sample_data_type' => $this->project->type]);
                    $samples[] = $sample;
                }
            }

            // samples() is link between project and sampleData
            // $data is single row from sampleData
            //$data->samples()->delete();
            $data->setTable($this->project->dbname.'_samples')->samples()->saveMany($samples);
        }

    }
}
