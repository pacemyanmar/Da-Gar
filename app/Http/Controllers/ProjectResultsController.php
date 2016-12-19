<?php

namespace App\Http\Controllers;

use App\DataTables\SurveyResultDataTable;
use App\Models\SurveyResult;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;

class ProjectResultsController extends Controller
{
    /** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $voterRepository;

    private $surveyResultModel;

    private $surveyInputRepo;

    public function __construct(ProjectRepository $projectRepo, VoterRepository $voterRepo, QuestionRepository $questionRepo, SurveyResult $surveyResultModel, SurveyInputRepository $surveyInputRepo)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->voterRepository = $voterRepo;
        $this->surveyResultModel = $surveyResultModel;
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
                $baseColumns = $columns = [
                    'id' => ['name' => 'id', 'data' => 'id', 'title' => 'Voter ID'],
                    'name' => ['name' => 'name', 'data' => 'name', 'title' => 'Name'],
                    'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id', 'title' => 'NRC ID'],
                ];
            }
            $table->setBaseColumns($baseColumns);
            $input_columns = [];
            foreach ($project->inputs as $k => $input) {
                $column = $input->name;
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
        //dd($results);

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
        $fillableColumns = $sample_dblink->getFillable();
        // Another option is to get all columns for the table like so:
        // $columns = \Schema::getColumnListing($this->table);
        // but it's safer to just get the fillable fields

        $dblinkColumns = $sample_dblink->getAttributes();

        foreach ($fillableColumns as $column) {
            if (!array_key_exists($column, $dblinkColumns)) {
                $dblinkColumns[$column] = null;
            }
        }

        if (array_key_exists('id', $dblinkColumns)) {
            // set id to $dblink underscore id (e.g: voter_id)
            $dblinkColumns[$dblink . '_id'] = $dblinkColumns['id'];
            // remove incremental id column from array
            unset($dblinkColumns['id']);
        }

        $flatResultToMongo = array_merge($each, $dblinkColumns, $results);
        $result = $this->surveyResultModel
            ->where('project_id', $flatResultToMongo['project_id'])
            ->where('samplable_id', $flatResultToMongo['samplable_id'])
            ->where('samplable_type', $flatResultToMongo['samplable_type'])
            ->where('sample', $sample)
            ->update($flatResultToMongo, ['upsert' => true]);

        //$qnumSort = array_map($getQuestion, array_keys($results));
        return json_encode(['status' => 'success', 'message' => 'Saved!', 'data' => $result]);
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
