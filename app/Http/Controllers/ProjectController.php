<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectDataTable;
use App\DataTables\SmsLogDataTable;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\LocationMeta;
use App\Models\LogicalCheck;
use App\Models\Observer;
use App\Models\Project;
use App\Models\Question;
use App\Models\Sample;
use App\Models\SampleData;
use App\Models\Section;
use App\Repositories\ProjectRepository;
use App\Scopes\OrderByScope;
use App\SmsHelper;
use App\Traits\QuestionsTrait;
use Flash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use League\Csv\Reader;
use League\Csv\Statement;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Ramsey\Uuid\Uuid;
use Response;
use Spatie\TranslationLoader\LanguageLine;

/**
 * Class ProjectController
 * @package App\Http\Controllers
 */
class ProjectController extends AppBaseController
{
    use QuestionsTrait;
    /**
     * @var  ProjectRepository
     */

    private $projectRepository;

    private $project;

    private $dbname;

    public function __construct(ProjectRepository $projectRepo)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
    }

    /**
     * Display a listing of the Project.
     *
     * @param ProjectDataTable $projectDataTable
     * @return Response
     */
    public function index(Project $project)
    {
        try {
            $this->authorize('index', Project::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }


        return view('projects.index')
            ->with('projects', $project->all());
    }

    public function migrate()
    {
        $projects = $this->projectRepository->all();
        foreach ($projects as $project) {
            foreach ($project->sections as $sort => $section) {
                $section['sort'] = $sort;
                $sections_to_save[] = new Section($section);
            }

            $project->sections()->saveMany($sections_to_save);
            unset($sections_to_save);
        }

        DB::statement('update questions join sections on sections.sort=questions.section AND sections.project_id=questions.project_id set questions.section=sections.id');
    }

    /**
     * Show the form for creating a new Project.
     *
     * @return Response
     */
    public function create()
    {
        try {
            $this->authorize('create', Project::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        return view('projects.create');
    }

    /**
     * Store a newly created Project in storage.
     *
     * @param CreateProjectRequest $request
     *
     * @return Response
     */
    public function store(CreateProjectRequest $request)
    {
        try {
            $this->authorize('create', Project::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        $input = $request->except(['samples']);

        if (!isset($input['sections'])) {
            $input['sections'][0] = [
                'sectionname' => 'Survey',
            ];
        }

        $short_project_name = substr($input['project'], 0, 10);
        $short_project_name = preg_replace('/[^a-zA-Z0-9]/', '_', $short_project_name);
        $unique = uniqid();
        $short_unique = substr($unique, 0, 5);
        $input['dbname'] = snake_case(strtolower($short_project_name) . '_' . $short_unique);

        // $lang = config('app.fallback_locale');

        // $input['project_trans'] = json_encode([$lang => $input['project']]);

        $project = Project::create($input);

        $sections = $input['sections'];

        foreach ($sections as $sort => $section) {
            $section['sort'] = $sort;
            $sections_to_save[] = new Section($section);
        }

        $project->sections()->saveMany($sections_to_save);

        // update survey_inputs as s join questions as q on s.question_id = q.id join projects as p on q.project_id = p.id set s.double_entry = 1, q.double_entry = 1 where p.id = 1 and q.section = 1;

        foreach ($input['sections'] as $skey => $section) {
            if (!empty($section)) {
                if (isset($section['indouble'])) {
                    $query = "update survey_inputs as s join questions as q on s.question_id = q.id join projects as p on q.project_id = p.id set s.double_entry = 1, q.double_entry = 1 where p.id = $project->id and q.section = $skey";
                } else {
                    $query = "update survey_inputs as s join questions as q on s.question_id = q.id join projects as p on q.project_id = p.id set s.double_entry = 0, q.double_entry = 0 where p.id = $project->id and q.section = $skey";
                }
                DB::update(DB::raw($query));
            }
        }

        Flash::success('Project saved successfully.');

        return redirect(route('projects.index'));
    }

    /**
     * Display the specified Project.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('view', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        if ($project->status != 'published') {
            return redirect(route('projects.index'));
        }

        return view('projects.show')->with('project', $project)
            ->with('questions', $project->questions);
    }

    /**
     * Show the form for editing the specified Project.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $project = $this->projectRepository->findWithoutFail($id);
        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        $observation_type = SampleData::pluck('observer_field', 'observer_field')->unique();


        $project->load(['inputs']);
        return view('projects.edit')
            ->with('project', $project)
            ->with('questions', $project->questions)
            ->with('observation_type', $observation_type);
    }

    /**
     * Update the specified Project in storage.
     *
     * @param  int $id
     * @param UpdateProjectRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateProjectRequest $request)
    {
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        $input = $request->except('samples', 'sections');

        if (Schema::hasTable($project->dbname)) {
            $input['status'] = 'modified';
        }

        // $lang = config('app.fallback_locale');
        // $project_trans = $project->project_trans;
        // if (!empty($project_trans) && array_key_exists($lang, $project_trans)) {
        //     $translation = $project_trans[$lang];
        // }
        // $new_translation = [$lang => $input['project']];
        // $input['project_trans'] = array_merge($new_translation, $translation);

        $project = $this->projectRepository->update($input, $id);

        $sections = $request->input('sections');

        if (empty($sections)) {
            $sections[0] = [
                'sectionname' => 'Survey',
            ];
        }

        $sectionsDb = $project->sections->pluck('id', 'id');

        foreach ($sections as $skey => $section) {
            if (!empty($section)) {
                $section['sort'] = $skey;
                // find section to update
                if (array_key_exists('sectionid', $section)) {
                    unset($sectionsDb[$section['sectionid']]);
                    $oldsection = Section::find($section['sectionid']);
                    $oldsection->sort = $skey;
                    $oldsection->sectionname = $section['sectionname'];
                    if (isset($section['descriptions'])) {
                        $oldsection->descriptions = $section['descriptions'];
                    }
                    if (isset($section['indouble'])) {
                        $oldsection->indouble = true;
                    } else {
                        $oldsection->indouble = false;
                    }
                    if (isset($section['optional'])) {
                        $oldsection->optional = true;
                    } else {
                        $oldsection->optional = false;
                    }
                    if (isset($section['disablesms'])) {
                        $oldsection->disablesms = true;
                    } else {
                        $oldsection->disablesms = false;
                    }

                    $oldsection->save();
                    if (isset($section['indouble'])) {
                        $query = "update survey_inputs as s join questions as q on s.question_id = q.id join projects as p on q.project_id = p.id set s.double_entry = 1, s.status = 'new', q.double_entry = 1 where p.id = $project->id and q.section = $oldsection->id";
                    } else {
                        $query = "update survey_inputs as s join questions as q on s.question_id = q.id join projects as p on q.project_id = p.id set s.double_entry = 0, s.status = 'new', q.double_entry = 0 where p.id = $project->id and q.section = $oldsection->id";
                    }
                    DB::update(DB::raw($query));
                } else {
                    // create new instance if section cannot find
                    $sections_to_save[] = new Section($section);
                }
            }
        }

        //delete removed section
        if (!empty($sectionsDb)) {
            foreach ($sectionsDb as $section_id) {
                $del_section = Section::find($section_id);
                $del_section->questions()->delete();
                $del_section->delete();
            }
        }

        if (!empty($sections_to_save)) {
            $project->sections()->saveMany($sections_to_save);
        }

        Flash::success('Project updated successfully.');

        return redirect()->back();
    }

    /**
     * Remove the specified Project from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('delete', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        $this->down($project);

        $this->projectRepository->delete($id);

        Flash::success('Project deleted successfully.');

        return redirect(route('projects.index'));
    }

    private function down($project)
    {
        Schema::dropIfExists($project->dbname);

        Schema::dropIfExists($project->dbname . '_double');
    }


    public function trainingmode($project_id, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {

            return $this->sendError($e->getMessage());
        }

        if (empty($project)) {

            return $this->sendError('Project not found');
        }

        $trainingmode = $request->input('trainingmode');

        if ($trainingmode) {
            $project->training = true;
            $message = 'Project changed to training mode';
        } else {
            $project->training = false;
            $message = 'Project changed to modified mode';
        }

        $project->save();

        return $this->sendResponse($project->status, $message);
    }

    public function sort($id)
    {
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        $sections = $project->sections;

        foreach ($sections as $section) {
            $questions = $section->questions;
            foreach ($questions as $qk => $question) {
                $question->sort = $section->sort . sprintf('%02d', $qk);
                $question->save();
                $inputs = $question->surveyInputs;
                foreach ($inputs as $k => $input) {
                    $input->sort = $section->sort . $question->sort . $k;
                    $input->save();
                }
            }
        }


        return redirect()->back();
    }

    public function dbcreate($id)
    {
        app()->setLocale('en');
        // get project instance Project::class
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        $this->project = $project;
        $this->dbname = $project->dbname;


        //$projectViewQuery = "CREATE VIEW ".$project->dbname." AS (SELECT * FROM ";
        $joinQuery = '';
        $sectionFields = '';

        // check if table has already created
        foreach ($project->sections as $key => $section) {

            $fields = $section->inputs->sortByDesc('other')->unique('inputid');

            $section_code = 's' . $section->sort;

            $section_dbname = $this->dbname . '_' . $section_code;

            if (Schema::hasTable($section_dbname)) {
                $this->updateTable('main', $fields, $section);
            } else {
                $this->createTable('main', $fields, $section);
            }
            if (config('sms.double_entry')) {
                // check if table has already created
                if (Schema::hasTable($section_dbname . '_dbl')) {
                    $this->updateTable('double', $fields, $section);
                } else {
                    $this->createTable('double', $fields, $section);
                }

                $viewName = $this->dbname . '_' . $section_code . '_view';

                if (!Schema::hasTable($viewName)) {
                    $this->createDoubleStatusView($section);
                } else {
                    DB::statement("DROP VIEW " . $viewName);
                    $this->createDoubleStatusView($section);
                }
            }
        }

        $project_fields = $project->inputs->sortByDesc('other')->unique('inputid');

        //if ($project->training) {
        // check if table has already created
        if (Schema::hasTable($project->dbname . '_training')) {
            $this->updateTable('training', $project_fields);
        } else {
            $this->createTable('training', $project_fields);
        }

        // }


        // check if table has already created
        if (Schema::hasTable($project->dbname . '_rawlog')) {
            $this->updateTable('rawlog', $project_fields);
        } else {
            $this->createTable('rawlog', $project_fields);
        }


        $project->questions()->update(['qstatus' => 'published']);

        $project->status = 'published';
        // change input status to published
        $project->inputs()->withoutGlobalScope(OrderByScope::class)
            ->update(['status' => 'published']);
        $project->save();
        if ($project->type != 'sample2db') {
            dispatch(new \App\Jobs\GenerateSample($project)); // need to decide this to run once or every time project update
        }
        //app()->setLocale(Session::get('locale'));
        Flash::success('Form built successfully.');

        return redirect()->back();
    }

    private function updateTable($type = 'main', $fields, $section = null)
    {
        $project = $this->project;
        $db_name = $this->dbname;
        switch ($type) {
            case 'main':
                $dbname = $db_name . '_s' . $section->sort;
                break;
            case 'double':
                $dbname = $db_name . '_s' . $section->sort . '_dbl';
                break;
            default:
                $dbname = $db_name . '_' . $type;
                break;
        }
        if ($type != 'main' && $type != 'double') {

            foreach ($project->sections as $key => $section) {
                $section_num = $section->sort;
                $section_name = 'section' . $section_num . 'status';
                if (!Schema::hasColumn($dbname, $section_name)) {
                    Schema::table($dbname, function ($table) use ($section_name) {
                        $table->unsignedSmallInteger($section_name)->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                    });
                }
            }
        }

        $questions = [];

        // if table exists, loop inputs
        foreach ($fields as $input) {
            $columnName = $input->inputid.'_c';
            /**
             * if input status is new or modified, this means we need to change table,
             * else do nothing for 'published'.
             */
            if ($input->status != 'published') {
                // check if column has created.
                if (Schema::hasColumn($dbname, $columnName)) {

                    Schema::table($dbname, function ($table) use ($input, $dbname, &$questions) {

                        switch ($input->type) {
                            case 'radio':
                                $inputType = 'unsignedTinyInteger';
                                break;
                            case 'checkbox':
                                $inputType = 'unsignedTinyInteger';
                                $questions[$input->question->qnum][$columnName] = $columnName;
                                break;

                            case 'number':
                                $inputType = 'unsignedBigInteger';
                                break;

                            case 'textarea':
                                $inputType = 'text';
                                break;

                            default:
                                $inputType = 'string';
                                break;
                        }
                        if ($inputType == 'string') {
                            $table->string($columnName, 100)
                                ->change();
                        } elseif ($inputType == 'unsignedTinyInteger') {
                            DB::statement("ALTER TABLE $dbname MODIFY COLUMN $columnName TINYINT(3) UNSIGNED NULL DEFAULT NULL;");
                        } else {
                            $table->$inputType($columnName)
                                ->change();
                        }

                        $index = DB::select(DB::raw("show index from $dbname where Column_name ='$columnName'"));

                        if (!empty($index)) {
                            foreach ($index as $keyIndex) {
                                $table->dropIndex($keyIndex->Key_name);
                            }
                        }
                        if ($input->in_index && $inputType != 'text') {

                            $table->index($columnName);

                        }
                    });
                } else {
                    // if column has not been created, creat now
                    Schema::table($dbname, function ($table) use ($input, $project) {
                        $columnName = $input->inputid.'_c';
                        switch ($input->type) {
                            case 'radio':
                                $inputType = 'unsignedTinyInteger';
                                break;
                            case 'checkbox':
                                $inputType = 'unsignedTinyInteger';
                                break;

                            case 'number':
                                $inputType = 'unsignedBigInteger'; // maxinum 10 digits exactly 4 billions
                                break;

                            case 'textarea':
                                $inputType = 'text';
                                break;

                            default:
                                $inputType = 'string';
                                break;
                        }
                        if ($input->in_index && $inputType != 'text') {
                            $table->$inputType($columnName)
                                ->index()
                                ->nullable();
                        } else {
                            if ($inputType == 'string') {
                                $table->string($columnName, 100)
                                    ->nullable();
                            } else {
                                $table->$inputType($columnName)
                                    ->nullable();
                            }
                        }

                    });
                }

                if ($input->other) {
                    if (Schema::hasColumn($dbname, $columnName . '_other')) {

                        Schema::table($dbname, function ($table) use ($input, $dbname) {
                            $columnName = $input->inputid.'_c';
                            $table->string($columnName . '_other', 100)->change()
                                ->nullable();
                        });
                    } else {
                        // if column has not been created, creat now
                        Schema::table($dbname, function ($table) use ($input, $project) {
                            $columnName = $input->inputid.'_c';
                            $table->string($columnName . '_other', 100)
                                ->nullable();
                        });
                    }
                }
            }
        }

        foreach ($questions as $question => $inputs) {
            $checkboxes = implode(' OR ', $inputs);
            if (!Schema::hasColumn($dbname, strtolower($question))) {
                Schema::table($dbname, function ($table) use ($question, $checkboxes) {
                    $table->unsignedTinyInteger(strtolower($question).'_cs')->virtualAs('IF(' . $checkboxes . ',1,0)');
                });
            }
        }
    }

    private function createTable($type = 'main', $fields, $section = null)
    {
        $project = $this->project;
        $db_name = $this->dbname;

        switch ($type) {
            case 'main':
                $dbname = $db_name . '_s' . $section->sort;
                break;
            case 'double':
                $dbname = $db_name . '_s' . $section->sort . '_dbl';
                break;
            default:
                $dbname = $db_name . '_' . $type;
                break;
        }

        // if table is not yet created, create table and inputs columns
        Schema::create($dbname, function (Blueprint $table) use ($project, $type, $fields, $section) {

            $table->increments('id');

            if ($type == 'training') {
                $table->string('sample_code')->index(); // sample
            } else {
                $table->unsignedInteger('sample_id')->index(); // sample
                $table->string('sample_type')->index(); // sample
                $table->unsignedInteger('user_id')->index();
                $table->unsignedInteger('update_user_id')->index()->nullable();
            }
            $table->timestamps();

            if ($type != 'main' && $type != 'double') {
                // get unique collection of inputs
                foreach ($project->sections as $key => $section) {
                    $section_num = $section->sort;
                    $table->unsignedSmallInteger('section' . $section_num . 'status')->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                    //$table->json('section' . $key)->nullable();
                    $table->timestamp('section' . $section_num . 'updated')->nullable();
                }
            } else {
                $table->unsignedSmallInteger('section' . $section->sort . 'status')->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                //$table->json('section' . $key)->nullable();
                $table->timestamp('section' . $section->sort . 'updated')->nullable();
            }

            $questions = [];

            foreach ($fields as $input) {
                $columnName = $input->inputid.'_c';
                switch ($input->type) {
                    case 'radio':
                        $inputType = 'unsignedTinyInteger';
                        break;

                    case 'checkbox':
                        $inputType = 'unsignedTinyInteger';
                        $questions[$input->question->qnum][$columnName] = $columnName;
                        break;

                    case 'number':
                        $inputType = 'unsignedBigInteger';
                        break;

                    case 'textarea':
                        $inputType = 'text';
                        break;

                    default:
                        $inputType = 'string';
                        break;
                }

                if ($input->in_index && $inputType != 'text') {
                    $table->$inputType($columnName)
                        ->index()
                        ->nullable();
                } else {
                    if ($inputType == 'string') {
                        $table->string($columnName, 100)
                            ->nullable();
                    } else {
                        $table->$inputType($columnName)
                            ->nullable();
                    }
                }

                if ($input->other) {
                    $table->string($columnName . '_other', 100)
                        ->nullable();
                }
            }

            // Create virtual column which can check multi select answers response
            foreach ($questions as $question => $inputs) {
                $checkboxes = implode(' OR ', $inputs);
                $table->unsignedTinyInteger(strtolower($question).'_cs')->virtualAS('IF(' . $checkboxes . ',1,0)');
            }
        });

    }

    public function search($project_id, Request $request)
    {
        $sample_id = $request->input('sample');
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }

        $sms_type = config('sms.type');

        if ($sms_type == 'observer') {
            $observer = Observer::where('code', $sample_id)->first();

            if ($observer) {
                $location_code = $observer->location->location_code;
            }

        } else {
            $location_code = $sample_id;
        }

        $sampleDb = $project->samplesDb()->first();
        $sample = SampleData::where('location_code', $location_code)->where('type', $project->dblink)->where('dbgroup', $project->dbgroup)->first();

        if (empty($sample)) {
            Flash::error('Sample Data not found');

            return redirect()->back();
        }

        $last_form_id = Sample::where('sample_data_id', $sample->id)
            ->where('project_id', $project->id)
            ->where('sample_data_type', $project->dblink)->pluck('form_id');
        $max_form_id = $last_form_id->max() + 1;

        return view('projects.survey.sample2db.info')
            ->with('project', $project)
            ->with('sample', $sample)
            ->with('form_id', $max_form_id);

    }

    public function addIncident($project_id, $sampleData_id, $project_dblink, $max_form_id)
    {
        $sampleInstance = Sample::firstOrCreate(['sample_data_id' => $sampleData_id, 'form_id' => $max_form_id, 'project_id' => $project_id, 'sample_data_type' => $project_dblink]);
        return redirect(route('projects.surveys.create', [$project_id, $sampleInstance->id, $max_form_id]));
    }

    public function export($project_id)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }
        $filename = $project->project . ' Form';

        $project_array[0] = [
            "id" => $project->id,
            "project" => $project->project,
            "dbname" => $project->dbname,
            "dblink" => $project->dblink,
            "type" => $project->type,
            "dbgroup" => $project->dbgroup,
            "parties" => $project->parties,
            "samples" => json_encode($project->samples),
            "copies" => $project->copies,
            "index_columns" => json_encode($project->index_columns),
            "status" => $project->status,
        ];
        if (!empty($project->project_trans)) {
            foreach ($project->project_trans as $lang => $trans) {
                $project_array[0]['project::' . $lang] = $trans;
            }
        }
        $sections = $project->sections->toArray();

        $questions = $project->questions->toArray();
        $inputs = $project->inputs->toArray();
        Excel::create($filename, function ($excel) use ($project_array, $sections, $questions, $inputs) {
            // Project sheet
            $excel->sheet('Project', function ($sheet) use ($project_array) {
                $sheet->setOrientation('landscape');
                $sheet->fromArray($project_array, null, 'A1', true);
            });

            // Project sheet
            $excel->sheet('Sections', function ($sheet) use ($sections) {
                $sheet->setOrientation('landscape');
                $sheet->fromArray($sections, null, 'A1', true);
            });

            // Questions sheet
            $excel->sheet('Questions', function ($sheet) use ($questions) {
                $sheet->setOrientation('landscape');
                $sheet->fromArray($questions, null, 'A1', true);
            });

            // Options sheet
            $excel->sheet('Options', function ($sheet) use ($inputs) {
                $sheet->setOrientation('landscape');
                $sheet->fromArray($inputs, null, 'A1', true);
            });

        })->export('xls');

        return redirect()->back();
    }

    public function import(Request $request)
    {
        if ($request->file('projectfile')->isValid()) {
            $primary_locale = config('sms.primary_locale.locale');
            $second_locale = config('sms.second_locale.locale');

            $project_xls_file = $request->projectfile->path();

            $reader = new Xls();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($project_xls_file);
            $spreadsheet->setActiveSheetIndexByName('project');
            $project = $spreadsheet->getActiveSheet()->toArray();

            $project_header = array_shift($project);
            $project_data = array_pop($project);

            $project = array_combine($project_header, $project_data);


            $short_project_name = substr($project['label'], 0, 10);
            $short_project_name = preg_replace('/[^a-zA-Z0-9]/', '_', $short_project_name);
            $unique = uniqid();
            $short_unique = substr($unique, 0, 5);
            $project['dbname'] = snake_case(strtolower($short_project_name) . '_' . $short_unique);

            $project['project'] = $project['label'];
            $project['unique_code'] = $project['uniqueid'];

            $projectInstance = Project::create($project);

            $spreadsheet->setActiveSheetIndexByName('sections');
            $sections = $spreadsheet->getActiveSheet()->toArray();

            $sections_header = array_shift($sections);
            array_walk(
                $sections,
                function (&$row) use ($sections_header) {
                    $row = array_combine($sections_header, $row);
                });

            foreach ($sections as $sort => $section) {
                $section['sort'] = $sort;
                $sections_to_save[] = new Section($section);
            }

            $projectInstance->sections()->saveMany($sections_to_save);

            $spreadsheet->setActiveSheetIndexByName('questions');
            $questions = $spreadsheet->getActiveSheet()->toArray();
            $questions_header = array_shift($questions);
            array_walk(
                $questions,
                function (&$row) use ($questions_header) {
                    $row = array_combine($questions_header, $row);
                });

            $spreadsheet->setActiveSheetIndexByName('options');
            $options = $spreadsheet->getActiveSheet()->toArray();
            $options_header = array_shift($options);
            array_walk(
                $options,
                function (&$row) use ($options_header) {
                    $row = array_combine($options_header, $row);
                });
            foreach ($projectInstance->sections as $section) {
                foreach ($questions as $sort => $question) {
                    if (trim($question['section']) === trim($section->sectionname)) {
                        unset($question['section']);
                        $question_raw = [
                            'section' => $section->id
                        ];
                        $raw_ans = [];
                        foreach ($options as $osort => $option) {
                            if (trim($option['question']) === trim($question['qnum'])) {
                                $option['other'] = ($option['other']) ? $option['other'] : FALSE;
                                switch ($option['type']) {
                                    case 'select_one':
                                        $option['type'] = 'radio';
                                        break;
                                    case 'select_many':
                                        $option['type'] = 'checkbox';
                                        break;
                                    default:
                                        $option['type'] = $option['type'];
                                        break;
                                }
                                $raw_ans[$osort] = $option;
                            }
                        }

                        $question_raw['sort'] = $sort;
                        $question_raw['project_id'] = $projectInstance->id;
                        $question_raw['raw_ans'] = json_encode(array_values($raw_ans), JSON_PRETTY_PRINT);
                        $question_raw['css_id'] = str_slug('s' . $section->id . $question['qnum']);
                        $question_raw['layout'] = '';
                        $question_raw['sort'] = $sort;
                        $question_row = array_merge($question, $question_raw);

                        $questionInstance = Question::create($question_row);

                        $question_translation = LanguageLine::firstOrNew([
                            'group' => 'questions',
                            'key' => $questionInstance->id.$questionInstance->qnum
                        ]);

                        $question_translation->text = [$primary_locale => $questionInstance->question, $second_locale => $questionInstance->question];
                        $question_translation->save();

                        $render = $this->to_render(
                            [
                                'question' => $questionInstance,
                                'section' => $questionInstance->section,
                                'project' => $projectInstance,
                            ],
                            [
                                'qnum' => $questionInstance->qnum,
                                'layout' => $questionInstance->layout,
                                'raw_ans' => $questionInstance->raw_ans,
                            ]
                        );

                        $inputs = $this->getInputs($render);
                        $q = $questionInstance->surveyInputs()->saveMany($inputs);
                        unset($questions[$sort]);
                    }
                }
            }
            Flash::success('Project imported successfully.');

            return redirect(route('projects.index'));

        } else {
            return redirect()->back()->withErrors('Invalid file');
        }
    }

    public function smslog($project_id, SmsLogDataTable $smsLogDataTable)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }
        $auth = Auth::user();

        if (!Schema::hasTable($project->dbname)) {
            Flash::error('Project need to build form. Contact Administrator.');
            if ($auth->role->level > 7) {
                return redirect(route('projects.edit', [$project->id]));
            } else {
                return redirect()->back();
            }
        }

        $smsLogDataTable->setProject($project);
        $projects = Project::all();
        return $smsLogDataTable->render('sms_logs.index', compact('projects'), compact('project'));
    }


    public function addLogic($project_id, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }
        $logics = $request->input('logic');

        $project->logics()->delete();

        if (!empty($logics)) {

            foreach ($logics as $logic) {
                switch ($logic['operator']) {
                    case 'between':
                        $leftval = $project->inputs()->where('inputid', $logic['leftval'])->get();

                        if ($leftval->isEmpty() || $leftval->count() != 1) {
                            Flash::error('Min/Max Logic left value error!');
                            return redirect()->back();
                        }
                        $rightval = explode(',', $logic['rightval']);
                        $right_error = false;
                        if (count($rightval) != 2) {
                            $right_error = true;
                        } else {
                            if (!is_numeric($rightval[0])) {
                                $min = $project->inputs()->where('inputid', $rightval[0])->get();
                                if ($min->isEmpty()) {
                                    $right_error = true;
                                }
                            }
                            if (!is_numeric($rightval[1])) {
                                $max = $project->inputs()->where('inputid', $rightval[1])->get();
                                if ($max->isEmpty()) {
                                    $right_error = true;
                                }
                            }
                        }
                        if ($right_error) {
                            Flash::error('Min/Max Logic Right value error!');
                            return redirect()->back();
                        }
                        break;
                    case 'min':
                        $leftval = $project->inputs()->where('inputid', $logic['leftval'])->get();

                        if ($leftval->isEmpty() || $leftval->count() != 1) {
                            Flash::error('Minimum Logic left value error!');
                            return redirect()->back();
                        }

                        if (!is_numeric($logic['rightval'])) {
                            Flash::error('Minimum Logic right value error!');
                            return redirect()->back();
                        }
                        break;
                    case 'max':
                        $leftval = $project->inputs()->where('inputid', $logic['leftval'])->get();

                        if ($leftval->isEmpty() || $leftval->count() != 1) {
                            Flash::error('Maximum Logic left value error!');
                            return redirect()->back();
                        }

                        if (!is_numeric($logic['rightval'])) {
                            Flash::error('Maximum Logic right value error!');
                            return redirect()->back();
                        }
                        break;
                    case 'equalto':
                        $leftval = $project->inputs()->where('inputid', $logic['leftval'])->get();

                        if ($leftval->isEmpty()) {
                            Flash::error('Equal to Logic left value error!');
                            return redirect()->back();
                        }

                        if (!array_key_exists('rightval', $logic)) {
                            $logic['rightval'] = null;
                        }

                        break;

                    default:
                        break;
                }
                $uuid_str = $logic['leftval'] . $logic['operator'] . $logic['rightval'] . $logic['scope'] . $project->id;
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $uuid_str);
                $logic['id'] = $uuid->toString();

                $logic_instance = new LogicalCheck($logic);
                $project->logics()->save($logic_instance);
            }
            Flash::success('Logics added successfully.');
        } else {
            Flash::success('Logics cleared successfully.');
        }

        return redirect()->back();

    }

    public function createAllViews($id)
    {
        // get project instance Project::class
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        $this->project = $project;
        $this->dbname = $project->dbname;

        // check if table has already created
        foreach ($project->sections as $key => $section) {

            $fields = $section->inputs->sortByDesc('other')->unique('inputid');

            $section_code = 's' . $section->sort;

            $section_dbname = $this->dbname . '_' . $section_code;

            if (config('sms.double_entry')) {

                $viewName = $this->dbname . '_' . $section_code . '_view';

                if (!Schema::hasTable($viewName)) {
                    $this->createDoubleStatusView($section);
                } else {
                    DB::statement("DROP VIEW " . $viewName);
                    $this->createDoubleStatusView($section);
                }
            }
        }

    }

    private function makeDoubleStatusColumns($section)
    {
        $section_questions = $section->questions->sortBy('sort');
        $section_num = $section->sort;
        $dbName = $this->dbname . '_s' . $section_num;
        $dbDblName = $this->dbname . '_s' . $section_num . '_dbl';
        $columns = [];
        foreach ($section_questions as $question) {
            $inputs = $question->surveyInputs->sortBy('sort');

            foreach ($inputs as $input) {
                $column = $input->inputid.'_c';
                $columns[$column] = "IF((" . $dbName . "." . $column . " IS NULL AND " . $dbDblName . "." . $column . " IS NULL) OR " . $dbName . "." . $column . " = " . $dbDblName . "." . $column . ", 0, 1) AS " . $column;
            }
            unset($inputs);
        }

        return $columns;
    }

    private function createDoubleStatusView($section)
    {
        //SELECT sunt_aut_d_59c93_section0.sample_id, IF(sunt_aut_d_59c93_section0.jw_1 = sunt_aut_d_59c93_section0_dbl.jw_1, 0, 1) AS jw_1 FROM sunt_aut_d_59c93_section0 LEFT JOIN sunt_aut_d_59c93_section0_dbl ON sunt_aut_d_59c93_section0.sample_id = sunt_aut_d_59c93_section0_dbl.sample_id;

        $section_num = $section->sort;
        $select = $this->makeDoubleStatusColumns($section);

        $viewName = $this->dbname . '_s' . $section_num . '_view';

        $dbName = $this->dbname . '_s' . $section_num;

        $dbDblName = $this->dbname . '_s' . $section_num . '_dbl';

        $baseColumns = [$dbName . ".sample_id"];

        $selectColumns = array_merge($baseColumns, $select);

        $selectColumns = implode(',', $selectColumns);


        $viewStatement = "CREATE VIEW " . $viewName . " AS (SELECT " . $selectColumns . " FROM ";
        $viewStatement .= $dbName . " LEFT JOIN " . $dbDblName . " ON ";
        $viewStatement .= $dbName . ".sample_id = " . $dbDblName . ".sample_id)";

        DB::statement($viewStatement);

    }

    /**
     * @param $id Project ID
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadSamples($id, Request $request)
    {

        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project))
            return redirect()->back()->withErrors('Project not found.');

        if ($request->input('fileurl')) {
            $url = $request->input('fileurl');

            $fileName = 'formurl_' . date('m-d-Y_hia') . '.csv';

            $csvData = file_get_contents($url);

            $path = storage_path('app/public');

            file_put_contents($path . '/' . $fileName, $csvData);

            dd('Done');

        }


        if ($request->file('samplefile')->isValid()) {

            $idcolumn = $request->input('idcolumn');

            if (empty($project->idcolumn)) {
                $idcolumn_slug = str_dbcolumn($idcolumn);
                $project->idcolumn = $idcolumn_slug;
            } else {
                $idcolumn_slug = $project->idcolumn;
            }

            if (!ini_get("auto_detect_line_endings")) {
                ini_set("auto_detect_line_endings", '1');
            }
            $reader = Reader::createFromPath($request->samplefile->path());
            $reader->setHeaderOffset(0);

            $stmt = (new Statement());
            $records = $stmt->process($reader);

            $headers = $records->getHeader();

            $column_list = [];

            array_walk($headers, function ($slug, $key) use ($idcolumn_slug, &$column_list) {
                $nkey = str_dbcolumn($slug);

                if ($nkey == $idcolumn_slug) {
                    $column_list['idcolumn'] = 'id';
                } else {
                    $column_list[$nkey] = $slug;
                }
            });

            if (!array_key_exists('idcolumn', $column_list))
                return redirect()->back()->withErrors('ID column not found in your file');

            $column_list = array_merge(['idcolumn' => $column_list['idcolumn']] + $column_list);

            $file = $request->samplefile->store('tmp');
            $project->sample_file = $file;

            $project->save();
            $storage_path = storage_path('app/' . $file);

            if (empty($project->sample_file) || $request->input('update_structure'))
                return $this->sampleStructure($project, $column_list, $idcolumn);

            // upload directly here
            return $this->importSampleData($records, $project, $idcolumn);

        } else {
            return redirect()->back()->withErrors('Invalid file');
        }
    }

    /**
     * @param $id Project ID
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveSampleStructure($id, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project))
            return redirect()->back()->withErrors('Project not found.');

        if ($project->id != $request->input('project_id'))
            return redirect()->back()->withErrors(['Error with Project. Are you cheating?']);


        $input = [
            'project_id' => $project->id
        ];

        $fields = $request->input('fields');

        $project->locationMetas()->delete();

        foreach ($fields as $field) {
            if ($field['field_name']) {
                $look_up = array_merge($input, ['field_name' => str_dbcolumn($field['field_name'])]);
                $fill = array_merge($input, [
                    'label' => $field['label'],
                    'field_name' => str_dbcolumn($field['field_name']),
                    'field_type' => snake_case($field['field_type'])
                ]);
                $locationMeta = $this->locationMeta->withTrashed()->firstOrNew($look_up, $fill);
                if ($locationMeta->trashed()) {
                    $locationMeta->restore();
                }
                $locationMeta->save();
            }
        }
    }


    /**
     * @param $project Project::class
     * @param $columns array
     * @return mixed
     */
    private function sampleStructure($project, $columns, $idcolumn)
    {
        array_walk($columns, function (&$item, $key) use ($idcolumn) {
            switch ($key) {
                case 'idcolumn':
                    $field['field_type'] = 'primary';
                    $field['label'] = preg_replace('/[\-_]/', ' ', title_case($idcolumn));
                    break;
                default:
                    $field['field_type'] = 'text';
                    $field['label'] = preg_replace('/[\-_]/', ' ', title_case($item));
                    break;
            }


            $field['field_name'] = str_dbcolumn($item);
            $new_loc = new LocationMeta();
            $item = $new_loc->fill($field);
        });

        $locationMetas = collect($columns);
        $projects = Project::pluck('project', 'id');

        return view('location_metas.edit-structure')
            ->with('project', $project)
            ->with('projects', $projects)
            ->with('locationMetas', $locationMetas);
    }

    /**
     * @param $records \League\Csv\ResultSet|array
     * @param $project Project::class
     */
    private function importSampleData($records, $project, $idcoumn)
    {

        $data_array = iterator_to_array($records, true);

        array_walk($data_array, function (&$data, $key) use ($project) {
            $newdata = [];
            foreach ($data as $dk => $dv) {
                if (str_dbcolumn($dk) == $project->idcolumn) {
                    $newdata['id'] = filter_var($dv, FILTER_SANITIZE_STRING);
                } else {
                    // this will throw error if column not exist, need to check first
                    $newdata[str_dbcolumn($dk)] = filter_var($dv, FILTER_SANITIZE_STRING);
                }
            }
            $data = $newdata;
        });
        $sample_data = new SampleData();
        $sample_data->setTable($project->dbname . '_samples');
        $sample_data->insertOrUpdate($data_array, $project->dbname . '_samples');

        return;
    }

}
