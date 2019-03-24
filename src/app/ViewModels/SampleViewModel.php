<?php

namespace App\ViewModels;

use App\Models\Project;
use App\Models\Sample;
use Spatie\ViewModels\ViewModel;

class SampleViewModel extends ViewModel
{
    protected $project;

    protected $sample;

    public function __construct(Project $project, Sample $sample)
    {
        $this->project = $project;

        $this->sample = $sample;
    }

    public function sampleJson()
    {
        return $this->sample->all();
    }
    public function sampleJsonPaginate()
    {
        return $this->sample
            ->with('project')
            ->with('details')
            ->with('createdBy')
            ->with('updatedBy')
            ->with('checkedBy')
            ->with('results')
            ->where('project_id', $this->project->id)
            ->paginate();
    }
}
