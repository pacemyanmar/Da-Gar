<?php

namespace App\Http\Controllers;

use App\DataTables\DoubleResponseDataTable;
use App\DataTables\SampleResponseDataTable;
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
    private $saveSample;
    private $saveSampleType;
    private $saveResults;
    private $sampleId;

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

        // To Do: all following codes need to remove
        $statesCollections = $project->samplesData->groupBy('level1');
        $locations['allStates'] = $project->samplesData->pluck('level1')->unique();
        $locations['allDistricts'] = $project->samplesData->pluck('level2')->unique();
        $locations['allTownships'] = $project->samplesData->pluck('level3')->unique();
        $locations['allVillageTracts'] = $project->samplesData->pluck('level4')->unique();
        $locations['allVillages'] = $project->samplesData->pluck('level5')->unique();

        $districtsByState = [];
        $townshipByState = [];
        $vtractByState = [];
        $villageByState = [];

        foreach ($statesCollections as $state => $samplesData) {
            $locations['state'][$state]['district'] = $districtsByState[$state] = $samplesData->pluck('level2', 'level2')->toArray();
            $locations['state'][$state]['township'] = $townshipByState[$state] = $samplesData->pluck('level3', 'level3')->toArray();
            $locations['state'][$state]['village_tract'] = $vtractByState[$state] = $samplesData->pluck('level4', 'level4')->toArray();
            $locations['state'][$state]['village'] = $villageByState[$state] = $samplesData->pluck('level5', 'level5')->toArray();
        }

        $districtsCollections = $project->samplesData->groupBy('level2');

        $townshipByDistrict = [];
        $vtractByDistrict = [];
        $villageByDistrict = [];

        foreach ($districtsCollections as $district => $samplesData) {
            $locations['district'][$district]['township'] = $townshipByDistrict[$district] = $samplesData->pluck('level3', 'level3')->toArray();
            $locations['district'][$district]['village_tract'] = $vtractByDistrict[$district] = $samplesData->pluck('level4', 'level4')->toArray();
            $locations['district'][$district]['village'] = $villageByDistrict[$district] = $samplesData->pluck('level5', 'level5')->toArray();
        }

        $townshipsCollections = $project->samplesData->groupBy('level3');

        $vtractBytownship = [];
        $villageBytownship = [];

        foreach ($townshipsCollections as $township => $samplesData) {
            $locations['township'][$township]['village_tract'] = $vtractBytownship[$township] = $samplesData->pluck('level4', 'level4')->toArray();
            $locations['township'][$township]['village'] = $villageBytownship[$township] = $samplesData->pluck('level5', 'level5')->toArray();
        }

        $village_tractsCollections = $project->samplesData->groupBy('level4');

        $villageByvillage_tract = [];

        foreach ($village_tractsCollections as $village_tract => $samplesData) {
            $locations['village_tract'][$village_tract]['village'] = $villageByvillage_tract[$village_tract] = $samplesData->pluck('level4', 'level4')->toArray();
        }

        switch ($project->type) {
            case 'sample2db':
                $project_type = $project->type;
                break;

            default:
                $project_type = 'db2sample';
                break;
        }

        return $table->render('projects.survey.' . $project_type . '.index', compact('project'), compact('locations'));
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

        $this->sampleId = $sample->id;

        if (empty($sample)) {
            Flash::error("No sample database found.");
            return redirect(route('projects.index'));
        }

        $view = view('projects.survey.create')
            ->with('project', $project)
            ->with('sample', $sample);

        $dbname = $project->dbname;
        $results = [];
        $double_results = [];

        foreach ($project->sections as $k => $section) {
            $section_table = $dbname . '_s' . $section->sort;
            $results['section' . $section->sort] = $sample->resultWithTable($section_table)->first();

            $section_dbl_table = $section_table . '_dbl';
            $double_results['section' . $section->sort] = $sample->resultWithTable($section_dbl_table)->first();

        }


        $project->load(['questions' => function ($query) {
            $query->where('qstatus', 'published');
        }]);

        $project->load(['inputs' => function ($query) {
            $query->where('status', 'published')
                ->orderBy('sort', 'ASC');
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
            } else {
                $sample->update_user_id = $auth_user->id;
            }
        } else {
            $sample->user_id = $auth_user->id;
        }

        $this->saveSample = $sample;

        // get all sections in a project
        $sections = $project->sections->sortBy('sort');

        $submitted_section = $request->input('section_id');

        $section = Section::findOrFail($submitted_section);

        $result_arr = [];

        $section_result = [];

        $question_result = [];

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
        $allResults = [];
        foreach ($questions as $question) {
            $qid = $question->id;
            $inputs = $question->surveyInputs;

            $question_inputs = $question->surveyInputs->pluck('value', 'inputid');

            $question_has_result_submitted = array_intersect_key((array)$results, $question_inputs->toArray());


            if (count($question_has_result_submitted) > 0) {
                if (!array_key_exists($question->id, $question_result)) {
                    $question_result[$question->id] = true;
                }
            }

            foreach ($inputs as $input) {
                $inputid = $input->inputid;
                // $result = submitted form data
                // look for individual inputid in $result array submitted or not
                if (array_key_exists($input->inputid, $results)) {
                    // if found, question is summitted and set checkbox values to zero if false
                    if ($input->type == 'checkbox') {
                        $result_arr[$qid][$inputid] = ($results[$inputid]) ? $results[$inputid] : 0;
                    } else {
                        $result_arr[$qid][$inputid] = ($results[$inputid] !== null && $results[$inputid] !== false && $results[$inputid] !== '') ? $results[$inputid] : null;
                    }
                } else {

                    if ($input->type == 'checkbox') {

                        if (count($question_has_result_submitted) > 0) {
                            $result_arr[$qid][$inputid] = 0;
                        } else {
                            $result_arr[$qid][$inputid] = null;
                        }

                    } else {
                        $result_arr[$qid][$inputid] = null;
                    }

                }
                if($input->other) {
                    $result_arr[$qid][$inputid.'_other'] = (array_key_exists($inputid.'_other', $results))?$results[$inputid.'_other']:null;
                }

                $this->logicalCheck($input, $result_arr[$qid][$inputid]);
            }

            if(array_key_exists($question->qnum, $this->errorBag)) {
                $this->getQuestionStatus($this->errorBag[$question->qnum], $question->qnum);
            }

            $allResults += $result_arr[$qid];
        }


        $section_table = $dbName . '_s' . $section->sort;

        $this->saveSampleType = (isset($sample_type)) ? $sample_type : 1;

        $this->saveResults = $allResults;

        $originTable = $section_table;

        $doubleTable = $section_table . '_dbl';

        $this->saveSection = 'section' . $section->sort . 'status';


        if (Auth::user()->role->role_name == 'doublechecker') {
            $this->saveResults($doubleTable);
        } else {
            $this->saveResults($originTable);
            if ($request->has('double')) {
                $this->saveResults($doubleTable);
            }
        }

        return $this->sendResponse($allResults, trans('messages.saved'));
    }

    /**
     * private $originTable;
     *
     * private $doubleTable;
     *
     * private $saveSample;
     *
     * private $saveResults;
     */

    private function saveResults($table)
    {
        $sample = $this->saveSample;

        $sample->setRelatedTable($table);

        $surveyResult = $sample->resultWithTable()->first();

        if (empty($surveyResult)) {

            $surveyResult = new SurveyResult();

        }

        $surveyResult->setTable($table);

        $surveyResult->sample()->associate($this->saveSample);

        $surveyResult->{$this->saveSection} = $this->getSectionStatus();

        $surveyResult->sample_type = $this->saveSampleType;

        $surveyResult->user_id = Auth()->user()->id;

        $surveyResult->forceFill($this->saveResults);

        $result = $surveyResult->save();
    }


    public function responseRateSample($project_id, $filter, SampleResponseDataTable $sampleResponse, Request $request)
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

        $section_num = $request->input('section');

        if ($section_num) {
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

        $sample_query = 'project_id, count(project_id) as total, SUM(IF(' . $project->dbname . '.sample_id IS NOT NULL,1,0)) AS reported';

        foreach ($project->inputs as $input) {
            if ($input->value) {
                $sample_query .= ' , SUM(IF(' . $input->inputid . '=' . $input->value . ',1,0)) AS ' . $input->inputid . '_' . $input->value . ' , SUM(IF(' . $input->inputid . ' IS NULL,1,0)) AS q' . $input->question->qnum . '_none';
            }
        }
        $query = DB::table('samples')->select(DB::raw($sample_query));
        $query->where('project_id', $project->id);
        $query->leftjoin($project->dbname, $project->dbname . '.sample_id', '=', 'samples.id');
        $query->groupBy('project_id');
        $results_count = $query->first();

        return view('projects.analysis')
            ->with('project', $project)
            ->with('questions', $project->questions)
            ->with('results', $results_count);
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
