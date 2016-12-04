<?php

namespace App\Http\Controllers;

use App\DataTables\SurveyResultDataTable;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Repositories\SurveyResultRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

    public function index($project_id, $samplable = null, SurveyResultDataTable $resultDataTable = null)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if ($resultDataTable instanceof SurveyResultDataTable) {
            $table = $resultDataTable;
        } else if ($samplable instanceof SurveyResultDataTable) {
            $table = $samplable;
        } else {
            $table = null;
            return redirect()->back()->withErrors('No datatable object found!');
        }
        $table->forProject($project);
        $table->setJoinMethod('leftjoin');
        if (!empty($samplable) && !$samplable instanceof SurveyResultDataTable) {
            $table->setSurveyType($samplable);
            if ($samplable == 'voter') {
                $columns = [
                    'id' => ['name' => 'id', 'data' => 'id', 'title' => 'Voter ID'],
                    'name' => ['name' => 'name', 'data' => 'name', 'title' => 'Name'],
                    //'section' => ['name' => 'section', 'data' => 'section', 'title' => 'Section'],
                ];
            }
            $input_columns = [];
            foreach ($project->inputs as $k => $input) {
                $column = camel_case($input->name);
                $input_columns[$column] = ['name' => $column, 'data' => $column, 'title' => $column];
                if ($k > 5) {
                    $input_columns[$column]['visible'] = false;
                }

            }

            ksort($input_columns, SORT_NATURAL);
            $columns = array_merge($columns, $input_columns);

            $table->setColumns($columns);
        }
        return $table->render('projects.' . $project->dblink . '.' . $project->type . '.index', compact('project'));
    }

    /**
     * [create results for project]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable  [sample id from route parameter]
     * @param  string $type       [sample or datalink type]
     * @return Illuminate\View\View         [view for result creation]
     */
    public function create($project_id, $samplable, $type = '')
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        // find out which repository to use based on $type
        $repository = $type . 'Repository';

        // check repository exists or defined
        if (property_exists($this, $repository)) {
            $sample = $this->$repository->findWithoutFail($samplable);
        } else {
            // if no repository exists, $sample should be empty array
            $sample = [];
        }

        $questions = $project->questions;

        return view('projects.' . $project->dblink . '.create')
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
    public function save($project_id, $samplable, Request $request)
    {
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
        $repository = $dblink . 'Repository';

        // check dblink repository exists or defined
        if (property_exists($this, $repository)) {
            $sample_dblink = $this->$repository->findWithoutFail($samplable);
        } else {
            // if no repository exists, $sample should be empty array
            $sample_dblink = [];
        }

        // get all result array from form
        $results = $request->only('result')['result'];
        if (empty($results)) {
            return json_encode(['status' => 'error', 'message' => 'No result submitted!']);
        }

        // sample (country|region|1|2)
        $sample = (!empty($request->only('samplable_type')['samplable_type'])) ? $request->only('samplable_type')['samplable_type'] : '';

        // for each result row
        $each = [
            'project_id' => $project->id,
            'samplable_id' => $sample_dblink->id,
            'samplable_type' => $project->dblink,
            'sample' => $sample,
        ];

        // get dblink column count and listing
        $dblinkColumns = Schema::getColumnListing($project->dblink);
        if (($key = array_search('id', $dblinkColumns)) !== false) {
            unset($dblinkColumns[$key]);
        }

        $datacolumn = ['data_one', 'data_two', 'data_three', 'data_four', 'data_five', 'data_six', 'data_seven', 'data_eight', 'data_nine', 'data_ten'];
        foreach ($dblinkColumns as $k => $column) {
            if (property_exists($sample_dblink->$column)) {
                $each[$datacolumn[$k]] = $sample_dblink->$column;
            }
        }

        $getQuestion = function ($key) use ($each, $results, $sample) {
            $inputRow = $this->surveyInputRepo->findWithoutFail($key);
            $inputid = isset($inputRow->name) ? $inputRow->name : '';
            $qsort = isset($inputRow->question->sort) ? $inputRow->question->sort : '';
            $section = isset($inputRow->question->section) ? $inputRow->question->section : 0;
            $isort = isset($inputRow->sort) ? $inputRow->sort : '';
            $qnumSort = ['inputid' => $inputid, 'sort' => $qsort . $isort, 'value' => $results[$key], 'survey_input_id' => $key, 'section' => $section, 'sample' => $sample];
            $result = array_merge($qnumSort, $each);

            $result = $this->surveyResultRepo->updateOrCreate([
                'project_id' => $result['project_id'],
                'samplable_id' => $result['samplable_id'],
                'samplable_type' => $result['samplable_type'],
                'survey_input_id' => $result['survey_input_id'],
                'sample' => $sample,
            ], $result);
            return $result;
        };

        $qnumSort = array_map($getQuestion, array_keys($results));
        return json_encode(['status' => 'success', 'message' => 'Saved!', 'data' => $qnumSort]);
    }

    public function show($project_id, $voter_id)
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}
