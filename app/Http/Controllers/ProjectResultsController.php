<?php

namespace App\Http\Controllers;

use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyResultRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;

class ProjectResultsController extends Controller
{
	/** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $voterRepository;

    private $surveyResultRepo;


    public function __construct(ProjectRepository $projectRepo, VoterRepository $voterRepo, QuestionRepository $questionRepo, SurveyResultRepository $surveyResultRepo)
    {
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->voterRepository = $voterRepo;
        $this->surveyResultRepo = $surveyResultRepo;
    }

    public function index() {

    }
    
    /**
     * [create results for project]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable  [sample id from route parameter]
     * @param  string $type       [sample or datalink type]
     * @return Illuminate\View\View         [view for result creation]
     */
    public function create($project_id, $samplable, $type = '') {
    	$project = $this->projectRepository->findWithoutFail($project_id);
        
        // find out which repository to use based on $type
        $repository = $type.'Repository';

        // check repository exists or defined
        if(property_exists($this, $repository)) {
            $sample = $this->$repository->findWithoutFail($samplable);
        } else {
            // if no repository exists, $sample should be empty array
            $sample = [];
        }
        
    	$questions = $project->questions;

		return view('projects.datalink.'.$project->type.'.create')
				->with('project', $project)
				->with('questions', $questions)
				->with('sample', $sample);
    }

    /**
     * [save results]
     * @param  integer  $project_id [current project id from route parameter]
     * @param  integer|string $samplable  [sample id from route parameter]
     * @param  Request $request    [form input]
     * @return string              [json string]
     */
    public function save($project_id, $samplable, Request $request) {
        $project = $this->projectRepository->findWithoutFail($project_id);
        $questions = $project->questions;
    	return json_encode($request->all());
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
