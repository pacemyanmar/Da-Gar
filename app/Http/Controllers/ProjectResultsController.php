<?php

namespace App\Http\Controllers;

use App\DataTables\DoubleResponseDataTable;
use App\DataTables\SampleResponseDataTable;
use App\DataTables\SurveyResultDataTable;
use App\Http\Controllers\AppBaseController;
use App\Models\Sample;
use App\Models\SampleData;
use App\Models\SurveyResult;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SampleRepository;
use App\Repositories\SurveyInputRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kanaung\Facades\Converter;
use Laracasts\Flash\Flash;

class ProjectResultsController extends AppBaseController
{
    /** @var  ProjectRepository */
    private $projectRepository;

    private $questionRepository;

    private $surveyResultModel;

    private $surveyInputRepo;

    private $sampleRepository;

    private $sampleDataModel;

    public function __construct(ProjectRepository $projectRepo, QuestionRepository $questionRepo, SurveyInputRepository $surveyInputRepo, SampleRepository $sampleRepo, SampleData $sampleDataModel)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->surveyInputRepo = $surveyInputRepo;
        $this->sampleRepository = $sampleRepo;
        $this->sampleDataModel = $sampleDataModel;
    }

    public function index($project_id, $samplable = null, SurveyResultDataTable $resultDataTable = null)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }
        $auth = Auth::user();

        if ($project->status != 'published') {
            Flash::error('Project need to build form.');
            if ($auth->role->level > 5) {
                return redirect(route('projects.edit', [$project->id]));
            } else {
                return redirect(route('projects.index'));
            }
        }

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

        $dbname = $project->dbname;

        $columns = [
        ];

        $samplesData = SampleData::$export;

        if ($project->index_columns) {
            $sampleDataColumns = array_merge($samplesData, $project->index_columns);
            foreach ($sampleDataColumns as $column => $name) {
                switch ($column) {
                    case 'user_id':
                        $columns['user_id'] = [
                            'name' => 'user.name',
                            'data' => 'username',
                            'title' => trans('messages.userid'),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                            'width' => '80px',
                        ];
                        break;
                    case 'name':
                        $columns['name'] = [
                            'name' => 'sample_datas.name',
                            'data' => 'name',
                            'title' => trans('messages.name'),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                            'width' => '80px',
                        ];
                        break;
                    case 'idcode':
                        $columns['idcode'] = [
                            'name' => 'idcode',
                            'data' => 'idcode',
                            'title' => trans('messages.idcode'),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                            'width' => '80px',
                        ];
                        break;
                    case 'form_id':
                        $columns['form_id'] = [
                            'name' => 'form_id',
                            'data' => 'form_id',
                            'title' => trans('messages.form_id'),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                            'width' => '80px',
                        ];
                        break;
                    case 'mobile':
                        $columns['mobile'] = [
                            'name' => 'sample_datas.mobile',
                            'data' => 'mobile',
                            'title' => trans('messages.mobile'),
                            'orderable' => false,
                            'defaultContent' => 'N/A',
                            'width' => '120px',
                        ];
                        break;

                    default:
                        $columns[$column] = [
                            'name' => $column,
                            'data' => $column,
                            'title' => trans('messages.' . strtolower($name)),
                            'orderable' => false,
                            'visible' => false,
                            'width' => '80px',
                        ];
                        break;
                }
                $exportColumns[$column] = trans('messages.' . strtolower($name));
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
            $exportColumns['idcode'] = 'ID Code';
            $exportColumns['form_id'] = 'Form No.';
            $exportColumns['name'] = 'Name';
            $exportColumns['nrc_id'] = 'NRC ID';
        }

        $baseColumns = $columns;

        $table->setBaseColumns($baseColumns);

        $section_columns = [];
        $wordcount = 17;
        foreach ($project->sectionsDb as $k => $section) {
            $sectionColumn = 'section' . ($k + 1) . 'status';
            $sectionname = $section['sectionname'];
            // if string long to show in label show as tooltip
            if (mb_strlen($section['sectionname']) > $wordcount) {
                $sectionshort = str_limit($sectionname, $wordcount - 5);
                $sectionname = "<span data-toggle='tooltip' data-placement='top' title='$sectionname' data-container='body'> $sectionshort <i class='fa fa-info-circle'></i></span>";
            }

            $section_columns[$sectionColumn] = [
                'name' => $dbname . '.' . $sectionColumn,
                'data' => $sectionColumn,
                'orderable' => false,
                'searchable' => false,
                'width' => '80px',
                'render' => function () {
                    return "function(data,type,full,meta){
                        var html;
                        if(type === 'display') {
                            if(data == 1) {
                                html = '<img src=\'" . asset('images/complete.png') . "\'>';
                            } else if(data == 2) {
                                html = '<img src=\'" . asset('images/incomplete.png') . "\'>';
                            } else if(data == 3) {
                                html = '<img src=\'" . asset('images/error.png') . "\'>';
                            } else {
                                html = '<img src=\'" . asset('images/missing.png') . "\'>';
                            }
                        } else {
                            html = data;
                        }

                        return html;
                    }";
                },
                'title' => $sectionname,
            ];

            $exportSectionColumns[$sectionColumn] = $sectionColumn;
        }

        $table->setSectionColumns($section_columns);

        $exportColumns = array_merge($exportColumns, $exportSectionColumns);

        $input_columns = [];

        $project->load('samplesDb.data');

        $project->load(['inputs' => function ($query) {
            $query->where('status', 'published');
        }]);

        foreach ($project->inputs as $k => $input) {
            $column = $input->inputid;
            $title = preg_replace('/s[0-9]+|ir/', '', $column);
            $title = strtoupper(preg_replace('/i/', '_', $title));
            $input_columns[$column] = ['name' => $column, 'data' => $column, 'title' => $title, 'class' => 'result', 'orderable' => false, 'width' => '80px'];
            $input_columns[$column . '_status'] = ['name' => $column . '_status', 'data' => $column . '_status', 'title' => $title . '_status', 'orderable' => false, 'visible' => false];
            if (!$input->in_index) {
                $input_columns[$column]['visible'] = false;
            }

        }

        if ($project->status != 'new') {
            ksort($input_columns, SORT_NATURAL);
            $all_columns = array_merge($columns, $section_columns, $input_columns);
        }

        $table->setColumns($all_columns);

        $statesCollections = $project->samplesData->groupBy('state');
        $locations['allStates'] = $project->samplesData->pluck('state')->unique();
        $locations['allDistricts'] = $project->samplesData->pluck('district')->unique();
        $locations['allTownships'] = $project->samplesData->pluck('township')->unique();
        $locations['allVillageTracts'] = $project->samplesData->pluck('village_tract')->unique();
        $locations['allVillages'] = $project->samplesData->pluck('village')->unique();

        $districtsByState = [];
        $townshipByState = [];
        $vtractByState = [];
        $villageByState = [];

        foreach ($statesCollections as $state => $samplesData) {
            $locations['state'][$state]['district'] = $districtsByState[$state] = $samplesData->pluck('district', 'district')->toArray();
            $locations['state'][$state]['township'] = $townshipByState[$state] = $samplesData->pluck('township', 'township')->toArray();
            $locations['state'][$state]['village_tract'] = $vtractByState[$state] = $samplesData->pluck('village_tract', 'village_tract')->toArray();
            $locations['state'][$state]['village'] = $villageByState[$state] = $samplesData->pluck('village', 'village')->toArray();
        }

        $districtsCollections = $project->samplesData->groupBy('district');

        $townshipByDistrict = [];
        $vtractByDistrict = [];
        $villageByDistrict = [];

        foreach ($districtsCollections as $district => $samplesData) {
            $locations['district'][$district]['township'] = $townshipByDistrict[$district] = $samplesData->pluck('township', 'township')->toArray();
            $locations['district'][$district]['village_tract'] = $vtractByDistrict[$district] = $samplesData->pluck('village_tract', 'village_tract')->toArray();
            $locations['district'][$district]['village'] = $villageByDistrict[$district] = $samplesData->pluck('village', 'village')->toArray();
        }

        $townshipsCollections = $project->samplesData->groupBy('township');

        $vtractBytownship = [];
        $villageBytownship = [];

        foreach ($townshipsCollections as $township => $samplesData) {
            $locations['township'][$township]['village_tract'] = $vtractBytownship[$township] = $samplesData->pluck('village_tract', 'village_tract')->toArray();
            $locations['township'][$township]['village'] = $villageBytownship[$township] = $samplesData->pluck('village', 'village')->toArray();
        }

        $village_tractsCollections = $project->samplesData->groupBy('village_tract');

        $villageByvillage_tract = [];

        foreach ($village_tractsCollections as $village_tract => $samplesData) {
            $locations['village_tract'][$village_tract]['village'] = $villageByvillage_tract[$village_tract] = $samplesData->pluck('village', 'village')->toArray();
        }

        return $table->render('projects.survey.' . $project->type . '.index', compact('project'), compact('locations'));
    }

    /**
     * [create results for project]
     * @param  integer $project_id [current project id from route parameter]
     * @param  integer|string $samplable  [sample id from route parameter]
     * @param  string $form_id      [sample form id]
     * @return Illuminate\View\View         [view for result creation]
     */
    public function create($project_id, $samplable, $form_id = '')
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

        if (empty($sample)) {
            Flash::error("No sample database found.");
            return redirect(route('projects.index'));
        }
        $dbname = $project->dbname;
        $result = $sample->resultWithTable($dbname)->first();
        //if ($auth->role->role_name == 'doublechecker') {
        //    $project->load(['questions' => function ($query) {
        //        $query->where('qstatus', 'published')->where('double_entry', 1);
        //    }]);
        //} else {
        $project->load(['questions' => function ($query) {
            $query->where('qstatus', 'published');
        }]);
        //}

        $project->load(['inputs' => function ($query) {
            $query->where('status', 'published')
                ->orderBy('sort', 'ASC');
        }]);

        $view = view('projects.survey.create')
            ->with('project', $project)
            ->with('sample', $sample);

        if (!empty($result)) {
            $view->with('results', $result);
        }

        if ($auth->role->role_name == 'doublechecker') {
            $dbname_double = $project->dbname . '_double';
            $double_results = $sample->resultWithTable($dbname_double)->first();
            $view->with('double_results', $double_results);
        }

        if (!empty($form_id) && $project->copies > 1) {
            $view->with('form', $form_id);
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

        if ($project->status != 'published') {
            Flash::warning("Project need to build to show '$project->project' form.");

            return redirect(route('projects.index'));
        }

        $questions = $project->questions;

        $dblink = strtolower($project->dblink);

        // find out which repository to use based on $dblink
        // voter | location | enumerator
        $dblink = strtolower($project->dblink);
        $sample = $this->sampleRepository->findWithoutFail($samplable);

        if (Auth::user()->role->role_name == 'doublechecker') {
            $dbname = $project->dbname . '_double';
        } else {
            $dbname = $project->dbname;
        }

        $surveyResult = new SurveyResult();

        $surveyResult->setTable($dbname);

        // get all result array from form
        $results = $request->input('result');

        if (empty($results)) {
            return $this->sendError(trans('messages.no_result_submitted'), $code = 404);
        }
        if (array_key_exists('ballot', $results)) {
            $ballots = $results['ballot'];
            unset($results['ballot']);
            $party_station_counts = [];
            $party_advanced_counts = [];
            foreach ($ballots as $party => $ballot) {
                $party_station_counts[] = $results[$party . '_station'] = $ballot['station'];
                $party_advanced_counts[] = $results[$party . '_advanced'] = $ballot['advanced'];
            }
        }

        if (array_key_exists('ballot_remark', $results)) {
            $ballot_remark = $results['ballot_remark'];
            unset($results['ballot_remark']);
            foreach ($ballot_remark as $rem => $vote) {
                $results[$rem] = $vote;
            }
            $rem = count($ballot_remark);
        }

        if (array_key_exists('registered_voters', $results)) {
            $rv = $results['registered_voters'];
            unset($results['registered_voters']);
            $results['registered_voters'] = $rv;
        }

        if (array_key_exists('advanced_voters', $results)) {
            $av = $results['advanced_voters'];
            unset($results['advanced_voters']);
            $results['advanced_voters'] = $av;
        }

        //$results['samplable_id'] = $dblink;
        //$results['samplable_id'] = $sample_dblink->id;
        $sectionstatus = [];
        // group by all inputs with section and loop
        $project_by_section = $project->inputs->groupBy('section');
        foreach ($project_by_section as $section => $section_inputs) {
            // get all inputs array of inputid and skip in a section which is not optional
            $max_total_inputs = $section_inputs->where('optional', 0)->pluck('inputid', 'skip')->toArray();

            $inputs_with_skip = array_filter(array_flip($max_total_inputs)); // from database only inputs with skip column remove NULL

            $submitted_inputs_with_skip = array_intersect_key($inputs_with_skip, $results);

            $max_total_inputs_by_name = $section_inputs->where('optional', 0)->pluck('name', 'inputid')->toArray();

            $submitted_total_inputs = array_intersect_key($max_total_inputs_by_name, $results);

            $classNames = [];

            $skips = [];
            // array of inputs with skip column from database
            foreach ($submitted_inputs_with_skip as $skip_input => $skipped) {
                // explode by commas all skip classes
                $skipped_inputs_arr = explode(',', $skipped);

                unset($max_total_inputs[$skip_input]);
                // loop skip classes .s0q4,.s0q5
                foreach ($skipped_inputs_arr as $skipid) {
                    // TODO: remove trailing space
                    //$classNames[] = ' ' . str_slug($skipid); // inputid from skip column
                    $classNames[] = $className = trim(str_slug($skipid));
                    $section_inputs->where('className', 'like', '%' . $className . '%');
                }
            }
            // find inputs to skip based on submitted results
            $skipped_inputs = $section_inputs->whereIn('className', $classNames)->pluck('name', 'inputid')->toArray();
            //$skipped_inputs = $section_inputs->pluck('className', 'inputid')->toArray();

            if (!empty($skipped_inputs)) {
                foreach ($skipped_inputs as $toskip => $name) {
                    if (array_key_exists($toskip, $submitted_total_inputs)) {
                        // remove skipped inputs to avoid validating
                        unset($submitted_total_inputs[$toskip]);
                    }
                }
            }
            $section_key = $section;
            // if section not empty in form submit
            if (!empty($submitted_total_inputs)) {

                $checked_inputs = [];
                $qsum = [];
                $q = 0;
                // group inputs by 'question_id'
                foreach ($section_inputs->groupBy('question_id') as $question => $question_inputs) {
                    // get all inputs which is not optional in a question
                    $inputs = $question_inputs->where('optional', 0)->pluck('name', 'inputid')->toArray();

                    if (!empty($skipped_inputs)) {
                        foreach ($skipped_inputs as $toskip => $name) {
                            // $toskip = inputid, $name = name
                            $qsum[$toskip] = $inputs;
                            if (array_key_exists($toskip, $inputs)) {
                                // remove skipped inputs
                                unset($inputs[$toskip]);
                            }
                        }
                    }

                    // check all inputs submitted by matching from database and submitted data
                    $interset_inputs = array_intersect_key($inputs, $submitted_total_inputs);
                    $max = count($inputs);
                    $min = count(array_flip($inputs));
                    $actual = count($interset_inputs);
                    $checked_inputs[] = $interset_inputs;
                    if (($actual >= $min && $actual <= $max)) {
                        $q += 0;
                    } else {
                        $q += 1;
                    }

                }

                if ($q) {
                    $results['section' . $section_key . 'status'] = 2;
                } else {
                    $results['section' . $section_key . 'status'] = 1;
                }
            }

            $voters = $section_inputs->whereIn('inputid', ['registered_voters', 'advanced_voters'])->all();
            if (!empty($voters) && isset($rem)) {
                if ($rem == 5 && !empty($rv) && !empty($av)) {
                    // ballot remarks is submited and have 5 results
                    $results['section' . $section_key . 'status'] = 1;
                    $rem1 = $ballot_remark['rem1'];
                    $rem2 = $ballot_remark['rem2'];
                    $rem3 = $ballot_remark['rem3'];
                    $rem4 = $ballot_remark['rem4'];
                    $rem5 = $ballot_remark['rem5'];
                    $total_votes = $rem1 + $rem2;
                    $total_counted = $rem3 + $rem4 + $rem5;
                    $total_party_advanced = array_sum($party_advanced_counts);
                    //  Rem(1) + Rem(2) != Rem(3) + Rem(4) + Rem(5) ||
                    //  Rem(4) / (Rem(1) + Rem(2)) > 0.15 ||
                    //  Rem(5) / (Rem(1) + Rem(2)) > 0.15 ||
                    //  Rem(2) / (Rem(1) + Rem(2)) > 0.1 ||
                    //  EA < (Rem(1) + Rem(2)) ||
                    //  EB != Rem(2) ||
                    //  Adv(USDP) + Adv(NLD) > Rem(2)

                    if ($total_votes != $total_counted || ($rem4 / $total_votes > 0.15) || ($rem5 / $total_votes > 0.15) || ($rem2 / $total_votes > 0.15) || ($av / ($rv + $av) > 0.1) || ($rv < $total_votes) || ($av != $rem2) || $total_party_advanced > $rem2) {
                        $results['section' . $section_key . 'status'] = 3;
                    }

                } else {
                    $results['section' . $section_key . 'status'] = 2;
                }
            }

        }

        $sample_type = $request->input('sample');

        $results['sample'] = (isset($sample_type)) ? $sample_type : 1;

        // sample (country|region|1|2)
        //$results['sample'] = (!empty($request->only('samplable_type')['samplable_type'])) ? $request->only('samplable_type')['samplable_type'] : '';

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

        $results['user_id'] = $auth_user->id;

        $old_result = $sample->resultWithTable($dbname);

        $old_result = $old_result->first(); // used first() because of one to one relation
        array_walk($results, array($this, 'zawgyiUnicode'));

        if (!empty($old_result)) {
            $old_result->setTable($dbname);
            $old_result->fill($results);

            $result = $old_result->save($results);
        } else {
            $surveyResult->fill($results);

            $result = $sample->resultWithTable($dbname)->save($surveyResult);
        }
        $sample->save(); // update Sample::class

        return $this->sendResponse($result, trans('messages.saved'));
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
        $value = strtr($value, $mya_en);

        $value = Converter::convert($value, 'zawgyi', 'unicode');
    }

    private function unicodeZawgyi(&$value, $key)
    {
        $value = Converter::convert($value, 'unicode', 'zawgyi');
    }

    public function responseRateSample($project_id, $filter, SampleResponseDataTable $sampleResponse)
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

        return $sampleResponse->render('projects.survey.' . $project->type . '.response-sample', compact('project', $project), compact('filter', $filter));
    }

    public function responseRateDouble($project_id, $section, DoubleResponseDataTable $doubleResponse)
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
            'section' => $section,
        ];
        $sections_array = $project->sectionsDb;
        $sections = [];
        foreach ($sections_array as $sect) {
            $sections[$sect->id] = $sect->sectionname;
        }

        $doubleResponse->setProject($project);

        $doubleResponse->setSection($section);

        return $doubleResponse->render('projects.survey.' . $project->type . '.response-double', compact('sections', $sections), compact('settings', $settings));
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

}
