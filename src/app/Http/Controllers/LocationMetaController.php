<?php

namespace App\Http\Controllers;

use App\DataTables\LocationMetaDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateLocationMetaRequest;
use App\Http\Requests\UpdateLocationMetaRequest;
use App\Models\LocationMeta;
use App\Models\Phone;
use App\Models\Project;
use App\Models\SampleData;
use App\Repositories\LocationMetaRepository;
use App\Repositories\ProjectRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;
use League\Csv\Reader;
use League\Csv\Statement;
use Response;
use Spatie\TranslationLoader\LanguageLine;

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

        $primary_fields = Arr::where($fields,function($value, $key){
            return ($value['field_type'] == 'primary');
        });

        if(count($primary_fields) !== 1) {
            return redirect()->back()->withErrors('Primary ID code column has not yet been set.');
        }

        if ($request->submit == 'Save Column Info') {
            $project->locationMetas()->delete();
            $this->saveColumns($input, $fields);
            $message = "Sample columns saved";

            Flash::success($message);

            return redirect()->back()->withSuccess($message);
        }

        if ($request->submit == "Update DB Schemas") {

            $this->updateStructure($project);

            $message = 'Sample DB Schemas updated successfully.';

            Flash::success($message);

            return redirect()->back()->withSuccess($message);
        }

        if ($request->submit == "Import Data") {
            //$this->updateStructure($project);
            $this->importData($project);
            $message = 'Data imported';
        }

        if ($request->submit == "Create and Import") {
            $this->saveColumns($input, $fields);
            $this->updateStructure($project);
            $this->importData($project);
            $message = 'Samples structure created and Data imported';
        }

        Flash::success($message);

        return redirect(route('projects.edit', $project->id));
    }

    private function saveColumns($input, $fields)
    {
        $filled = [];

        foreach($fields as $k => $field) {
            if($field['field_name']) {
                $field_name = str_dbcolumn($field['field_name']);
                $look_up = array_merge($input, ['field_name' => $field_name]);
                $fill = array_merge($input, [
                    'sort' => $k,
                    'label' => $field['label'],
                    'field_name' => $field_name,
                    'field_type' => Str::snake($field['field_type']),
                    'filter_type' => $field['filter_type'],
                    'data_type' => $field['data_type'],
                    'show_index' => array_key_exists('show', $field)? $field['show']:0,
                    'export' => array_key_exists('export', $field)?$field['export']:0
                ]);
                $locationMeta = $this->locationMeta->withTrashed()->firstOrNew($look_up);
                $locationMeta->fill($fill);

                $filled[] = $locationMeta;
                $locationMeta->save();

                $primary_locale = config('sms.primary_locale.locale');
                $second_locale = config('sms.second_locale.locale');
                $language_line = LanguageLine::firstOrNew([
                    'group' => 'samples',
                    'key' => $locationMeta->field_name
                ]);

                $language_line->text = [$primary_locale => $locationMeta->label, $second_locale => $locationMeta->label];
                $language_line->save();

                if ($locationMeta->trashed()) {
                    $locationMeta->restore();
                }
            }
        }
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
                            case 'code';
                                $table->string($location->field_name,20)->change();
                                break;                        
                            case 'textarea';
                                $table->text($location->field_name)->change();
                                break;
                            default;
                                $table->string($location->field_name,100)->nullable()->change();
                        }
                    } else {
                        switch ($location->field_type) {
                            case 'code';
                                $table->string($location->field_name,20)->nullable()->index();
                                break;                        
                            case 'textarea';
                                $table->text($location->field_name)->nullable();
                                break;
                            case 'number':
                            case 'sbo_number':
                                $table->integer($location->field_name)->nullable()->index();
                                break;
                            default;
                                if($location->show_index || $location->export) {
                                    $table->string($location->field_name,100)->nullable()->index();
                                } else {
                                    $table->string($location->field_name,100)->nullable();
                                }
                        }
                    }

                    if($location->show_index || $location->export) {
                        if (!$doctrineTable->hasIndex($table_name . '_' . $location->field_name . '_index')) {
                            $table->index($location->field_name);
                        }
                    }

                }

            });
        } else {
            Schema::create($table_name, function ($table) use ($project) {

                foreach ($project->locationMetas as $location) {

                    switch ($location->field_type) {
                        case 'primary';
                            $table->string($location->field_name,50)
                                ->primary($location->field_name);
                            break;
                        case 'code';
                            $table->string($location->field_name,20)->index();
                            break;                        
                        case 'textarea';
                            $table->text($location->field_name);
                            break;
                        case 'number':
                        case 'sbo_number':
                            $table->integer($location->field_name)->nullable()->index();
                            break;
                        default;
                            if($location->show_index || $location->export) {
                                $table->string($location->field_name,100)->nullable()->index();
                            } else {
                                $table->string($location->field_name,100)->nullable();
                            }
                    }

                }

            });
        }
    }

    public function importData($project)
    {
        $storage_path = storage_path('app/'.$project->sample_file);

        $reader = Reader::createFromPath($storage_path, 'r');
        $reader->setHeaderOffset(0);

        $stmt = (new Statement());
        $records = $stmt->process($reader);

        $data_array = iterator_to_array($records,true);

        $phones = Phone::all();

        $phone_mass_insert = [];

        array_walk($data_array, function(&$data, $key) use ($project, $phones, &$phone_mass_insert) {
            $newdata = [];


            foreach($data as $dk => $dv) {
                $data_column = str_dbcolumn($dk);
                if($data_column == $project->idcolumn) {
                    $newdata['id'] = filter_var($dv, FILTER_SANITIZE_STRING);
                } else {
                    $newdata[$data_column] = filter_var($dv, FILTER_SANITIZE_STRING);
                }
            }

            foreach($newdata as $dk => $dv) {
                $phone_column = $project->locationMetas->where('field_name', $dk)->where('field_type', 'phone')->first();

                if($phone_column) {
                    Log::debug($newdata);
                    $sbo_number_col = $project->locationMetas->where('field_type', 'sbo_number')->first();
                    $observer_number = (array_key_exists($sbo_number_col->field_name, $newdata))?$newdata[$sbo_number_col->field_name]:null;
                    $guessed_observer_number = (is_numeric(substr($phone_column->data_type, -1)))?substr($phone_column->data_type, -1):1;
                    Log::debug($sbo_number_col);
                    Log::debug($observer_number);

                    $phone_number = preg_replace('/[^0-9]/','',$newdata[$dk]);
                    if($phone_number) {
                        if($phone = $phones->find($phone_number)) {

                            if ($guessed_observer_number != $phone->observer || $newdata['id'] != $phone->sample_code) {
                                Log::debug($phone->phone . ',' . $guessed_observer_number . ',' . $phone->observer . ',' . $newdata['id'] . ',' . $phone->sample_code);

                                $phone->observer = ($observer_number)??$guessed_observer_number;
                                $phone->sample_code = $newdata['id'];
                                $phone->save();
                            }
                        } else {
                            $phone_mass_insert[$phone_number] = [
                                'phone' => $phone_number,
                                'sample_code' => $newdata['id'],
                                'observer' => ($observer_number)??substr($phone_column->data_type, -1)
                            ];
                        }
                    }
                }
            }
            $data = $newdata;
        });

        if(!empty($phone_mass_insert))
            Phone::insert(array_values($phone_mass_insert));

        $sample_data = new SampleData();

        $sample_data->insertOrUpdate($data_array, $project->dbname.'_samples');
    }
}
