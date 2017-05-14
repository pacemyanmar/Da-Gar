<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectDataTable;
use App\DataTables\SmsLogDataTable;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Sample;
use App\Models\SampleData;
use App\Models\Section;
use App\Repositories\ProjectRepository;
use App\Scopes\OrderByScope;
use Flash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Response;

class ProjectController extends AppBaseController
{
    /**
     * @var  ProjectRepository
     */

    private $projectRepository;

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
    public function index(ProjectDataTable $projectDataTable)
    {
        try {
            $this->authorize('index', Project::class);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
        return $projectDataTable->render('projects.index');
    }

    public function migrate()
    {
        $projects = $this->projectRepository->all();
        foreach ($projects as $project) {
            foreach ($project->sections as $sort => $section) {
                $section['sort'] = $sort;
                $sections_to_save[] = new Section($section);
            }

            $project->sectionsDb()->saveMany($sections_to_save);
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

        $samples = $request->input('samples');
        if (!empty($samples)) {
            foreach ($samples as $sample) {
                $key = $sample['name'];
                $val = $sample['id'];
                $input['samples'][$key] = $val;
            }
        } else {
            $input['samples']['Default'] = 1;
        }

        $short_project_name = substr($input['project'], 0, 10);
        $short_project_name = preg_replace('/[^a-zA-Z0-9]/', '_', $short_project_name);
        $unique = uniqid();
        $short_unique = substr($unique, 0, 5);
        $input['dbname'] = snake_case(strtolower($short_project_name) . '_' . $short_unique);

        // $lang = config('app.fallback_locale');

        // $input['project_trans'] = json_encode([$lang => $input['project']]);

        $project = $this->projectRepository->create($input);

        $sections = $input['sections'];

        foreach ($sections as $sort => $section) {
            $section['sort'] = $sort;
            $sections_to_save[] = new Section($section);
        }

        $project->sectionsDb()->saveMany($sections_to_save);

        // update survey_inputs as s join questions as q on s.question_id = q.id join projects as p on q.project_id = p.id set s.double_entry = 1, q.double_entry = 1 where p.id = 1 and q.section = 1;

        foreach ($input['sections'] as $skey => $section) {
            if (!empty($section)) {
                if (isset($section['double'])) {
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
        $project->load(['inputs']);
        return view('projects.edit')
            ->with('project', $project)
            ->with('questions', $project->questions);
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

        $input = $request->except('samples');
        if (!array_key_exists('parties', $input)) {
            $input['parties'] = null;
        }
        $samples = $request->input('samples');
        foreach ($samples as $sample) {
            $key = $sample['name'];
            $val = $sample['id'];
            $input['samples'][$key] = $val;
        }
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
        $sections = $input['sections'];

        if (empty($sections)) {
            $sections[0] = [
                'sectionname' => 'Survey',
            ];
        }

        $sectionsDb = $project->sectionsDb->pluck('id', 'id');

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
                    }
                    if (isset($section['optional'])) {
                        $oldsection->optional = true;
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
            $project->sectionsDb()->saveMany($sections_to_save);
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

        $questions = $project->questions;

        foreach ($questions as $question) {
            $inputs = $question->surveyInputs;
            $tosort = $question->sort;

            foreach ($inputs as $k => $input) {
                $sk = $k + 1;
                $input->sort = $tosort . sprintf('%02d', $sk);
                $input->save();
            }
        }
        return redirect()->back();
    }

    public function dbcreate($id)
    {
        // get project instance Project::class
        $project = $this->projectRepository->findWithoutFail($id);

        try {
            $this->authorize('update', $project);
        } catch (AuthorizationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        // get unique collection of inputs
        $fields = $project->inputs->unique('inputid');

        //if ($project->training) {
        // check if table has already created
        if (Schema::hasTable($project->dbname . '_training')) {
            $this->updateTable($project->dbname . '_training', $project, $fields);
        } else {
            $this->createTable($project->dbname . '_training', $project, $fields);
        }

        // }

        // check if table has already created
        if (Schema::hasTable($project->dbname)) {
            $this->updateTable($project->dbname, $project, $fields);
        } else {
            $this->createTable($project->dbname, $project, $fields);
        }

        // check if table has already created
        if (Schema::hasTable($project->dbname . '_double')) {
            $this->updateTable($project->dbname . '_double', $project, $fields);
        } else {
            $this->createTable($project->dbname . '_double', $project, $fields);
        }


        $project->questions()->update(['qstatus' => 'published']);

        $project->status = 'published';
        $project->save();
        if ($project->type != 'sample2db') {
            dispatch(new \App\Jobs\GenerateSample($project)); // need to decide this to run once or every time project update
        }

        Flash::success('Form built successfully.');

        return redirect()->back();
    }

    private function updateTable($dbname, $project, $fields)
    {
        foreach ($project->sectionsDb as $key => $section) {
            $section_num = $key + 1;
            $section_name = 'section' . $section_num . 'status';
            if (!Schema::hasColumn($dbname, $section_name)) {
                Schema::table($dbname, function ($table) use ($section_name) {
                    $table->unsignedSmallInteger($section_name)->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                });
            }
        }

        // if table exists, loop inputs
        foreach ($fields as $input) {
            /**
             * if input status is new or modified, this means we need to change table,
             * else do nothing for 'published'.
             */
            if ($input->status != 'published') {
                // check if column has created.
                if (Schema::hasColumn($dbname, $input->inputid)) {

                    Schema::table($dbname, function ($table) use ($input, $dbname) {

                        switch ($input->type) {
                            case 'radio':
                            case 'checkbox':
                                if ($input->other) {
                                    $inputType = 'string';
                                } else {
                                    $inputType = 'boolean';
                                }
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
                            $table->string($input->inputid, 100)
                                ->change();
                        } else {
                            $table->$inputType($input->inputid)
                                ->change();
                        }

                        $index = DB::select(DB::raw("show index from $dbname where Column_name ='$input->inputid'"));

                        if (!empty($index)) {
                            foreach ($index as $keyIndex) {
                                $table->dropIndex($keyIndex->Key_name);
                            }
                        }
                        if ($input->in_index) {

                            $table->index($input->inputid);

                        }
                    });
                } else {
                    // if column has not been created, creat now
                    Schema::table($dbname, function ($table) use ($input, $project) {
                        switch ($input->type) {
                            case 'radio':
                            case 'checkbox':
                                if ($input->other) {
                                    $inputType = 'string';
                                } else {
                                    $inputType = 'boolean';
                                }
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
                        if ($input->in_index) {
                            $table->$inputType($input->inputid)
                                ->index()
                                ->nullable();
                        } else {
                            if ($inputType == 'string') {
                                $table->string($input->inputid, 100)
                                    ->nullable();
                            } else {
                                $table->$inputType($input->inputid)
                                    ->nullable();
                            }
                        }
                    });
                }
            }
        }
        // change input status to published
        $project->inputs()->withoutGlobalScope(OrderByScope::class)
            ->update(['status' => 'published']);
    }

    private function createTable($dbname, $project, $fields)
    {
        // if table is not yet created, create table and inputs columns
        Schema::create($dbname, function (Blueprint $table) use ($project, $fields, $dbname) {

            $table->increments('id');
            $training_mode = preg_match('/_training$/', $dbname, $mode);
            if ($training_mode) {
                $table->string('sample_code')->index(); // sample
            } else {
                $table->unsignedInteger('sample_id')->index(); // sample
                $table->string('sample')->index(); // sample
                $table->unsignedInteger('user_id')->index();
                $table->unsignedInteger('update_user_id')->index()->nullable();
            }
            $table->timestamps();
            foreach ($project->sectionsDb as $key => $section) {
                $section_num = $key + 1;
                $table->unsignedSmallInteger('section' . $section_num . 'status')->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                //$table->json('section' . $key)->nullable();
            }

            foreach ($fields as $input) {

                switch ($input->type) {
                    case 'radio':
                    case 'checkbox':
                        if ($input->other) {
                            $inputType = 'string';
                        } else {
                            $inputType = 'unsignedTinyInteger';
                        }
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
                if ($input->in_index) {
                    $table->$inputType($input->inputid)
                        ->index()
                        ->nullable();
                } else {
                    if ($inputType == 'string') {
                        $table->string($input->inputid, 100)
                            ->nullable();
                    } else {
                        $table->$inputType($input->inputid)
                            ->nullable();
                    }
                }
            }
        });

        // change input status to published
        $project->inputs()->withoutGlobalScope(OrderByScope::class)
            ->update(['status' => 'published']);
    }

    public function search($project_id, Request $request)
    {
        $sample_id = $request->input('sample');
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }

        $sampleDb = $project->samplesDb()->first();
        $sampleData = SampleData::where('idcode', $sample_id)->first();

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect()->back();
        }

        $last_form_id = Sample::where('sample_data_id', $sampleData->id)
            ->where('project_id', $project->id)
            ->where('sample_data_type', $project->dblink)->pluck('form_id');
        $max_form_id = $last_form_id->max() + 1;

        return view('projects.survey.sample2db.info')
            ->with('project', $project)
            ->with('sample', $sampleData)
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
        foreach ($project->project_trans as $lang => $trans) {
            $project_array[0]['project::' . $lang] = $trans;
        }
        $sections = $project->sectionsDb->toArray();

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

    public function import($project_id)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }
    }

    public function smslog($project_id, SmsLogDataTable $smsLogDataTable)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }
        $smsLogDataTable->setProject($project);
        $projects = Project::all();
        return $smsLogDataTable->render('sms_logs.index', compact('projects'), compact('project'));
    }
}
