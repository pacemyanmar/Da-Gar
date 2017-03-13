<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Section;
use App\Repositories\ProjectRepository;
use App\Scopes\OrderByScope;
use Flash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * @param  int              $id
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
                $input->sort = $tosort . $sk;
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
        dispatch(new \App\Jobs\GenerateSample($project)); // need to decide this to run once or every time project update
        Flash::success('Form built successfully.');

        return redirect()->back();
    }

    private function down($project)
    {
        Schema::dropIfExists($project->dbname);

        Schema::dropIfExists($project->dbname . '_double');
    }

    private function createTable($dbname, $project, $fields)
    {
        // if table is not yet created, create table and inputs columns
        Schema::create($dbname, function (Blueprint $table) use ($project, $fields) {

            $table->increments('id');
            $table->unsignedInteger('sample_id')->index(); // sample
            $table->string('sample')->index(); // sample
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('update_user_id')->index()->nullable();
            $table->timestamps();
            foreach ($project->sectionsDb as $key => $section) {
                $section_num = $key + 1;
                $table->unsignedSmallInteger('section' . $section_num . 'status')->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                //$table->json('section' . $key)->nullable();
            }

            //$table->unsignedInteger('registered_voters')->index()->default(0);
            //$table->unsignedInteger('advanced_voters')->index()->default(0);
            if (!empty($project->parties)) {
                $parties = explode(',', $project->parties);
                foreach ($parties as $party) {
                    $table->unsignedInteger($party . '_station')->index()->nullable();
                    $table->unsignedInteger($party . '_advanced')->index()->nullable();
                }

                $table->unsignedInteger('rem1')->index()->default(0);
                $table->unsignedInteger('rem2')->index()->default(0);
                $table->unsignedInteger('rem3')->index()->default(0);
                $table->unsignedInteger('rem4')->index()->default(0);
                $table->unsignedInteger('rem5')->index()->default(0);
            }
            foreach ($fields as $input) {
                $double_column = $input->inputid . '_d';
                $double_status = $input->inputid . '_ds';
                switch ($input->type) {
                    case 'radio':
                        $inputType = 'string';
                        break;
                    case 'checkbox':
                        $inputType = 'unsignedSmallInteger';
                        break;

                    case 'number':
                        $inputType = 'unsignedInteger';
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
        if (!empty($project->parties)) {
            $parties = explode(',', $project->parties);
            foreach ($parties as $party) {
                if (!Schema::hasColumn($dbname, $party . '_station')) {
                    $table->unsignedInteger($party . '_station')->index()->nullable();
                }
                if (!Schema::hasColumn($dbname, $party . '_advanced')) {
                    $table->unsignedInteger($party . '_advanced')->index()->nullable();
                }
            }
            if (!Schema::hasColumn($dbname, 'rem1')) {
                $table->unsignedInteger('rem1')->index()->default(0);
            }
            if (!Schema::hasColumn($dbname, 'rem2')) {
                $table->unsignedInteger('rem2')->index()->default(0);
            }
            if (!Schema::hasColumn($dbname, 'rem3')) {
                $table->unsignedInteger('rem3')->index()->default(0);
            }
            if (!Schema::hasColumn($dbname, 'rem4')) {
                $table->unsignedInteger('rem4')->index()->default(0);
            }
            if (!Schema::hasColumn($dbname, 'rem5')) {
                $table->unsignedInteger('rem5')->index()->default(0);
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
                                $inputType = 'string';
                                break;
                            case 'checkbox':
                                $inputType = 'unsignedSmallInteger';
                                break;

                            case 'number':
                                $inputType = 'unsignedInteger';
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
                                $inputType = 'string';
                                break;
                            case 'checkbox':
                                $inputType = 'unsignedSmallInteger';
                                break;

                            case 'number':
                                $inputType = 'unsignedInteger';
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

    public function search($project_id, Request $request)
    {
        $sample_id = $request->input('sample');
        $project = $this->projectRepository->findWithoutFail($project_id);
        dd($project->samplesData()->where('idcode', $sample_id)->first());
    }
}
