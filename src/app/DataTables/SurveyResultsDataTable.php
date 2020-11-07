<?php

namespace App\DataTables;

use App\Models\Sample;
use App\Models\SampleData;
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
        $orderTable = str_plural($this->project->dbname);
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

            $columnName = (array_key_exists('name', $column))?$column['name']:null;

            if($columnName && array_key_exists('search', $column)) {
                $value = $column['search']['value'];

                if (in_array($column['data'], array_keys($sectionColumns)) && $value != '') {

                    $table->filterColumn($columnName, function ($query, $keyword) use ($columnName) {
                        if ($keyword) {
                            $query->where($columnName, '=', $keyword);
                        } else {
                            $query->where($columnName, '=', $keyword)->orWhereNull($columnName);
                        }
                    });
                }
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
        $table = str_plural($this->project->dbname);
        $orderBy = (isset($this->orderBy)) ? $table . '.' . $this->orderBy : $this->project->dbname.'sdv.id';
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

//        if ($auth->role->role_name == 'doublechecker') {
//            $query->whereRaw(DB::raw('(samples.qc_user_id is null or samples.qc_user_id = ' . $auth->id . ')'));
//        }
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
            $query->leftjoin($project->dbname.'_samples AS sdv', function ($join) use ($project) {
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
                $query->leftjoin($section_table.' AS '.$sect_short, function ($join) use ($sect_short) {
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


        // Total resopnse any section
        $total = Request::input('total');
        if ($total) {
            $sectionColumns = $project->sections;
            $query->where(function ($q) use ($sectionColumns) {
                foreach ($sectionColumns as $section) {
                    $sectionStatus = 'section'.$section->sort.'status';
                    $sect_short = 'pj_s'.$section->sort;
                    $q->orWhereNotNull($sect_short.'.'.$sectionStatus)->orWhere($sect_short.'.'.$sectionStatus, '<>', 0);
                }
            });
        }

        // Total response by section status
        $totalstatus = Request::input('totalstatus');
        if ($totalstatus) {
            $sectionColumns = $project->sections;
            switch ($totalstatus) {
                case 'complete':
                    $status = 1;
                    break;
                case 'incomplete':
                    $status = 2;
                    break;
                case 'missing':
                    $status = 0;
                    break;
                case 'incorrect':
                    $status = 3;
                    break;
                default:
                    $status = null;
                    break;
            }

            $query->where(function ($q) use ($sectionColumns, $status) {

                foreach ($sectionColumns as $section) {
                    $sectionStatus = 'section'.$section->sort.'status';
                    $sect_short = 'pj_s'.$section->sort;
                    switch($status) {
                        case '0':
                            $q->where(function($q) use ($sectionColumns, $status, $section){
                                $sectionStatus = 'section'.$section->sort.'status';
                                $sect_short = 'pj_s'.$section->sort;
                                $q->whereNull($sect_short.'.'.$sectionStatus)->orWhere($sect_short.'.'.$sectionStatus, '=', 0);
                            });
                            break;
                        case 1:
                            $q->where($sect_short.'.'.$sectionStatus, '=', $status);
                            break;
                        case '2':
                            $q->orWhere($sect_short.'.'.$sectionStatus, '=', 2);
                            $q->orWhere(function($q) use ($sectionColumns, $status, $section){
                                $sectionStatus = 'section'.$section->sort.'status';
                                $sect_short = 'pj_s'.$section->sort;
                                $q->where($sect_short.'.'.$sectionStatus, '=', 1);
                                $current = $section->sort;
                                $q->where(function($q) use ($sectionColumns, $status, $current) {
                                    foreach($sectionColumns as $sect) {
                                        $sectStatus = 'section'.$sect->sort.'status';
                                        $sect_sh = 'pj_s'.$sect->sort;
                                        if($sect->sort !== $current) {
                                            //$q->orWhere($sect_sh.'.'.$sectStatus, '=', 1);
                                            $q->orWhere($sect_sh.'.'.$sectStatus, '=', 0);
                                            $q->orWhereNull($sect_sh.'.'.$sectStatus)->orWhere($sect_sh.'.'.$sectStatus, '=', 0);
                                        }
                                    }
                                });
                            });
                            break;
                        default:
                            $q->orWhere($sect_short.'.'.$sectionStatus, '=', $status);
                    }

                }

            });
        }

        $select_filters = $project->locationMetas->where('filter_type', 'selectbox')->pluck('field_name');

        foreach ($select_filters as $filter) {
            $sample_filter = Request::input($filter);
            if (!empty($sample_filter)) {
                $query->where($filter, $sample_filter);
            }
        }

        if($code = Request::input('code')) {
            $query->where('sample_data_id', $code);
        }

        $inputcolumn = Request::input('column');
        $inputvalue = Request::input('value');
        $sect = Request::input('sect');
        if ($inputcolumn && $inputvalue && $sect) {
            if ($inputvalue == 'NULL') {
                $query->whereNull($sect.'.'.$inputcolumn);
            } else {
                $query->where($sect.'.'.$inputcolumn, $inputvalue);
            }
        }


//        $nosample = Request::input('nosample');
//        if ($nosample) {
//            $query->where('sdv.sample', '<>', '0');
//        }

        $query->orderBy(DB::raw('LENGTH(sdv.id),sdv.id'), 'asc');
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

        $locationMetas = $project->locationMetas;

        $sampleData = new SampleData();

        $sampleData->setTable($this->project->dbname.'_samples');

        $data_collection = $sampleData->get();

        $selectbox = $locationMetas->where('filter_type', 'selectbox');

        $selectfields = $selectbox->pluck('field_name');

        $selectfields->push('channel');

        $select_js = "";
        foreach ($selectfields as $field) {
            $collect = $data_collection->pluck($field)->unique();
            $options = "";

            if($field == 'channel') {
                $options .= '<option value="sms">sms</option>';
                $options .= '<option value="web">web</option>';
            } else {
                foreach($collect as $option) {
                    $options .= '<option value="'.$option.'">'.$option.'</option>';
                }
            }

            $select_js .= "this.api().columns('.$field').every( function () {
                              var column = this;
                              var location = $('<select style=\"width:80% !important\"><option value=\"\">-</option>$options</select>')
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
                            this.api().columns(['.typein','.result']).every(function () {
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
                            this.api().columns(['.statuscolumns']).every( function () {
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
