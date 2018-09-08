<?php

namespace App\Http\Controllers;

use App\DataTables\SampleDetailsDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateSampleDetailsRequest;
use App\Http\Requests\UpdateSampleDetailsRequest;
use App\Models\SampleDetails;
use App\Repositories\ProjectRepository;
use App\Repositories\SampleDetailsRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Response;

class SampleDetailsController extends AppBaseController
{
    /** @var  SampleDetailsRepository */
    private $sampleDetailsRepository;

    private $sampleDetails;

    private $projectRepository;

    public function __construct(SampleDetailsRepository $sampleDetailsRepo, SampleDetails $sampleDetails, ProjectRepository $projectRepository)
    {
        $this->sampleDetailsRepository = $sampleDetailsRepo;
        $this->sampleDetails = $sampleDetails;
        $this->projectRepository = $projectRepository;
    }

    /**
     * Display a listing of the SampleDetails.
     *
     * @param SampleDetailsDataTable $sampleDetailsDataTable
     * @return Response
     */
    public function index(SampleDetailsDataTable $sampleDetailsDataTable, Request $request)
    {
        $project_id = $request->input('project_id');
        $sampleDetailsDataTable->setProject($project_id);
        return $sampleDetailsDataTable->render('sample_details.index', ['project_id', $request->input('project_id')]);
    }

    /**
     * Show the form for creating a new SampleDetails.
     *
     * @return Response
     */
    public function create()
    {
        return view('sample_details.create');
    }

    /**
     * Store a newly created SampleDetails in storage.
     *
     * @param CreateSampleDetailsRequest $request
     *
     * @return Response
     */
    public function store(CreateSampleDetailsRequest $request)
    {
        $input = $request->all();

        $sampleDetails = $this->sampleDetailsRepository->create($input);

        Flash::success('Sample Details saved successfully.');

        return redirect(route('sample-details.index'));
    }

    /**
     * Display the specified SampleDetails.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id, Request $request)
    {
        $project_id = $request->input('project_id');
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
        }
        $sampleColumns = $project->locationMetas->pluck('label','field_name');

        $sampleDetails = $this->sampleDetails->setTable($project->dbname.'_samples')->find($id);

        if (empty($sampleDetails)) {
            Flash::error('Sample Details not found');

            return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
        }

        return view('sample_details.show')->with('sampleColumns', $sampleColumns )->with('sampleDetails', $sampleDetails);
    }

    /**
     * Show the form for editing the specified SampleDetails.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id, Request $request)
    {
        $project_id = $request->input('project_id');
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
        }
        $sampleColumns = $project->locationMetas->pluck('label','field_name');

        $sampleDetails = $this->sampleDetails->setTable($project->dbname.'_samples')->find($id);

        if (empty($sampleDetails)) {
            Flash::error('Sample Details not found');

            return redirect(route('sample-details.index', ['project_id', $project_id]));
        }

        return view('sample_details.edit')->with('sampleColumns', $sampleColumns )->with('sampleDetails', $sampleDetails);
    }

    /**
     * Update the specified SampleDetails in storage.
     *
     * @param  int              $id
     * @param UpdateSampleDetailsRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSampleDetailsRequest $request)
    {
        $sampleDetails = $this->sampleDetailsRepository->findWithoutFail($id);

        if (empty($sampleDetails)) {
            Flash::error('Sample Details not found');

            return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
        }

        $sampleDetails = $this->sampleDetailsRepository->update($request->all(), $id);

        Flash::success('Sample Details updated successfully.');

        return redirect(route('sample-details.index'));
    }

    /**
     * Remove the specified SampleDetails from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id, Request $request)
    {
        $sampleDetails = $this->sampleDetailsRepository->findWithoutFail($id);

        if (empty($sampleDetails)) {
            Flash::error('Sample Details not found');

            return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
        }

        $this->sampleDetailsRepository->delete($id);

        Flash::success('Sample Details deleted successfully.');

        return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
    }
}
