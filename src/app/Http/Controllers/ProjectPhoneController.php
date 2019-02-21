<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectPhoneDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateProjectPhoneRequest;
use App\Http\Requests\UpdateProjectPhoneRequest;
use App\Models\Project;
use App\Repositories\ProjectPhoneRepository;
use Flash;
use Response;

class ProjectPhoneController extends AppBaseController
{
    /** @var  ProjectPhoneRepository */
    private $projectPhoneRepository;

    public function __construct(ProjectPhoneRepository $projectPhoneRepo)
    {
        $this->projectPhoneRepository = $projectPhoneRepo;
    }

    /**
     * Display a listing of the ProjectPhone.
     *
     * @param ProjectPhoneDataTable $projectPhoneDataTable
     * @return Response
     */
    public function index(ProjectPhoneDataTable $projectPhoneDataTable)
    {
        return $projectPhoneDataTable->render('project_phones.index');
    }

    /**
     * Show the form for creating a new ProjectPhone.
     *
     * @return Response
     */
    public function create()
    {
        $projects = Project::pluck('project', 'id');
        return view('project_phones.create')->with('projects', $projects);
    }

    /**
     * Store a newly created ProjectPhone in storage.
     *
     * @param CreateProjectPhoneRequest $request
     *
     * @return Response
     */
    public function store(CreateProjectPhoneRequest $request)
    {
        $input = $request->all();

        $projectPhone = $this->projectPhoneRepository->create($input);

        Flash::success('Project Phone saved successfully.');

        return redirect(route('projectPhones.index'));
    }

    /**
     * Display the specified ProjectPhone.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $projectPhone = $this->projectPhoneRepository->findWithoutFail($id);

        if (empty($projectPhone)) {
            Flash::error('Project Phone not found');

            return redirect(route('projectPhones.index'));
        }

        return view('project_phones.show')->with('projectPhone', $projectPhone);
    }

    /**
     * Show the form for editing the specified ProjectPhone.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $projectPhone = $this->projectPhoneRepository->findWithoutFail($id);

        if (empty($projectPhone)) {
            Flash::error('Project Phone not found');

            return redirect(route('projectPhones.index'));
        }

        $projects = Project::pluck('project', 'id');

        return view('project_phones.edit')->with('projectPhone', $projectPhone)->with('projects', $projects);
    }

    /**
     * Update the specified ProjectPhone in storage.
     *
     * @param  int              $id
     * @param UpdateProjectPhoneRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateProjectPhoneRequest $request)
    {
        $projectPhone = $this->projectPhoneRepository->findWithoutFail($id);

        if (empty($projectPhone)) {
            Flash::error('Project Phone not found');

            return redirect(route('projectPhones.index'));
        }

        $projectPhone = $this->projectPhoneRepository->update($request->all(), $id);

        Flash::success('Project Phone updated successfully.');

        return redirect(route('projectPhones.index'));
    }

    /**
     * Remove the specified ProjectPhone from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $projectPhone = $this->projectPhoneRepository->findWithoutFail($id);

        if (empty($projectPhone)) {
            Flash::error('Project Phone not found');

            return redirect(route('projectPhones.index'));
        }

        $this->projectPhoneRepository->delete($id);

        Flash::success('Project Phone deleted successfully.');

        return redirect(route('projectPhones.index'));
    }
}
