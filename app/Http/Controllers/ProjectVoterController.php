<?php

namespace App\Http\Controllers;

use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;

class ProjectVoterController extends Controller
{
	/** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $voterRepository;


    public function __construct(ProjectRepository $projectRepo, VoterRepository $voterRepo, QuestionRepository $questionRepo)
    {
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->voterRepository = $voterRepo;
    }

    public function index() {

    }

    public function create($project_id, $voter_id) {
    	$project = $this->projectRepository->findWithoutFail($project_id);
    	$voter = $this->voterRepository->findWithoutFail($voter_id);
    	$questions = $project->questions;

		return view('projects.datalink.l2p.create')
				->with('project', $project)
				->with('questions', $questions)
				->with('voter', $voter);
    }

    public function save() {

    }

    public function show($project_id, $voter_id) {

    }

    public function edit() {

    }

    public function update() {

    }

    public function delete() {

    }
}
