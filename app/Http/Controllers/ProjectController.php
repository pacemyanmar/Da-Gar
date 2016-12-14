<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Repositories\ProjectRepository;
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
        $project = $this->projectRepository->findWithoutFail($id);
        $fields = $project->inputs->unique('name');
        if (Schema::hasTable($project->dbname)) {
            foreach ($fields as $input) {
                if (Schema::hasColumn($project->dbname, $input->name)) {
                    Schema::table($project->dbname, function ($table) use ($input) {

                        switch ($input->type) {
                            case 'number':
                                $inputType = 'integer';
                                $table->$inputType($input->name)->unsigned()->change();
                                break;

                            case 'text':
                                $inputType = 'text';
                                break;

                            default:
                                $inputType = 'string';
                                $table->$inputType($input->name, 20)->change();
                                break;
                        }
                    });
                } else {
                    Schema::table($project->dbname, function ($table) use ($input) {
                        switch ($input->type) {
                            case 'number':
                                $inputType = 'integer';
                                $table->$inputType($input->name)->unsigned()->change();
                                break;

                            case 'text':
                                $inputType = 'text';
                                $table->$inputType($input->name)->change();
                                break;

                            default:
                                $inputType = 'string';
                                $table->$inputType($input->name, 20)->change();
                                break;
                        }
                    });
                }
            }

        } else {
            Schema::create($project->dbname, function (Blueprint $table) use ($project, $fields) {

                $table->increments('id');
                $table->string('form_id')->nullable();
                $table->string('location_id')->nullable();
                $table->string('person_id')->nullable();
                $table->integer('user_id')->unsigned();
                $table->integer('update_user_id')->unsigned();
                foreach ($fields as $input) {

                    switch ($input->type) {
                        case 'number':
                            $inputType = 'integer';
                            $table->$inputType($input->name)->unsigned()->nullable();
                            break;

                        case 'text':
                            $inputType = 'text';
                            $table->$inputType($input->name)->nullable();
                            break;

                        default:
                            $inputType = 'string';
                            $table->$inputType($input->name, 20)->nullable();
                            break;
                    }
                }
            });
        }

        Flash::success('Form built successfully.');

        return redirect()->back();
    }

    private function down($project)
    {
        Schema::drop($project->dbname);
    }

}
