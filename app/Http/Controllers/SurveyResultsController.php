<?php

namespace App\Http\Controllers;

use App\DataTables\DoubleResponseDataTable;
use App\DataTables\SampleResponseDataTable;
use App\DataTables\Scopes\OrderByCode;
use App\DataTables\Scopes\OrderByFormId;
use App\DataTables\SurveyResultsDataTable;
use App\Models\SampleData;
use App\Models\Section;
use App\Models\SurveyResult;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SampleRepository;
use App\Repositories\SurveyInputRepository;
use App\Traits\LogicalCheckTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kanaung\Facades\Converter;
use Laracasts\Flash\Flash;

class SurveyResultsController extends AppBaseController
{
    use LogicalCheckTrait;

    protected $errorBag;

    /** @var  ProjectRepository */
    private $projectRepository;
    private $questionRepository;
    private $surveyInputRepo;
    private $sampleRepository;
    private $sampleDataModel;
    private $sample;
    private $sampleType;
    private $results;
    private $sampleId;
    private $project;


    public function __construct(ProjectRepository $projectRepo,
                                QuestionRepository $questionRepo,
                                SurveyInputRepository $surveyInputRepo,
                                SampleRepository $sampleRepo,
                                SampleData $sampleDataModel)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->surveyInputRepo = $surveyInputRepo;
        $this->sampleRepository = $sampleRepo;
        $this->sampleDataModel = $sampleDataModel;
        $this->channel = 'web';
    }

    public function index(SurveyResultsDataTable $resultDataTable, $project_id, $samplable = null)
    {
        // get project by id
        $project = $this->projectRepository->findWithoutFail($project_id);

        // get application current locale
        $locale = \App::getLocale();

        // get current authenticated user
        $auth = Auth::user();

        // check wheather project exists, return with error if not.
        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        // check project status, if not published, redirect to edit page to build form and publish.
        if ($project->status != 'published') {
            Flash::error('Project need to build form.');
            // change redirect page based on user role
            if ($auth->role->level > 5) {
                return redirect(route('projects.edit', [$project->id]));
            } else {
                return redirect(route('projects.index'));
            }
        }

        // check datatables instance and return error if not exist
        if ($resultDataTable instanceof SurveyResultsDataTable) {
            $table = $resultDataTable;
        } else {
            $table = null;
            return redirect()->back()->withErrors('No datatable instance found!');
        }

        // set project on datatables table
        $table->setProject($project);

        // set table join method based on project type
        if ($project->type == 'sample2db') {
            $table->setJoinMethod('join');
            $samplesData = config('sms.incident_columns');
        } else {
            $table->setJoinMethod('leftjoin');
            $samplesData = config('sms.export_columns');
        }

        // set sample type if not null
        if (null !== $samplable) {
            $table->setSampleType($samplable);
        }



        switch ($project->type) {
            case 'sample2db':
                $project_type = $project->type;
                break;

            default:
                $project_type = 'db2sample';
                break;
        }

        return $table->addScope(new OrderByCode())->addScope(new OrderByFormId())->render('projects.survey.' . $project_type . '.index', compact('project'), compact('locations'));
    }

    /**
     * [create results for project]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable [sample id from route parameter]
     * @param  string $form_id [sample form id]
     * @return Illuminate\View\View         [view for result creation]
     */
    public function create($project_id, $samplable, $form_id = '', Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        $auth = Auth::user();
        if ($project->status != 'published') {
            Flash::warning("Project need to build to show '$project->project' form.");

            return redirect(route('projects.index'));
        }

        // find out which repository to use based on $dblink
        // voter | location | enumerator
        $dblink = strtolower($project->dblink);

        $sample = $this->sampleRepository->findWithoutFail($samplable);

        $sample_data = $this->sampleDataModel->setTable($project->dbname.'_samples')->find($sample->sample_data_id);

        $this->sampleId = $sample->id;

        if (empty($sample)) {
            Flash::error("No sample database found.");
            return redirect(route('projects.index'));
        }

        $view = view('projects.survey.create')
            ->with('project', $project)
            ->with('sample', $sample)
            ->with('sample_data', $sample_data);

        $dbname = $project->dbname;
        $results = [];
        $double_results = [];

        foreach ($project->sections as $k => $section) {
            $section_table = $dbname . '_s' . $section->sort;
            $results['section' . $section->sort] = $sample->resultWithTable($section_table)->first();
            if(config('sms.double_entry')) {
                $section_dbl_table = $section_table . '_dbl';
                $double_results['section' . $section->sort] = $sample->resultWithTable($section_dbl_table)->first();
            }

        }


        $project->load(['questions' => function ($query) {
            $query->where('qstatus', 'published');
        }]);

        $project->load(['inputs' => function ($query) {
            $query->where('status', 'published')
                ->orderBy('sort', 'ASC');
        }]);

        $project->load(['locationMetas' => function($q){
            //$q->withTrashed();
            $q->orderBy('sort','ASC');
        }]);

        if (!empty($results)) {
            $view->with('results', $results);
        }
        if (!empty($double_results)) {
            $view->with('double_results', $double_results);
        }


        if (!empty($form_id) && $project->copies > 1) {
            $view->with('form', $form_id);
        }

        if ($request->has('double')) {
            $view->with('double', true);
        }

        return $view;
    }

    /**
     * [save results]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable [sample id from route parameter]
     * @param  Request $request [form input]
     * @return string              [json string]
     */
    public function save($project_id, $samplable, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if ($project->status != 'published') {
            Flash::warning("Project need to build to show '$project->project' form.");

            return redirect(route('projects.index'));
        }

        $this->project = $project;

        // get all result array from form
        $results = $request->input('result');

        if (empty($results)) {
            $results = (array)$results;
        }

        $sample = $this->sampleRepository->findWithoutFail($samplable);


        $auth_user = Auth::user();

        if (!empty($sample->user_id) && $sample->user_id != $auth_user->id) {
            if ($auth_user->role->role_name == 'doublechecker') {
                $sample->qc_user_id = $auth_user->id;
            }
        } else {
            $sample->user_id = $auth_user->id;
        }

        $this->sample = $sample;

        // get all sections in a project
        $sections = $project->sections->sortBy('sort');

        $submitted_section = $request->input('section_id');

        $section = Section::findOrFail($submitted_section);

        $this->section = $section;

        $section_result = [];

        $dbName = $project->dbname;

        $sample_type = $request->input('sample');


        $allResults = [];

        $section_inputs = $section->inputs->pluck('value', 'inputid');

        $section_has_result_submitted = array_intersect_key((array)$results, $section_inputs->toArray());

        if (count($section_has_result_submitted) > 0) {
            if (!array_key_exists($section->id, $section_result)) {
                $section_result[$section->id] = true;

                $allResults['section' . $section->sort . 'updated'] = Carbon::now();
            }
        }

        $questions = $section->questions;

        $allResults = $this->processUserInput($questions, $results);

        $section_table = $dbName . '_s' . $section->sort;

        $results_table = (Auth::user()->role->role_name == 'doublechecker' || $request->has('double'))? $section_table . '_dbl':$section_table;

        $this->sampleType = (isset($sample_type)) ? $sample_type : 1;

        $this->results = $allResults;

        $this->section = 'section' . $section->sort . 'status';

        $this->saveResults($results_table);

        // save sample to update latest input user
        $sample->save();

        $allResults['status'] = ['section'.$section->sort => $this->sectionStatus];
        return $this->sendResponse($allResults, trans('messages.saved'));
    }

    public function responseRateSample($project_id, $filter, $type='first', SampleResponseDataTable $sampleResponse, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }
        if ($project->status != 'published') {
            Flash::warning("Project need to build to show '$project->project' response rate.");

            return redirect(route('projects.index'));
        }

        $sampleResponse->setProject($project);

        $sampleResponse->setFilter($filter);
        switch ($project->type) {
            case 'sample2db':
                $project_type = $project->type;
                break;

            default:
                $project_type = 'db2sample';
                break;
        }

        if($type != 'first') {
            $sampleResponse->setType($type);
        }

        $section_num = $request->input('section');

        if ($section_num !== false) {
            $sampleResponse->setSection($section_num);
        }

        $filters = ['type' => $filter, 'section_num' => $section_num];

        return $sampleResponse->render('projects.survey.' . $project_type . '.response-sample', compact('project', $project), compact('filters', $filters));
    }

    public function responseRateDouble($project_id, DoubleResponseDataTable $doubleResponse)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        if ($project->status != 'published') {
            Flash::warning("Project need to build to show '$project->project' double response rate.");

            return redirect(route('projects.index'));
        }

        $settings = [
            'project_id' => $project->id,
        ];


        $doubleResponse->setProject($project);

        switch ($project->type) {
            case 'sample2db':
                $project_type = $project->type;
                break;

            default:
                $project_type = 'db2sample';
                break;
        }

        return $doubleResponse->render('projects.survey.' . $project_type . '.response-double', compact('settings', $settings));
    }

    public function originUse($project_id, $survey_id, $column, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            return $this->sendError(trans('messages.no_project'), $code = 404);
        }
        $sample = $this->sampleRepository->findWithoutFail($survey_id);

        if (empty($sample)) {
            return $this->sendError(trans('messages.no_project'), $code = 404);
        }

        $ori_table = $project->dbname;
        $dou_table = $ori_table . '_double';

        $ori_result = $sample->resultWithTable($ori_table)->first(); // used first() because of one to one relation

        if (empty($ori_result)) {
            return $this->sendError(trans('messages.no_result1'), $code = 404);
        }

        $dou_result = $sample->resultWithTable($dou_table)->first();

        if (empty($dou_result)) {
            return $this->sendError(trans('messages.no_result2'), $code = 404);
        }

        $dou_result->setTable($dou_table);
        $dou_result->{$column} = $ori_result->{$column};

        $dou_result->save();

        if ($dou_result) {
            return $this->sendResponse($dou_result, 'Data updated to second dataset!');
        }

        return $this->sendError(trans('messages.no_result_submitted'), $code = 404);
    }

    public function doubleUse($project_id, $survey_id, $column, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            return $this->sendError(trans('messages.no_project'), $code = 404);
        }

        $sample = $this->sampleRepository->findWithoutFail($survey_id);

        if (empty($sample)) {
            return $this->sendError(trans('messages.no_project'), $code = 404);
        }

        $ori_table = $project->dbname;
        $dou_table = $ori_table . '_double';
        $ori_result = $sample->resultWithTable($ori_table)->first(); // used first() because of one to one relation

        if (empty($ori_result)) {
            return $this->sendError(trans('messages.no_result1'), $code = 404);
        }

        $dou_result = $sample->resultWithTable($dou_table)->first();

        if (empty($dou_result)) {
            return $this->sendError(trans('messages.no_result2'), $code = 404);
        }

        $ori_result->setTable($ori_table);
        $ori_result->{$column} = $dou_result->{$column};

        $ori_result->save();

        if ($ori_result) {
            return $this->sendResponse($ori_result, 'Data updated to first dataset!');
        }

        return $this->sendError(trans('messages.no_result_submitted'), $code = 404);
    }

    public function analysis($project_id)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }
        $project->load(['inputs']);


        $sample_query = 'project_id, count(project_id) as total';
        $reported = [];
        foreach ($project->inputs as $input) {
            if (is_numeric($input->value)) {
                $sample_query .= ' , SUM(IF(' . $input->inputid . '=' . $input->value . ',1,0)) AS ' . $input->inputid . '_' . $input->value . ' ,';
                $sample_query .= 'SUM(IF(' . $input->inputid . ' IS NULL OR ' .$input->inputid. ' = 0,1,0)) AS q' . $input->question->qnum . '_none';
                $reported[$input->inputid] = 'SUM(IF(' . $input->inputid . ',1,0)) AS '.strtolower($input->question->qnum).'_reported';
            }
        }

        $reported = implode(' , ', $reported);


        $query = DB::table('samples')->select(DB::raw($sample_query), DB::raw($reported));
        $query->where('project_id', $project->id);
        foreach($project->sections as $section) {
            $query->leftjoin($project->dbname.'_s'.$section->sort. ' AS pj_s'.$section->sort, 'pj_s'.$section->sort.'.sample_id', '=', 'samples.id');
        }
        $query->groupBy('project_id');
        $results = $query->first();

        return view('projects.analysis')
            ->with('project', $project)
            ->with('questions', $project->questions)
            ->with('results', $results);
    }

    private function zawgyiUnicode(&$value, $key)
    {
        $mya_en = [
            '၀' => '0',
            '၁' => '1',
            '၂' => '2',
            '၃' => '3',
            '၄' => '4',
            '၅' => '5',
            '၆' => '6',
            '၇' => '7',
            '၈' => '8',
            '၉' => '9',
        ];
        if (is_string($value)) {
            $value = strtr($value, $mya_en);

            $value = Converter::convert($value, 'zawgyi', 'unicode');
        }
    }

    private function unicodeZawgyi(&$value, $key)
    {
        $value = Converter::convert($value, 'unicode', 'zawgyi');
    }

}
