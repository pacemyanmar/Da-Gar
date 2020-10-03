<?php

namespace App\ViewModels;

use App\Models\Project;
use App\Models\Sample;
use Spatie\ViewModels\ViewModel;

class SampleViewModel extends ViewModel
{
    protected $project;

    protected $samples;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->samples = $this->project->samplesList;
    }

    public function sampleJson()
    {
        return $this->sample->all();
    }
    public function sampleJsonPaginate()
    {
        return $this->samples
            ->with('project')
            ->with('details')
            ->with('createdBy')
            ->with('updatedBy')
            ->with('checkedBy')
            ->with('results')
            ->paginate();
    }
}
