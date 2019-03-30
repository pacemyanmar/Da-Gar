<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateProjectAPIRequest;
use App\Http\Requests\API\UpdateProjectAPIRequest;
use App\Models\Project;
use App\Models\Sample;
use App\Repositories\ProjectRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ProjectController
 * @package App\Http\Controllers\API
 */

class ProjectAPIController extends AppBaseController
{
    /** @var  ProjectRepository */
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepo)
    {
        $this->projectRepository = $projectRepo;
    }

    public function responses($id)
    {
        /** @var Project $project */
        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project)) {
            return $this->sendError('Project not found');
        }

        $sample_sms = Sample::query();
        $sample_sms->select(DB::raw('count(channel_time) AS sms_channel_count'),
            DB::raw('channel'),
            DB::raw('FROM_UNIXTIME(ROUND((CEILING(UNIX_TIMESTAMP(channel_time) / 300) * 300))) AS sms_time_slice'));
        $sample_sms->where('project_id', $project->id)->whereNotNull('channel_time')->where('channel', 'sms')->groupBy('sms_time_slice')->groupBy('channel')
        ->orderBy('channel_time', 'ASC');

        $samples_sms = $sample_sms->get();

        $sample_web = Sample::query();
        $sample_web->select(DB::raw('count(channel_time) AS web_channel_count'),
            DB::raw('channel'),
            DB::raw('FROM_UNIXTIME(ROUND((CEILING(UNIX_TIMESTAMP(channel_time) / 300) * 300))) AS web_time_slice'));
        $sample_web->where('project_id', $project->id)->whereNotNull('channel_time')->where('channel', 'web')->groupBy('web_time_slice')->groupBy('channel')
            ->orderBy('channel_time', 'ASC');

        $samples_web = $sample_web->get();

        $responses = array_merge($samples_sms->toArray(), $samples_web->toArray());

        return $this->sendResponse($responses, 'Project retrieved successfully');
    }

    /**
     * Display a listing of the Project.
     * GET|HEAD /projects
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->projectRepository->pushCriteria(new RequestCriteria($request));
        $this->projectRepository->pushCriteria(new LimitOffsetCriteria($request));
        $projects = $this->projectRepository->all();

        return $this->sendResponse($projects->toArray(), 'Projects retrieved successfully');
    }

    /**
     * Store a newly created Project in storage.
     * POST /projects
     *
     * @param CreateProjectAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateProjectAPIRequest $request)
    {
        $input = $request->all();

        $projects = $this->projectRepository->create($input);

        return $this->sendResponse($projects->toArray(), 'Project saved successfully');
    }

    /**
     * Display the specified Project.
     * GET|HEAD /projects/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Project $project */
        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project)) {
            return $this->sendError('Project not found');
        }

        return $this->sendResponse($project->toArray(), 'Project retrieved successfully');
    }

    /**
     * Update the specified Project in storage.
     * PUT/PATCH /projects/{id}
     *
     * @param  int $id
     * @param UpdateProjectAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateProjectAPIRequest $request)
    {
        $input = $request->all();

        /** @var Project $project */
        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project)) {
            return $this->sendError('Project not found');
        }

        $project = $this->projectRepository->update($input, $id);

        return $this->sendResponse($project->toArray(), 'Project updated successfully');
    }

    /**
     * Remove the specified Project from storage.
     * DELETE /projects/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Project $project */
        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project)) {
            return $this->sendError('Project not found');
        }

        $project->delete();

        return $this->sendResponse($id, 'Project deleted successfully');
    }

    public function getNewIncidents($id)
    {
        /** @var Project $project */
        $project = $this->projectRepository->findWithoutFail($id);

        if (empty($project)) {
            return $this->sendError('Project not found');
        }

        $new_incidents = $project->reportedIncidents->groupBy('inputid');

        return $this->sendResponse($new_incidents, "New incidents");

    }
}
