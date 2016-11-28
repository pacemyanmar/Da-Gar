<?php

namespace App\Http\Controllers;

use App\DataTables\Scopes\SurveyResultByProjectScope;
use App\DataTables\SurveyResultDataTable;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Repositories\SurveyResultRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectResultsController extends Controller
{
	/** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $voterRepository;

    private $surveyResultRepo;

    private $surveyInputRepo;

    public function __construct(ProjectRepository $projectRepo, VoterRepository $voterRepo, QuestionRepository $questionRepo, SurveyResultRepository $surveyResultRepo, SurveyInputRepository $surveyInputRepo)
    {
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->voterRepository = $voterRepo;
        $this->surveyResultRepo = $surveyResultRepo;
        $this->surveyInputRepo = $surveyInputRepo;
    }

    public function index($project_id, $samplable = null, SurveyResultDataTable $resultDataTable = null) {
            $project = $this->projectRepository->findWithoutFail($project_id);
            $resultDataTable = ($resultDataTable)?$resultDataTable:($samplable instanceof SurveyResultDataTable)?$samplable:null;
            return $resultDataTable
                    ->forProject($project)
                    ->render('projects.'.$project->dblink.'.'.$project->type.'.index', compact('project'));
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

		return view('projects.'.$project->dblink.'.create')
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
        /**
         * 'value',
        'qnum',
        'sort',
        'samplable_id',
        'samplable_type',
        'survey_input_id',
        'project_id'
         */
        // find out which repository to use based on $dblink
        // voter | location | enumerator
        $dblink = strtolower($project->dblink);
        $repository = $dblink.'Repository';

        // check repository exists or defined
        if(property_exists($this, $repository)) {
            $sample = $this->$repository->findWithoutFail($samplable);
        } else {
            // if no repository exists, $sample should be empty array
            $sample = [];
        }

        $results = $request->only('result')['result'];
        if(empty($results)) return json_encode(['status' => 'error', 'message' => 'No result submitted!']);
        
        $samplable_type = (empty($request->only('samplable_type')['samplable_type']))? $project->dblink: $request->only('samplable_type')['samplable_type'];
        $each = [
                    'project_id' => $project->id,
                    'samplable_id' => $sample->id,
                    'samplable_type' => $samplable_type,
                    'samplable_data' => $sample
                ];

        $getQuestion = function($key) use ($each, $results) {
            $inputRow = $this->surveyInputRepo->findWithoutFail($key);
            $inputid = isset($inputRow->inputid)?$inputRow->inputid:'';
            $qsort = isset($inputRow->question->sort)?$inputRow->question->sort:'';
            $isort = isset($inputRow->sort)?$inputRow->sort:'';
            $qnumSort = ['inputid' => $inputid, 'sort' => $qsort.$isort, 'value' => $results[$key], 'survey_input_id' => $key];
            $result = array_merge($qnumSort, $each);

            $result = $this->surveyResultRepo->updateOrCreate([
                                                    'project_id' => $result['project_id'],
                                                    'samplable_id' => $result['samplable_id'],
                                                    'samplable_type' => $result['samplable_type'],
                                                    'survey_input_id' => $result['survey_input_id']
                                                    ], $result);
            return $result;
        };

        $qnumSort = array_map($getQuestion, array_keys($results));
        return json_encode(['status' => 'success', 'message' => 'Saved!', 'data' => $qnumSort]);
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
