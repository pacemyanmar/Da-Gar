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
    public function handle(Sample $sampleInstance)
    {
        // SampleData is detail informations about sample
        $samplables = SampleData::where('type', $this->project->dblink)->where('dbgroup', $this->project->dbgroup)->get();
        foreach ($samplables as $sampleData) {
            $samples = [];
            for ($i = 1; $i <= $this->project->copies; $i++) {
                //$form_id = sprintf("%02d", $i);
                //$samples[] = new Sample(['form_id' => $i, 'project_id' => $this->project->id, 'sample_data_type' => $this->project->dblink]);
                $samples[] = $sampleInstance->firstOrNew(['sample_data_id' => $sampleData->id, 'form_id' => $i, 'project_id' => $this->project->id, 'sample_data_type' => $this->project->dblink]);
            }
            // samples() is link between project and sampleData
            // $sample is single row from sampleData
            //$sample->samples()->delete();
            $sampleData->samples()->saveMany($samples);
        }

    }
}
