<?php

namespace App\Http\Controllers;

use App\DataTables\SampleDetailsDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateSampleDetailsRequest;
use App\Http\Requests\UpdateSampleDetailsRequest;
use App\Models\Phone;
use App\Models\SampleData;
use App\Models\SampleDetails;
use App\Repositories\ProjectRepository;
use App\Repositories\SampleDetailsRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Response;
use Illuminate\Support\Facades\Log;

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
    public function index($project_id, SampleDetailsDataTable $sampleDetailsDataTable)
    {

        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        if ($project->locationMetas->isEmpty()) {
            Flash::error('Sample Data not uploaded. Please upload now.');
            $observation_type = SampleData::pluck('observer_field','observer_field')->unique();

            return view('projects.edit')
                ->with('project', $project)
                ->with('questions', $project->questions)
                ->with('observation_type', $observation_type);
        }

        $sampleDetailsDataTable->setProject($project);
        return $sampleDetailsDataTable->render('sample_details.index', compact('project'));
    }

    /**
     * Show the form for creating a new SampleDetails.
     *
     * @return Response
     */
    public function create($project_id)
    {
        return view('sample_details.create', $project_id)->with('project_id', $project_id);
    }

    /**
     * Store a newly created SampleDetails in storage.
     *
     * @param CreateSampleDetailsRequest $request
     *
     * @return Response
     */
    public function store($project_id, CreateSampleDetailsRequest $request)
    {
        $input = $request->all();

        $sampleDetails = $this->sampleDetailsRepository->create($input);

        Flash::success('Sample Details saved successfully.');

        return redirect(route('sample-details.index', $project_id));
    }

    /**
     * Display the specified SampleDetails.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($project_id, $id, Request $request)
    {
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

        return view('sample_details.show')->with('project', $project)->with('sampleColumns', $sampleColumns )->with('sampleDetails', $sampleDetails);
    }

    /**
     * Show the form for editing the specified SampleDetails.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($project_id, $id, Request $request)
    {
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

        return view('sample_details.edit')
            ->with('project', $project)
            ->with('sampleColumns', $sampleColumns )
            ->with('sampleDetails', $sampleDetails);
    }

    /**
     * Update the specified SampleDetails in storage.
     *
     * @param  int              $id
     * @param UpdateSampleDetailsRequest $request
     *
     * @return Response
     */
    public function update($project_id, $id, UpdateSampleDetailsRequest $request)
    {
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

        $phone_columns = $project->locationMetas->where('field_type', 'phone');

        Log::debug($phone_columns);

        foreach( $phone_columns as $column ) {
            $phone_number = $request->input($column);
            Log::debug($phone_number);
            $phone_number = preg_replace('/[^0-9]/','',$phone_number);
            $phone = Phone::find($phone_number);

            if(empty($phone)) {
                $phone = new Phone();
                $phone->phone = $phone_number;
            }
            $phone->sample_code = $sampleDetails->id;
            $phone->save();
        }

        $sampleDetails->fill($request->all());

        $sampleDetails->save();

        Flash::success('Sample Details updated successfully.');

        return redirect(route('sample-details.index', $project_id));
    }

    /**
     * Remove the specified SampleDetails from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($project_id, $id, Request $request)
    {
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('sample-details.index', ['project_id', $request->input('project_id')]));
        }

        $sampleDetails = $this->sampleDetails->setTable($project->dbname.'_samples')->find($id);

        if (empty($sampleDetails)) {
            Flash::error('Sample Details not found');

            return redirect(route('sample-details.index', ['project_id', $project_id]));
        }

        $sampleDetails->delete();

        Flash::success('Sample Details deleted successfully.');

        return redirect(route('sample-details.index', $project_id));
    }
}
