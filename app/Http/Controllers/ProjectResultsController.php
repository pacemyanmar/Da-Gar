<?php

namespace App\Http\Controllers;

use App\DataTables\SurveyResultDataTable;
use App\Models\SurveyResult;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;

class ProjectResultsController extends Controller
{
    /** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $voterRepository;

    private $surveyResultModel;

    private $surveyInputRepo;

    public function __construct(ProjectRepository $projectRepo, VoterRepository $voterRepo, QuestionRepository $questionRepo, SurveyInputRepository $surveyInputRepo)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->voterRepository = $voterRepo;
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
            return redirect()->back()->withErrors('No datatable instance found!');
        }

        $table->forProject($project);

        if ($project->type == 'sample2db') {
            $table->setJoinMethod('join');
        } else {
            $table->setJoinMethod('leftjoin');
        }

        if (!$samplable instanceof SurveyResultDataTable) {
            $table->setSampleType($samplable);
        }

        $columns = [
            'id' => ['name' => 'id', 'data' => 'id', 'title' => 'ID'],
        ];
        if ($project->index_columns) {
            foreach ($project->index_columns as $column => $name) {
                $columns[$column] = [
                    'name' => $column,
                    'data' => $column,
                    'title' => ucfirst($name),
                    'orderable' => false,
                ];
            }
        } else {
            if ($project->dblink == 'voter') {
                $columns = [
                    'id' => ['name' => 'id', 'data' => 'id', 'title' => 'Voter ID'],
                    'name' => ['name' => 'name', 'data' => 'name', 'title' => 'Name'],
                    'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id', 'title' => 'NRC ID'],
                ];
            }
        }

        $baseColumns = $columns;

        $table->setBaseColumns($baseColumns);

        foreach ($project->sections as $k => $section) {
            $sectionColumn = 'section' . ($k + 1) . 'status';
            $columns[$sectionColumn] = [
                'name' => $sectionColumn,
                'data' => $sectionColumn,
                'title' => ucfirst($section['sectionname']),
            ];
        }

        $input_columns = [];
        foreach ($project->inputs as $k => $input) {
            $column = $input->inputid;
            $input_columns[$column] = ['name' => $column, 'data' => $column, 'title' => $column];
            if (!$input->in_index) {
                $input_columns[$column]['visible'] = false;
            }

        }

        ksort($input_columns, SORT_NATURAL);
        $columns = array_merge($columns, $input_columns);

        $table->setColumns($columns);
        return $table->render('projects.survey.' . $project->type . '.index', compact('project'));
    }

    /**
     * [create results for project]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable  [sample id from route parameter]
     * @param  string $type       [sample type]
     * @return Illuminate\View\View         [view for result creation]
     */
    public function create($project_id, $samplable, $type = '')
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if ($project->status == 'new') {
            Flash::warning("Project need to build to show '$project->project' form.");

            return redirect(route('projects.index'));
        }

        $dblink = strtolower($project->dblink);

        // find out which repository to use based on $dblink
        // voter | location | enumerator
        $dblink = strtolower($project->dblink);
        $repository = $dblink . 'Repository';

        // check dblink repository exists or defined
        if (property_exists($this, $repository)) {
            $sample = $this->$repository->findWithoutFail($samplable);
        } else {
            // if no repository exists, $sample should be empty array
            $sample = [];
        }

        $results = $sample->results($project->dbname)
            ->where('project_id', $project->id);
        if (!empty($type) && in_array($type, array_values($project->samples))) {
            $results = $results->where('sample', $type);
        }
        $results = $results->first();

        $questions = $project->questions()->onlyPublished()->get();

        return view('projects.survey.create')
            ->with('project', $project)
            ->with('questions', $questions)
            ->with('sample', $sample)
            ->with('results', $results);
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

        $dblink = strtolower($project->dblink);

        // find out which repository to use based on $dblink
        // voter | location | enumerator
        $dblink = strtolower($project->dblink);
        $repository = $dblink . 'Repository';

        // check dblink repository exists or defined
        if (property_exists($this, $repository)) {
            $sample = $this->$repository->findWithoutFail($samplable);
        } else {
            // if no repository exists, $sample should be empty array
            $sample = [];
        }

        $surveyResult = new SurveyResult();

        $surveyResult->setTable($project->dbname);

        // get all result array from form
        $results = $request->only('result')['result'];

        //$results['samplable_id'] = $dblink;
        //$results['samplable_id'] = $sample_dblink->id;

        if (empty($results)) {
            return json_encode(['status' => 'error', 'message' => 'No result submitted!']);
        }

        // sample (country|region|1|2)
        $results['sample'] = (!empty($request->only('samplable_type')['samplable_type'])) ? $request->only('samplable_type')['samplable_type'] : '';

        $results['user_id'] = Auth::user()->id;
        $results['project_id'] = $project->id;

        $old_result = $sample->results($project->dbname)
            ->where('project_id', $project->id);

        if (!empty($type) && in_array($type, array_values($project->samples))) {
            $old_result = $old_result->where('sample', $type);
        }
        $old_result = $old_result->first();

        if (!empty($old_result)) {
            $old_result->setTable($project->dbname);
            $old_result->fill($results);

            $result = $old_result->save($results);
        } else {
            $surveyResult->fill($results);

            $result = $sample->results($project->dbname)->save($surveyResult);
        }

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
