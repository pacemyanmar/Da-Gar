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
     * Show the form for editing the specified LocationMeta.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function editStructure($project_id)
    {
        $project = $this->project->find($project_id);

        if (empty($project)) {
            Flash::error('Project not found');

            return redirect(route('projects.index'));
        }

        $locationMetas = $project->load(['locationMetas' => function($q){
            $q->withTrashed();
            $q->orderBy('sort','ASC');
        }])->locationMetas;

        $projects = Project::pluck('project', 'id');

        return view('location_metas.edit-structure')
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
    public function createOrUpdateStructure($project_id, UpdateLocationMetaRequest $request)
    {
        $project = $this->project->find($project_id);

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

    public function updateStructure($project)
    {
        $table_name = $project->dbname.'_samples';

        if (Schema::hasTable($table_name)) {

            Schema::table($table_name, function ($table) use ($project, $table_name) {

                $conn = Schema::getConnection();
                $dbSchemaManager = $conn->getDoctrineSchemaManager();
                $doctrineTable = $dbSchemaManager->listTableDetails($table_name);

                foreach ($project->locationMetas as $location) {
                    if (Schema::hasColumn($table_name, $location->field_name)) {
                        switch ($location->field_type) {
                            case 'primary';
                                // do nothing for now
                                //$table->string($location->field_name)->primary()->change();
                                break;
                            default;
                                $table->string($location->field_name)->nullable()->change();
                        }
                    } else {
                        switch ($location->field_type) {
                            case 'primary';
                                // do nothing for now
                                //$table->string($location->field_name)->primary();
                                break;
                            default;
                                $table->string($location->field_name)->nullable();
                        }
                    }

                    if (! $doctrineTable->hasIndex($table_name.'_'.$location->field_name.'_index'))
                    {
                        $table->index($location->field_name);
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

        $sample_data->insertOrUpdate($data_array, $project->dbname.'_samples');
    }
}
