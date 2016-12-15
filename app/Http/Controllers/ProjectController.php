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
use Illuminate\Support\Facades\Schema;
use Response;

class ProjectController extends AppBaseController
{
    /** @var  ProjectRepository */
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepo)
    {
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
        $input['dbname'] = uniqid(snake_case($input['project']) . '_');
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

        $input = $request->all();
        if (!isset($input['sections'])) {
            $input['sections'][0] = [
                'sectionname' => 'Survey',
            ];
        }

        $project = $this->projectRepository->update($input, $id);

        Flash::success('Project updated successfully.');

        return redirect(route('projects.index'));
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

    public function dbcreate($id)
    {
        // get project instance Project::class
        $project = $this->projectRepository->findWithoutFail($id);

        // get unique collection of inputs
        $fields = $project->inputs->unique('name');

        // check if table has already created
        if (Schema::hasTable($project->dbname)) {
            // if table exists, loop inputs
            foreach ($fields as $input) {
                // check if column has created.
                if (Schema::hasColumn($project->dbname, $input->inputid)) {
                    // if input status is modified, this means we need to ALTER TABLE COLUMN, else do nothing for 'new' and 'published'.
                    if ($input->status == 'modified') {
                        Schema::table($project->dbname, function ($table) use ($input) {

                            switch ($input->type) {
                                case 'number':
                                    $inputType = 'integer';
                                    $table->$inputType($input->inputid)->unsigned()->change();
                                    break;

                                case 'text':
                                    $inputType = 'text';
                                    $table->$inputType($input->inputid)->change();
                                    break;

                                default:
                                    $inputType = 'string';
                                    $table->$inputType($input->inputid, 20)->change();
                                    break;
                            }
                            // change input status to published
                            $project->inputs()->withoutGlobalScope(OrderByScope::class)->where('name', $input->name)->update(['status' => 'published']);
                        });
                    }
                } else {
                    // if column has not been created, creat now
                    Schema::table($project->dbname, function ($table) use ($input) {
                        switch ($input->type) {
                            case 'number':
                                $inputType = 'integer';
                                $table->$inputType($input->inputid)->unsigned()->nullable()->index();
                                break;

                            case 'text':
                                $inputType = 'text';
                                $table->$inputType($input->inputid)->nullable();
                                break;

                            default:
                                $inputType = 'string';
                                $table->$inputType($input->inputid, 20)->nullable()->index();
                                break;
                        }
                        // change input status to published
                        $project->inputs()->withoutGlobalScope(OrderByScope::class)->where('name', $input->name)->update(['status' => 'published']);
                    });
                }
            }

        } else {
            // if table is not yet created, create table and inputs columns
            Schema::create($project->dbname, function (Blueprint $table) use ($project, $fields) {

                $table->increments('id');
                $table->string('form_id', 20)->nullable(); // form code
                $table->string('location_id', 20)->nullable(); // location code
                $table->string('person_id', 20)->nullable(); // observer code
                $table->string('sample', 20)->nullable(); // sample
                $table->string('location0', 20)->nullable(); // village
                $table->string('location1', 20)->nullable(); // village tract
                $table->string('location2', 20)->nullable(); // township
                $table->string('location3', 20)->nullable(); // district
                $table->string('location4', 20)->nullable(); // state
                $table->string('location5', 20)->nullable(); // country
                $table->string('location6', 20)->nullable(); // world region
                $table->string('lat_long', 50)->nullable(); // latitude, longitude
                $table->integer('user_id')->unsigned();
                $table->integer('update_user_id')->unsigned()->nullable();
                $table->timestamps();
                foreach ($project->sections as $key => $section) {
                    $table->string('section' . $key, 10)->nullable();
                }
                foreach ($fields as $input) {

                    switch ($input->type) {
                        case 'number':
                            $inputType = 'integer';
                            $table->$inputType($input->inputid)->unsigned()->nullable();
                            break;

                        case 'text':
                            $inputType = 'text';
                            $table->$inputType($input->inputid)->nullable();
                            break;

                        default:
                            $inputType = 'string';
                            $table->$inputType($input->inputid, 20)->nullable();
                            break;
                    }

                    // change input status to published
                    $project->inputs()->withoutGlobalScope(OrderByScope::class)->where('name', $input->name)->update(['status' => 'published']);
                }
            });
        }

        $project->status = 'published';
        $project->save();

        Flash::success('Form built successfully.');

        return redirect()->back();
    }

    private function down($project)
    {
        Schema::drop($project->dbname);
    }

}
