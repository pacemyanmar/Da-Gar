<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Repositories\ProjectRepository;
use App\Scopes\OrderByScope;
use Flash;
use Illuminate\Database\Schema\Blueprint;
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
        return $projectDataTable->render('projects.index');
    }

    /**
     * Show the form for creating a new Project.
     *
     * @return Response
     */
    public function create()
    {
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
        $input = $request->all();
        if (!isset($input['sections'])) {
            $input['sections'][0] = [
                'sectionname' => 'Survey',
            ];
        }

        $samples = $request->only('samples');
        if (array_key_exists('samples', $samples) && !empty($samples['samples'])) {
            foreach ($samples['samples'] as $sample) {
                $key = $sample['name'];
                $val = $sample['id'];
                $input['samples'][$key] = $val;
            }
        } else {
            $input['samples']['Default'] = 1;
        }

        $short_project_name = substr($input['project'], 0, 10);
        $unique = uniqid();
        $short_unique = substr($unique, 0, 5);
        $input['dbname'] = snake_case($short_project_name . '_' . $short_unique);

        $project = $this->projectRepository->create($input);

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

        if (empty($project)) {
            Flash::error('Project not found');

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

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }
        $project->load(['inputs' => function ($query) {
            $query->where('status', 'published');
        }]);
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

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        $input = $request->except('samples');
        if (!isset($input['sections'])) {
            $input['sections'][0] = [
                'sectionname' => 'Survey',
            ];
        }
        $samples = $request->only('samples');
        foreach ($samples['samples'] as $sample) {
            $key = $sample['name'];
            $val = $sample['id'];
            $input['samples'][$key] = $val;
        }

        $project = $this->projectRepository->update($input, $id);

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

        // get unique collection of inputs
        $fields = $project->inputs->unique('inputid');

        // check if table has already created
        if (Schema::hasTable($project->dbname)) {
            // if table exists, loop inputs
            foreach ($fields as $input) {
                $double_column = $input->inputid . '_d';
                $double_status = $input->inputid . '_ds';
                /**
                 * if input status is new or modified, this means we need to change table,
                 * else do nothing for 'published'.
                 */
                if ($input->status != 'published') {
                    // check if column has created.
                    if (Schema::hasColumn($project->dbname, $input->inputid)) {

                        Schema::table($project->dbname, function ($table) use ($input, $project, $double_column, $double_status) {

                            switch ($input->type) {
                                case 'radio':
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
                            $table->$inputType($input->inputid)
                                ->change();

                            if ($input->in_index) {

                                $table->index($input->inputid);

                            } else {

                                $index = DB::select(DB::raw("show index from $project->dbname where Column_name ='$input->inputid'"));

                                if (!empty($index)) {
                                    foreach ($index as $keyIndex) {
                                        $table->dropIndex($keyIndex->Key_name);
                                    }
                                }
                            }

                            if ($input->double_entry) {

                                if (!Schema::hasColumn($project->dbname, $double_column)) {

                                    $table->$inputType($double_column)
                                        ->after($input->inputid)
                                        ->nullable();
                                }
                                if (!Schema::hasColumn($project->dbname, $double_status)) {

                                    $table->boolean($double_status)
                                        ->after($double_column)
                                        ->nullable();
                                }
                            } else {
                                if (Schema::hasColumn($project->dbname, $double_column)) {
                                    $table->dropColumn($double_column);
                                }
                                if (Schema::hasColumn($project->dbname, $double_status)) {
                                    $table->dropColumn($double_status);
                                }
                            }
                            // change input status to published
                            $project->inputs()->withoutGlobalScope(OrderByScope::class)
                                ->update(['status' => 'published']);
                        });
                    } else {
                        // if column has not been created, creat now
                        Schema::table($project->dbname, function ($table) use ($input, $project, $double_column, $double_status) {
                            switch ($input->type) {
                                case 'radio':
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
                                $table->$inputType($input->inputid)
                                    ->nullable();
                            }

                            if ($input->double_entry) {
                                $table->$inputType($double_column)
                                    ->after($input->inputid)
                                    ->nullable();
                                $table->boolean($double_status)
                                    ->after($double_column)
                                    ->nullable();
                            }
                            // change input status to published
                            $project->inputs()->withoutGlobalScope(OrderByScope::class)
                                ->update(['status' => 'published']);
                        });
                    }
                }
            }
        } else {
            // if table is not yet created, create table and inputs columns
            Schema::create($project->dbname, function (Blueprint $table) use ($project, $fields) {

                $table->increments('id');
                $table->unsignedInteger('sample_id')->index(); // sample
                $table->string('sample')->index(); // sample
                $table->unsignedInteger('user_id')->index();
                $table->unsignedInteger('update_user_id')->index()->nullable();
                $table->timestamps();
                foreach ($project->sections as $key => $section) {
                    $section_num = $key + 1;
                    $table->unsignedSmallInteger('section' . $section_num . 'status')->index()->default(0); // 0 => missing, 1 => complete, 2 => incomplete, 3 => error
                    //$table->json('section' . $key)->nullable();
                }
                foreach ($fields as $input) {
                    $double_column = $input->inputid . '_d';
                    $double_status = $input->inputid . '_ds';
                    switch ($input->type) {
                        case 'radio':
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
                        $table->$inputType($input->inputid)
                            ->nullable();
                    }

                    if ($input->double_entry) {
                        $table->$inputType($double_column)
                            ->after($input->inputid)
                            ->nullable();
                        $table->boolean($double_status)
                            ->after($double_column)
                            ->nullable();
                    }

                    // change input status to published
                    $project->inputs()->withoutGlobalScope(OrderByScope::class)
                        ->update(['status' => 'published']);
                }
            });
        }
        $project->questions()->update(['qstatus' => 'published']);

        $project->status = 'published';
        $project->save();
        dispatch(new \App\Jobs\GenerateSample($project));
        Flash::success('Form built successfully.');

        return redirect()->back();
    }

    private function down($project)
    {
        Schema::drop($project->dbname);
    }
}
