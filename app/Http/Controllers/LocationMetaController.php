<?php

namespace App\Http\Controllers;

use App\DataTables\LocationMetaDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateLocationMetaRequest;
use App\Http\Requests\UpdateLocationMetaRequest;
use App\Models\LocationMeta;
use App\Models\Project;
use App\Models\SampleData;
use App\Repositories\LocationMetaRepository;
use App\Repositories\ProjectRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laracasts\Flash\Flash;
use League\Csv\Reader;
use League\Csv\Statement;
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

        $locationMetas = $project->load(['locationMetas' => function($q){
            $q->withTrashed();
            $q->orderBy('sort','ASC');
        }])->locationMetas;

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

        $primary_fields = array_where($fields,function($value, $key){
            return ($value['field_type'] == 'primary');
        });

        if(count($primary_fields) !== 1) {
            return redirect()->back()->withErrors('Primary ID code column has not yet been set.');
        }

        $project->locationMetas()->delete();

        $filled = [];

        foreach($fields as $k => $field) {
            if($field['field_name']) {
                $field_name = str_dbcolumn($field['field_name']);
                $look_up = array_merge($input, ['field_name' => $field_name]);
                $fill = array_merge($input, [
                    'sort' => $k,
                    'label' => $field['label'],
                    'field_name' => $field_name,
                    'field_type' => snake_case($field['field_type']),
                    'filter_type' => $field['filter_type'],
                    'show_index' => array_key_exists('show', $field)? $field['show']:0,
                    'export' => array_key_exists('export', $field)?$field['export']:0
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

        $message = "Sample column structure saved";

        if ($request->submit == "Update Structure") {

            $this->updateStructure($project);

            $message = 'Sample Structure created sccessfully.';
        }



        if ($request->submit == "Import Data") {
            $this->importData($project);
            $message = 'Data imported';
        }

        Flash::success($message);

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
        $table_name = $project->dbname.'_samples';

        if (Schema::hasTable($table_name)) {

            Schema::table($table_name, function ($table) use ($project, $table_name) {

                foreach ($project->locationMetas as $location) {
                    if (Schema::hasColumn($table_name, $location->field_name)) {
                        switch ($location->field_type) {
                            case 'primary';
                                // do nothing for now
                                //$table->string($location->field_name)->primary()->change();
                                break;
                            default;
                                $table->string($location->field_name)->nullable()->index()->change();
                        }
                    } else {
                        switch ($location->field_type) {
                            case 'primary';
                                // do nothing for now
                                //$table->string($location->field_name)->primary();
                                break;
                            default;
                                $table->string($location->field_name)->nullable()->index();
                        }
                    }

                }

            });
        } else {
            Schema::create($table_name, function ($table) use ($project) {

                foreach ($project->locationMetas as $location) {

                    switch ($location->field_type) {
                        case 'primary';
                            $table->string($location->field_name)
                                ->primary($location->field_name);
                            break;
                        default;
                            $table->string($location->field_name)->nullable()->index();
                    }

                }

            });
        }

    }

    public function importData($project)
    {
        $this->updateStructure($project);
        $storage_path = storage_path('app/'.$project->sample_file);

        $reader = Reader::createFromPath($storage_path, 'r');
        $reader->setHeaderOffset(0);

        $stmt = (new Statement());
        $records = $stmt->process($reader);

        $data_array = iterator_to_array($records,true);

        array_walk($data_array, function(&$data, $key) use ($project) {
            $newdata = [];
            foreach($data as $dk => $dv) {
                if(str_dbcolumn($dk) == $project->idcolumn) {
                    $newdata['id'] = filter_var($dv, FILTER_SANITIZE_STRING);
                } else {
                    $newdata[str_dbcolumn($dk)] = filter_var($dv, FILTER_SANITIZE_STRING);
                }
            }
            $data = $newdata;
        });
        $sample_data = new SampleData();
        $sample_data->setTable($project->dbname.'_samples');
        $sample_data->insert($data_array);
    }
}
