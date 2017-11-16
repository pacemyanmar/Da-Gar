<?php

namespace App\DataTables;

use App\Models\Sample;
use App\Traits\CsvExportTrait;
use App\Traits\SurveyQueryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Services\DataTable;

class SurveyResultsDataTable extends DataTable
{
    use SurveyQueryTrait, CsvExportTrait;

    protected $filterColumns;

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $orderTable = str_plural($this->project->dblink);
        $orderBy = (isset($this->orderBy)) ? $orderTable . '.' . $this->orderBy : 'sample_datas_view.location_code';
        $order = (isset($this->order)) ? $this->order : 'asc';

        $table = datatables()
            ->eloquent($this->query())
            ->addColumn('project_id', $this->project->id)
            ->addColumn('action', 'projects.sample_datatables_actions');
        $table->escapeColumns(['action']);

        $filterColumns = $this->filterColumns;
        $sectionColumns = $this->makeSectionColumns();

        foreach ($filterColumns as $index => $column) {
            $columnName = $column['name'];
            $value = $column['search']['value'];

            if (in_array($column['data'], array_keys($sectionColumns)) && $value != '') {

                $table->filterColumn($columnName, function($query, $keyword) use ($columnName) {
                    if($keyword) {
                        $query->where($columnName, '=', $keyword);
                    } else {
                        $query->where($columnName, '=', $keyword)->orWhereNull($columnName);
                    }
                });
            }
        }

        //$table->orderColumn($orderBy, DB::raw('LENGTH(' . $orderBy . ')') . " $1");

        return $table->make(true, true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $auth = Auth::user();
        // create table name
        $table = str_plural($this->project->dblink);
        $orderBy = (isset($this->orderBy)) ? $table . '.' . $this->orderBy : 'sample_datas_view.location_code';
        $order = (isset($this->order)) ? $this->order : 'asc';

        // dblink
        $type = $this->project->dblink;

        $joinMethod = (isset($this->joinMethod)) ? $this->joinMethod : 'join';


        $project = $this->project;

        $childTable = $project->dbname;

        if($auth->role->level >= 5) {
            $selectColumns = implode(',', $this->getSelectColumns());
        } else {
            $selectColumns = implode(',', $this->getSelectColumns(false));
        }



        //$count = sizeof($unique_inputs);
        // run query
        $query = Sample::query();
        if ($auth->role->role_name == 'doublechecker') {
            $query->whereRaw(DB::raw('(samples.qc_user_id is null or samples.qc_user_id = ' . $auth->id . ')'));
        }
        // if ($auth->role->role_name == 'entryclerk') {
        //     $query->whereRaw(DB::raw('(samples.user_id is null or samples.user_id = ' . $auth->id . ')'));
        // }
        $query->leftjoin('users as user', function ($join) {
            $join->on('user.id', 'samples.user_id');
        });
        $query->leftjoin('users as update_user', function ($join) {
            $join->on('update_user.id', 'samples.update_user_id');
        });
        $query->leftjoin('users as qc_user', function ($join) {
            $join->on('qc_user.id', 'samples.qc_user_id');
        });
        if ($this->project->status != 'new') {
            $query->select(DB::raw($selectColumns));
            // join with samplable database (voters, enumerators)
            $query->leftjoin('sample_datas_view AS sdv', function ($join) use ($project) {
                $join->on('samples.sample_data_id', 'sdv.id')->where('samples.project_id', $project->id);
            });

            $section_filter = Request::input('section');

            $status = Request::input('status');

            // loop sections
            foreach ($project->sections as $k => $section) {
                if (config('sms.double_entry')) {
                    $dbl_section_table = $childTable . '_s' . $section->sort . '_dbl';
                    $dbl_short = 'pj_s'.$section->sort.'_dbl';
                    $query->leftjoin($dbl_section_table. ' AS '.$dbl_short, function ($join) use ($dbl_short) {
                        $join->on('samples.id', '=', $dbl_short . '.sample_id');
                    });

                    if ($auth->role->role_name == 'doublechecker') {
                        $joinMethod = 'leftjoin';
                    }

                }
                // join with result database

                $section_table = $childTable . '_s' . $section->sort;
                $sect_short = 'pj_s'.$section->sort;
                $query->{$joinMethod}($section_table.' AS '.$sect_short, function ($join) use ($sect_short) {
                    $join->on('samples.id', '=', $sect_short . '.sample_id');
                });


                if ($section_filter != '' && $section_filter == $section->sort) {
                    $section_status = 'section'. $section_filter .'status';
                    if ($status) {
                        $query->where($sect_short . '.' . $section_status .'', $status);
                    } else {
                        $query->where(function ($q) use ($sect_short, $section_status, $status) {
                            $q->whereNull($sect_short . '.' . $section_status)
                                ->orWhere($sect_short . '.' . $section_status, $status);
                        });
                    }
                }

            }

        }

        $this->filterColumns = Request::get('columns', []);


        $query->where('project_id', $project->id);
        $dataclerk = Request::input('user');

        if (!empty($dataclerk)) {
            if ($dataclerk == 'none') {
                $query->whereNull('user.name');
            } else {
                $query->where('user.name', $dataclerk);
            }

        }

        $township = Request::input('level3');

        if (!empty($township)) {
            $query->where('level3', $township);
        }

        $district = Request::input('level2');

        if (!empty($district)) {
            $query->where('level2', $district);
        }

        $state = Request::input('level1');

        if (!empty($state)) {
            $query->where('level1', $state);
        }

        $total = Request::input('total');
        if ($total) {
            $sectionColumns = $project->sections;
            $query->where(function ($q) use ($sectionColumns) {
                foreach ($sectionColumns as $section) {
                    $sectionStatus = 'section'.$section->sort.'status';
                    $sect_short = 'pj_s'.$section->sort;
                    $q->whereNotNull($sect_short.'.'.$sectionStatus)->orWhere($sect_short.'.'.$sectionStatus, '<>', 0);
                }

            });
        }


        $nosample = Request::input('nosample');
        if ($nosample) {
            $query->where('sdv.sample', '<>', '0');
        }

        $query->orderBy('sdv.location_code', 'asc');
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        $tableAttributes = [
            'class' => 'table table-striped table-bordered',
        ];
        $table = $this->builder()
            ->setTableAttributes($tableAttributes)
            ->addAction(['width' => '40px', 'title' => trans('messages.action')])
            ->columns($this->getColumns())
            ->ajax(['type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token(),
                ],
                'data' => '{"_method":"GET"}'])
            ->parameters($this->getBuilderParameters());


        //$table->addAction(['width' => '80px']);

        return $table;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $auth = Auth::user();
        if($auth->role->level >= 5) {
            $datatablesColumns = $this->getDatatablesColumns();
        } else {
            $datatablesColumns = $this->getDatatablesColumns(false);
        }

        if (!empty($datatablesColumns) && is_array($datatablesColumns)) {
            //dd($this->getDatatablesColumns());
            $action = ['action' => ['title' => '', 'exportable' => false,'orderable' => false, 'searchable' => false, 'width' => '5px', 'order' => [[1, 'asc']]]];
            $columns = array_merge($action, $datatablesColumns);

            return $columns;
        } else {
            return [
                'inputid' => ['name' => 'inputid', 'data' => 'inputid', 'title' => 'No.'],
                'samplable_id' => ['name' => 'samplable_id', 'data' => 'samplable_id', 'title' => 'ID', 'defaultContent' => ''],
                'value' => ['name' => 'value', 'data' => 'value', 'title' => 'Value', 'defaultContent' => ''],
            ];
        }
    }

    /**
     * Get default builder parameters.
     *
     * @return array
     */
    protected function getBuilderParameters()
    {
        $auth = Auth::user();
        $locale = \App::getLocale();
        $project = $this->project;

        $observer = "GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.id ,'\"') SEPARATOR ',<br>\\n') AS obid, 
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.given_name ,'\"') SEPARATOR ',<br>\\n') AS given_name,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.family_name ,'\"') SEPARATOR ',<br>\\n') AS family_name,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.full_name ,'\"') SEPARATOR ',<br>\\n') AS full_name,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.observer_field ,'\"') SEPARATOR ',<br>\\n') AS observer_field,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.code ,'\"') SEPARATOR ',<br>\\n') AS code,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.sample_id ,'\"') SEPARATOR ',<br>\\n') AS sample_id,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.email1 ,'\"') SEPARATOR ',<br>\\n') AS email1,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.email2 ,'\"') SEPARATOR ',<br>\\n') AS email2,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.national_id ,'\"') SEPARATOR ',<br>\\n') AS national_id,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.phone_1 ,'\"') SEPARATOR ',<br>\\n') AS phone_1,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.phone_2 ,'\"') SEPARATOR ',<br>\\n') AS phone_2,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.address ,'\"') SEPARATOR ',<br>\\n') AS address,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.language ,'\"') SEPARATOR ',<br>\\n') AS language,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.ethnicity ,'\"') SEPARATOR ',<br>\\n') AS ethnicity,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.occupation ,'\"') SEPARATOR ',<br>\\n') AS occupation,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.gender ,'\"') SEPARATOR ',<br>\\n') AS gender,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.dob ,'\"') SEPARATOR ',<br>\\n') AS dob,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.education ,'\"') SEPARATOR ',<br>\\n') AS education,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.sms_primary ,'\"') SEPARATOR ',<br>\\n') AS obsms_primary,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.sms_backup ,'\"') SEPARATOR ',<br>\\n') AS obsms_backup,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.call_primary ,'\"') SEPARATOR ',<br>\\n') AS obcall_primary,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.call_backup ,'\"') SEPARATOR ',<br>\\n') AS obcall_backup,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.hotline1 ,'\"') SEPARATOR ',<br>\\n') AS obhotline1,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.hotline2 ,'\"') SEPARATOR ',<br>\\n') AS obhotline2,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.form_type ,'\"') SEPARATOR ',<br>\\n') AS form_type,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.full_name_trans ,'\"') SEPARATOR ',<br>\\n') AS full_name_trans,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.created_at ,'\"') SEPARATOR ',<br>\\n') AS obcreated,
                     GROUP_CONCAT(CONCAT('\n', ob.code ,' : \"', ob.updated_at ,'\"') SEPARATOR ',<br>\\n') AS obupdated";


        if (!Schema::hasTable('sample_datas_view')) {
            DB::statement("CREATE VIEW sample_datas_view AS
                           (
                           SELECT sd.id, sd.location_code, sd.type, sd.dbgroup, sd.sample, sd.ps_code, sd.area_type,
                           sd.level6, sd.level5, sd.level4, sd.level3, sd.level2, sd.level1, sd.level6_trans,
                           sd.level5_trans, sd.level4_trans, sd.level3_trans, sd.level2_trans, sd.level1_trans,
                           sd.parties, sd.parent_id, sd.created_at, sd.updated_at, sd.sms_primary, sd.sms_backup, sd.call_primary, 
                           sd.call_backup, sd.hotline1, sd.hotline2, sd.sms_time, sd.incident_center, $observer  
                           FROM sample_datas AS sd LEFT JOIN observers AS ob ON ob.sample_id = sd.id  GROUP BY sd.id, sd.location_code, sd.type, sd.dbgroup, sd.sample, sd.ps_code, sd.area_type,
                           sd.level6, sd.level5, sd.level4, sd.level3, sd.level2, sd.level1, sd.level6_trans,
                           sd.level5_trans, sd.level4_trans, sd.level3_trans, sd.level2_trans, sd.level1_trans,
                           sd.parties, sd.parent_id, sd.created_at, sd.updated_at, sd.sms_primary, sd.sms_backup, sd.call_primary, 
                           sd.call_backup, sd.hotline1, sd.hotline2, sd.sms_time, sd.incident_center
                           )");
        }
        $sampleData = DB::table('sample_datas_view')->where('type', $project->dblink)->where('dbgroup', $project->dbgroup);

        if ($auth->role->level >= 7) {
            $button = [
                'extend' => 'collection',
                'text' => '<i class="fa fa-download"></i> ' . trans('messages.export'),
                'buttons' => [
                    'csvp',
                    'excelp',
                ],
            ];
        } else {
            $button = [];
        }

        $township_query = "level3, level3_trans";

        $townships = $sampleData->select(DB::raw($township_query))->get()->unique()->sortBy('level3');

        $level3_option = "";
        foreach ($townships as $key => $township) {
            if ($township->level3) {
                $townshipkey = str_replace("'", "\\'", $township->level3);
                if (!$township->level3_trans || $locale == config('app.fallback_locale')) {
                    $level3_option .= "<option value=\"$townshipkey\">$townshipkey</option>";
                } else {
                    $tsptrans = str_replace("'", "\\'", $township->level3_trans);
                    $level3_option .= "<option value=\"$townshipkey\">$tsptrans</option>";
                }
            }

        }

        $district_query = "level2, level2_trans";

        $districts = $sampleData->select(DB::raw($district_query))->get()->unique()->sortBy('level2');

        $level2_option = "";
        foreach ($districts as $key => $district) {
            if ($district->level2) {
                $districtkey = str_replace("'", "\\'", $district->level2);
                if (!$district->level2_trans || $locale == config('app.fallback_locale')) {
                    $level2_option .= "<option value=\"$districtkey\">$districtkey</option>";
                } else {
                    $dsttrans = str_replace("'", "\\'", $district->level2_trans);
                    $level2_option .= "<option value=\"$districtkey\">$dsttrans</option>";
                }
            }

        }

        $state_query = "level1, level1_trans";
        $states = $sampleData->select(DB::raw($state_query))->get()->unique()->sortBy('level1');

        $level1_option = "";
        foreach ($states as $key => $state) {
            if ($state->level1) {
                $statekey = str_replace("'", "\\'", $state->level1);
                if (!$state->level1_trans || $locale == config('app.fallback_locale')) {
                    $level1_option .= "<option value=\"$statekey\">$statekey</option>";
                } else {
                    $statrans = str_replace("'", "\\'", $state->level1_trans);
                    $level1_option .= "<option value=\"$statekey\">$statrans</option>";
                }
            }

        }

        $call_primary_option = "";

        $call_primary = $project->samplesData->pluck('call_primary', 'call_primary');

        foreach ($call_primary as $phone) {
            if ($phone) {
                $call_primary_option .= "<option value=\"$phone\">$phone</option>";
            }
        }

        $incident_center_option = "";

        $incident_center = $project->samplesData->pluck('incident_center', 'incident_center');

        foreach ($incident_center as $phone) {
            if ($phone) {
                $incident_center_option .= "<option value=\"$phone\">$phone</option>";
            }
        }

        $sms_time_option = "";

        $sms_time = $project->samplesData->pluck('sms_time', 'sms_time');

        foreach ($sms_time as $time) {
            if ($time) {
                $sms_time_option .= "<option value=\"$time\">$time</option>";
            }
        }

        $observer_field_option = "";

        $obs_type = $project->samplesData->pluck('observer_field', 'observer_field');

        foreach ($obs_type as $type) {
            if ($type) {
                $observer_field_option .= "<option value=\"$type\">$type</option>";
            }
        }

        $columnName = array_keys($this->getDatatablesColumns());

        //$textColumns = ['location_code', 'spotchecker', 'spotchecker_code', 'name', 'nrc_id', 'form_id', 'mobile'];
        $textColumns = ['location_code', 'user_id','update_user_id','qc_user_id', 'full_name', 'phone_1'];
        $textColumns = array_intersect($columnName, $textColumns);


        $flippedColumnName = array_flip($columnName);

        $textColsArr = [];
        foreach ($textColumns as $key => $value) {
            $textColsArr[] = $flippedColumnName[$value] + 1;
        }

        $selectColumns = ['level5', 'level4', 'level3', 'level2', 'level1', 'call_primary', 'sms_time', 'incident_center', 'observer_field'];

        $selectColumns = array_intersect_key($columnName, $selectColumns);

        $locationColumns = [];

        $selectColsArr = [];
        foreach ($selectColumns as $key => $value) {
            $selectColsArr[] = $flippedColumnName[$value] + 1;
            $locationColumns[$value] = $flippedColumnName[$value] + 1;
        }

        $select_js = "";
        foreach ($locationColumns as $location => $column_key) {
            $location_option = isset(${$location . '_option'}) ? ${$location . '_option'} : '';

            if ($location_option) {
                $select_js .= "this.api().columns('$column_key').every( function () {
                              var column = this;
                              var location = $('<select style=\"width:80% !important\"><option value=\"\">-</option>$location_option</select>')
                              .appendTo( $(column.header()) )
                              .on( 'change', function () {
                              var val = $.fn.dataTable.util.escapeRegex(
                                          $(this).val()
                                          );

                                  column
                                  .search( val ? val : '', false, false )
                                  .draw();
                              } );
                              location.addClass('form-control input-sm');
                              } );\n";
            }
        }

        $statusColumns = array_intersect_key($this->getDatatablesColumns(), $this->makeSectionColumns());

        $statusColsArr = [];
        foreach ($statusColumns as $key => $value) {
            $statusColsArr[] = $flippedColumnName[$key] + 1;
        }

        $textCols = implode(',', $textColsArr);
        $selectCols = implode(',', $selectColsArr);
        $statusCols = implode(',', $statusColsArr);

        return [
            'dom' => 'Brtip',
            'ordering' => false,
            'autoWidth' => true,
            //'sServerMethod' => 'POST',
            'scrollX' => true,
            'pageLength' => 20,
            'fixedColumns' => false,
            'language' => [
                "decimal" => trans('messages.decimal'),
                "emptyTable" => trans('messages.emptyTable'),
                "info" => trans('messages.info'),
                "infoEmpty" => trans('messages.infoEmpty'),
                "infoFiltered" => '',
                "infoPostFix" => trans('messages.infoPostFix'),
                "thousands" => trans('messages.thousands'),
                "lengthMenu" => trans('messages.lengthMenu'),
                "loadingRecords" => trans('messages.loadingRecords'),
                "processing" => trans('messages.processing'),
                "search" => trans('messages.search'),
                "zeroRecords" => trans('messages.zeroRecords'),
                "paginate" => [
                    "first" => trans('messages.paginate.first'),
                    "last" => trans('messages.paginate.last'),
                    "next" => trans('messages.paginate.next'),
                    "previous" => trans('messages.paginate.previous'),
                ],
                "aria" => [
                    "sortAscending" => trans('messages.aria.sortAscending'),
                    "sortDescending" => trans('messages.aria.sortDescending'),
                ],
                "buttons" => [
                    'print' => trans('messages.print'),
                    'reset' => trans('messages.reset'),
                    'reload' => trans('messages.reload'),
                    'export' => trans('messages.export'),
                    'colvis' => trans('messages.colvis'),
                ],
            ],
            'buttons' => [
                //'print',
                //'reset',
                //'reload',
                $button,
                'colvis',
            ],
            'initComplete' => "function () {
                            this.api().columns([$textCols,'.result']).every(function () {
                                var column = this;
                                var br = document.createElement(\"br\");
                                var input = document.createElement(\"input\");
                                input.className = 'form-control input-sm';
                                input.style.width = '80%';
                                $(br).appendTo($(column.header()));
                                $(input).appendTo($(column.header()))
                                .on('change', function () {
                                    column.search($(this).val(), false, false, true).draw();
                                });
                            });
                            this.api().columns([$statusCols]).every( function () {
                              var column = this;
                              var select = $('<select style=\"width:80% !important\"><option value=\"\">" . trans('messages.all') . "</option><option value=\"0\">" . trans('messages.missing') . "</option><option value=\"1\">" . trans('messages.complete') . "</option><option value=\"2\">" . trans('messages.incomplete') . "</option><option value=\"3\">" . trans('messages.error') . "</option></select>')
                              .appendTo( $(column.header()) )
                              .on( 'change', function () {
                              var val = $.fn.dataTable.util.escapeRegex(
                                          $(this).val()
                                          );

                                  column
                                  .search( val ? val : '', false, false )
                                  .draw();
                              } );
                              select.addClass('form-control input-sm');
                              } );
                              $select_js
                        }",
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return $this->project->project_en . '-' . date("Y-m-d-H-i-s");
    }
}
