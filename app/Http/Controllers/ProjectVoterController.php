<?php

namespace App\Http\Controllers;

use App\Repositories\ProjectRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;

class ProjectVoterController extends Controller
{
	/** @var  ProjectRepository */
    private $projectRepository;

    private $voterRepository;

    public function __construct(ProjectRepository $projectRepo, VoterRepository $voterRepository)
    {
        $this->projectRepository = $projectRepo;
        $this->voterRepository = $voterRepository;
    }

    public function voterSurvey() {

    }

    public function createVoterSurveyResult() {

    }

    public function updateVoterSurveyResult() {

    }
}
