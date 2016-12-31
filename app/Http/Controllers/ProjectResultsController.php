<?php

namespace App\Http\Controllers;

use App\DataTables\SurveyResultDataTable;
use App\Models\SurveyResult;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SampleRepository;
use App\Repositories\SurveyInputRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;

class ProjectResultsController extends Controller
{
    /** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $surveyResultModel;

    private $surveyInputRepo;

    private $sampleRepository;

    public function __construct(ProjectRepository $projectRepo, QuestionRepository $questionRepo, SurveyInputRepository $surveyInputRepo, SampleRepository $sampleRepo)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->surveyInputRepo = $surveyInputRepo;
        $this->sampleRepository = $sampleRepo;
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
        ];

        if ($project->index_columns) {
            foreach ($project->index_columns as $column => $name) {
                switch ($column) {
                    case 'user_id':
                        $columns[$column] = [
                            'name' => 'user.name',
                            'data' => 'username',
                            'title' => ucfirst($name),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                        ];
                        break;
                    case 'name':
                        $columns[$column] = [
                            'name' => 'sample_datas.name',
                            'data' => 'name',
                            'title' => ucfirst($name),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                        ];
                        break;

                    default:
                        $columns[$column] = [
                            'name' => $column,
                            'data' => $column,
                            'title' => ucfirst($name),
                            'orderable' => false,
                        ];
                        break;
                }

            }
        } else {
            switch ($project->dblink) {
                case 'voter':
                    $columns = [
                        'idcode' => ['name' => 'idcode', 'data' => 'idcode', 'title' => 'Voter ID'],
                        'name' => ['name' => 'name', 'data' => 'name', 'title' => 'Name'],
                        'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id', 'title' => 'NRC ID'],
                    ];
                    break;

                case 'enumerator':
                    $columns = [
                        'idcode' => ['name' => 'idcode', 'data' => 'idcode', 'title' => 'Code'],
                        'form_id' => ['name' => 'form_id', 'data' => 'form_id', 'title' => 'Form No.'],
                        'name' => ['name' => 'name', 'data' => 'name', 'title' => 'Name'],
                        'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id', 'title' => 'NRC ID'],
                    ];

                default:
                    $columns = [
                        'idcode' => ['name' => 'idcode', 'data' => 'idcode', 'title' => 'Code'],
                        'form_id' => ['name' => 'form_id', 'data' => 'form_id', 'title' => 'Form No.'],
                    ];
                    break;
            }
        }

        $baseColumns = $columns;

        $table->setBaseColumns($baseColumns);
        $section_columns = [];
        foreach ($project->sections as $k => $section) {
            $sectionColumn = 'section' . ($k + 1) . 'status';
            $section_columns[$sectionColumn] = [
                'name' => $sectionColumn,
                'data' => $sectionColumn,
                'render' => function () {
                    return "function(data,type,full,meta){
                        var html;
                        if(data == 1) {
                            html = '<img src=\'" . asset('images/complete.png') . "\'>';
                        } else if(data == 2) {
                            html = '<img src=\'" . asset('images/incomplete.png') . "\'>';
                        } else if(data == 3) {
                            html = '<img src=\'" . asset('images/error.png') . "\'>';
                        } else {
                            html = '<img src=\'" . asset('images/missing.png') . "\'>';
                        }
                        return html;
                    }";
                },
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

        if ($project->status != 'new') {
            ksort($input_columns, SORT_NATURAL);
            $columns = array_merge($columns, $section_columns, $input_columns);
        }

        $table->setColumns($columns);
        return $table->render('projects.survey.' . $project->type . '.index', compact('project'));
    }

    /**
     * [create results for project]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable  [sample id from route parameter]
     * @param  string $form_id      [sample form id]
     * @return Illuminate\View\View         [view for result creation]
     */
    public function create($project_id, $samplable, $form_id = '', $type = '')
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if ($project->status == 'new') {
            Flash::warning("Project need to build to show '$project->project' form.");

            return redirect(route('projects.index'));
        }

        // find out which repository to use based on $dblink
        // voter | location | enumerator
        $dblink = strtolower($project->dblink);

        $sample = $this->sampleRepository->findWithoutFail($samplable);

        if (empty($sample)) {
            Flash::error("No sample database found.");
            return redirect(route('projects.index'));
        }
        $dbname = $project->dbname;
        $result = $sample->resultWithTable($dbname)->first();

        $questions = $project->questions()->onlyPublished()->get();

        $view = view('projects.survey.create')
            ->with('project', $project)
            ->with('questions', $questions)
            ->with('sample', $sample);

        if (!empty($result)) {
            $view->with('results', $result);
        }

        return $view;
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
        $sample = $this->sampleRepository->findWithoutFail($samplable);

        $surveyResult = new SurveyResult();

        $surveyResult->setTable($project->dbname);

        $sample_type = $request->only('sample');

        // get all result array from form
        $results = $request->only('result')['result'];

        if (empty($results)) {
            return json_encode(['status' => 'error', 'message' => 'No result submitted!']);
        }
        $results['sample'] = (isset($sample_type['sample'])) ? $sample_type['sample'] : 1;
        //$results['samplable_id'] = $dblink;
        //$results['samplable_id'] = $sample_dblink->id;
        $sectionstatus = [];
        foreach ($project->inputs->groupBy('section') as $section => $section_inputs) {
            $section_status = $section_inputs->where('optional', 0)->pluck('name', 'inputid')->toArray();

            // interset to find empty section
            $interset_status = array_intersect_key($section_status, $results);

            if (!empty($interset_status)) {

                $section_key = $section + 1;
                // check if individual question is complete
                foreach ($section_inputs->groupBy('question_id') as $question => $question_inputs) {
                    $inputs = $question_inputs->where('optional', 0)->pluck('name', 'inputid')->toArray();
                    $interset_inputs = array_intersect_key($inputs, $results);
                    $max = count($inputs);
                    $min = count(array_flip($inputs));
                    $actual = count($interset_inputs);
                    if (($actual >= $min && $actual <= $max)) {
                        $q[] = 0;
                    } else {
                        $q[] = 1;
                    }
                }

                $qsum = array_sum($q);
                if ($qsum) {
                    $results['section' . $section_key . 'status'] = 2;
                } else {
                    $results['section' . $section_key . 'status'] = 1;
                }
            }

        }

        // sample (country|region|1|2)
        //$results['sample'] = (!empty($request->only('samplable_type')['samplable_type'])) ? $request->only('samplable_type')['samplable_type'] : '';

        $auth_user = Auth::user()->id;
        //$results['project_id'] = $project->id;

        if (!empty($sample->user_id) && $sample->user_id != $auth_user) {
            $sample->update_user_id = $auth_user;
        } else {
            $sample->user_id = $auth_user;
        }

        $results['user_id'] = $auth_user;

        $old_result = $sample->resultWithTable($project->dbname);

        $old_result = $old_result->first();

        if (!empty($old_result)) {
            $old_result->setTable($project->dbname);
            $old_result->fill($results);

            $result = $old_result->save($results);
        } else {
            $surveyResult->fill($results);

            $result = $sample->resultWithTable($project->dbname)->save($surveyResult);
        }
        $sample->save(); // update Sample::class

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
