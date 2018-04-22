<?php

namespace App\Http\Controllers;

use App\DataTables\LocationMetaDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateLocationMetaRequest;
use App\Http\Requests\UpdateLocationMetaRequest;
use App\Models\LocationMeta;
use App\Models\Project;
use App\Repositories\LocationMetaRepository;
use App\Repositories\ProjectRepository;
use App\Http\Controllers\AppBaseController;
use Laracasts\Flash\Flash;
use Response;

class LocationMetaController extends AppBaseController
{
    /** @var  LocationMetaRepository */
    private $locationMeta;

    private $project;

    public function __construct(LocationMeta $locationMeta,Project $project)
    {
        $this->locationMeta = $locationMeta;
        $this->project = $project;
    }

    /**
     * Display a listing of the LocationMeta.
     *
     * @param LocationMetaDataTable $locationMetaDataTable
     * @return Response
     */
    public function index(LocationMetaDataTable $locationMetaDataTable)
    {
        return $locationMetaDataTable->render('location_metas.index');
    }

    /**
     * Show the form for creating a new LocationMeta.
     *
     * @return Response
     */
    public function create()
    {
        $projects = Project::pluck('project', 'id');
        return view('location_metas.create')->with('projects', $projects);
    }

    /**
     * Store a newly created LocationMeta in storage.
     *
     * @param CreateLocationMetaRequest $request
     *
     * @return Response
     */
    public function store(CreateLocationMetaRequest $request)
    {
        $input = $request->except(['fields']);

        $fields = $request->input('fields');

        $project = Project::find($request->input('project_id'));

        foreach($fields as $field) {
            $inputs = array_merge($input, $field);

            $locationMeta = $this->locationMeta->create($inputs);
        }


        Flash::success('Location Meta saved successfully.');

        return redirect(route('locationMetas.index'));
    }

    /**
     * Display the specified LocationMeta.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $locationMeta = $this->locationMeta->findOrFail($id);

        if (empty($locationMeta)) {
            Flash::error('Location Meta not found');

            return redirect(route('locationMetas.index'));
        }

        return view('location_metas.show')->with('locationMeta', $locationMeta);
    }

    /**
     * Show the form for editing the specified LocationMeta.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $project = $this->project->find($id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        $locationMetas = $project->locationMetas;

        $projects = Project::pluck('project', 'id');

        return view('location_metas.edit')
            ->with('project', $project)
            ->with('projects', $projects)
            ->with('locationMetas', $locationMetas);
    }



    /**
     * Update the specified LocationMeta in storage.
     *
     * @param  int              $id
     * @param UpdateLocationMetaRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateLocationMetaRequest $request)
    {
        $project = $this->project->find($id);

        if (empty($project)) {
            return redirect()->back()->withErrors(['Project not found'])->withInput($request->all());
        }

        if($project->id != $request->input('project_id')) {
            return redirect()->back()->withErrors(['Error with Project. Are you cheating?']);
        }

        $input = [
            'project_id' => $project->id
        ];

        $fields = $request->input('fields');

        if($fields[0]['field_type'] != 'primary') {
            return redirect()->back()->withErrors('Primary ID code column has not yet been set.');
        }

        $project->locationMetas()->delete();

        $filled = [];

        foreach($fields as $field) {
            if($field['field_name']) {
                $field_name = str_dbcolumn($field['field_name']);
                $look_up = array_merge($input, ['field_name' => $field_name]);
                $fill = array_merge($input, [
                    'label' => $field['label'],
                    'field_name' => $field_name,
                    'field_type' => snake_case($field['field_type'])
                ]);
                $locationMeta = $this->locationMeta->withTrashed()->firstOrNew($look_up);
                $locationMeta->fill($fill);

                $filled[] = $locationMeta;
                $locationMeta->save();

                if ($locationMeta->trashed()) {
                    $locationMeta->restore();
                }
            }
        }

        if ($request->submit == "Update Structure") $this->updateStructure($project);

        if ($request->submit == "Import Data") $this->importData($project);

        Flash::success('Location Meta updated successfully.');

        return redirect(route('projects.edit', $project->id));
    }

    /**
     * Remove the specified LocationMeta from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $locationMeta = $this->locationMeta->findWithoutFail($id);

        if (empty($locationMeta)) {
            Flash::error('Location Meta not found');

            return redirect(route('locationMetas.index'));
        }

        $locationMeta->destroy();

        Flash::success('Location Meta deleted successfully.');

        return redirect(route('locationMetas.index'));
    }

    public function updateStructure($project)
    {
        $table_name = $project->dbname;

        Schema::create($table_name , function (Blueprint $table) use ($project) {

            foreach ($project->locationMetas as $location){
                
                switch ($location->field_type) {
                    case 'primary';
                        $table->primary($location->field_name);
                        break;
                    default;
                        $table->string($location->field_name);
                }

            }

        });


        dd($project->id." UpdateData");
    }

    public function importData($project)
    {
        dd($project->id ." importData");
    }
}
