<?php

namespace App\DataTables;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Services\DataTable;

class SampleResponseDataTable extends DataTable
{
    private $project;

    private $filter;

    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }
    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
            ->make(true, true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $query = Sample::query();
        $project = $this->project;
        $childTable = $project->dbname;

        $sectionColumns = [];
        foreach ($project->sectionsDb as $k => $section) {
            $sectionColumns[] = 'section' . ($k + 1) . 'status';
        }

        // modify column name to use in sql query TABLE.COLUMN format
        array_walk($sectionColumns, function (&$column, $index) use ($childTable) {
            $columnStr = 'SUM(IF((' . $childTable . '.' . $column . ' = 0 OR ' . $childTable . '.' . $column . ' IS NULL), 1, 0)) AS ' . $column . '_missing';
            $columnStr .= ', SUM(IF(' . $childTable . '.' . $column . ' = 1, 1, 0)) AS ' . $column . '_complete';
            $columnStr .= ', SUM(IF(' . $childTable . '.' . $column . ' = 2, 1, 0)) AS ' . $column . '_incomplete';
            $columnStr .= ', SUM(IF(' . $childTable . '.' . $column . ' = 3, 1, 0)) AS ' . $column . '_error';
            $column = $columnStr;
        });

        $sectionColumnsStr = implode(',', $sectionColumns);

        if ($project->status != 'new') {
            switch ($this->filter) {
                case 'user':
                    # code...
                    $filter = 'user';
                    $query->select('user.name AS ' . $filter, DB::raw('SUM(IF(sample_datas.id,1,0)) AS alltotal, SUM(IF(' . $childTable . '.id, 1, 0)) AS total'), DB::raw($sectionColumnsStr));
                    $query->groupBy($filter);
                    break;

                default:
                    # code...
                    $filter = $this->filter;
                    $query->select('sample_datas.' . $filter, DB::raw('SUM(IF(sample_datas.id,1,0)) AS alltotal, SUM(IF(' . $childTable . '.id, 1, 0)) AS total'), DB::raw('GROUP_CONCAT(DISTINCT user.name) as user_name', 'GROUP_CONCAT(DISTINCT update_user.name) as update_user', 'GROUP_CONCAT(DISTINCT qc_user.name) as qc_user'), DB::raw($sectionColumnsStr));
                    $query->groupBy('sample_datas.' . $filter);
                    break;
            }

            $query->leftjoin('sample_datas', function ($join) {
                $join->on('samples.sample_data_id', 'sample_datas.id');
            });

            $query->leftjoin($childTable, function ($join) use ($childTable) {
                $join->on('samples.id', '=', $childTable . '.sample_id');
            });
        }

        $query->leftjoin('users as user', function ($join) {
            $join->on('user.id', 'samples.user_id');
        });
        $query->leftjoin('users as update_user', function ($join) {
            $join->on('update_user.id', 'samples.update_user_id');
        });
        $query->leftjoin('users as qc_user', function ($join) {
            $join->on('qc_user.id', 'samples.qc_user_id');
        });
        $query->where('project_id', $project->id);
        $query->where('sample_datas.sample', '<>', '0');

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
        return $this->builder()
            ->setTableAttributes($tableAttributes)
            ->columns($this->getColumns())
            ->ajax(['type' => 'POST', 'data' => '{"_method":"GET"}'])
            ->parameters($this->getBuilderParameters());
    }

    /**
     * Get default builder parameters.
     *
     * @return array
     */
    protected function getBuilderParameters()
    {
        return [
            'dom' => 'Brtip',
            'scrollX' => true,
            'language' => [
                "decimal" => trans('messages.decimal'),
                "emptyTable" => trans('messages.emptyTable'),
                "info" => trans('messages.info'),
                "infoEmpty" => trans('messages.infoEmpty'),
                "infoFiltered" => trans('messages.infoFiltered'),
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

            ],
            'initComplete' => "function () {
                            this.api().columns([0]).every(function () {
                                var column = this;
                                var br = document.createElement(\"br\");
                                var input = document.createElement(\"input\");
                                input.className = 'form-control input-sm';
                                input.style.width = '100px';
                                $(br).appendTo($(column.header()));
                                $(input).appendTo($(column.header()))
                                .on('change', function () {
                                    column.search($(this).val(), false, false, true).draw();
                                });
                            });
                        }",
            "footerCallback" => "function ( row, data, start, end, display ) {
                            var api = this.api();
                            total = api
                                .column( 1 )
                                .data()
                                .reduce( function (a, b) {
                                    return parseInt(a, 10) + parseInt(b, 10);
                                }, 0 );
                            api.columns().every(function(){
                                  var column = this;
                                  console.log(column.data());
                                  var sum = column
                                      .data()
                                      .reduce(function (a, b) {
                                         a = parseInt(a, 10);
                                         if(isNaN(a)){ a = 0; }

                                         b = parseInt(b, 10);
                                         if(isNaN(b)){ b = 0; }

                                         return a + b;
                                      });

                                  $(column.footer()).html(sum + ' (' + parseFloat((sum * 100)/ total).toFixed(1) + '%)');
                              });

                            $(api.column(0).footer()).html('Total');
                        }",
        ];
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $project = $this->project;
        $filter = $this->filter;
        $columns = [
            //'idcode' => ['data' => 'idcode', 'name' => 'idcode', 'title' => 'ID Code'],
            "$filter" => ['data' => "$filter", 'name' => 'sample_datas.' . $filter, 'orderable' => false, "render" => function () use ($project, $filter) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                        if(data){
                                              return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'>' + data + '</a>';
                                          } else {
                                            return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "=none> None </a>';
                                          }
                                    } else {
                                      return data;
                                    }
                                  }";
            }],
            "alltotal" => ['data' => 'alltotal', 'name' => 'alltotal', 'title' => 'All Forms', 'orderable' => false, "render" => function () use ($project, $filter) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&alltotal=1>' + data + '</a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }],
            "total" => ['data' => 'total', 'name' => 'total', 'title' => 'Response', 'orderable' => false, "render" => function () use ($project, $filter) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&total=1>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(1) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }],
            //'user_name' => ['data' => 'user_name', 'name' => 'user.name', 'defaultContent' => 'N/A'],
            //'update_user' => ['data' => 'update_user', 'name' => 'update_user.name', 'defaultContent' => 'N/A'],
        ];

        $sectionColumns = [];
        foreach ($project->sectionsDb as $k => $section) {
            $section_key = ($k + 1);
            $section_id = 'section' . $section_key . 'status';
            //$sectionname = $section->sectionname;
            //$sectionname = "<span data-toggle='tooltip' data-placement='top' title='$sectionname' data-container='body'> <i class='fa fa-info-circle'></i>Sect$section_key  </span>";
            $sectionname = '';

            $complete_img = "<img data-toggle='tooltip' data-placement='top' title='Complete' data-container='body' src='" . asset('images/complete.png') . "'>";
            $incomplete_img = "<img data-toggle='tooltip' data-placement='top' title='Incomplete' data-container='body' src='" . asset('images/incomplete.png') . "'>";
            $missing_img = "<img data-toggle='tooltip' data-placement='top' title='Missing' data-container='body' src='" . asset('images/missing.png') . "'>";
            $error_img = "<img data-toggle='tooltip' data-placement='top' title='Error' data-container='body' src='" . asset('images/error.png') . "'>";

            $columns[$section_id . '_complete'] = ['data' => $section_id . '_complete', 'name' => $section_id . '_complete', 'defaultContent' => 'N/A', 'title' => $sectionname . $complete_img, 'searchable' => false, 'orderable' => false, "render" => function () use ($project, $filter, $section_id) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-success\" href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=1&section=' + encodeURI('" . $section_id . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }];
            $columns[$section_id . '_incomplete'] = ['data' => $section_id . '_incomplete', 'name' => $section_id . '_incomplete', 'defaultContent' => 'N/A', 'title' => $sectionname . $incomplete_img, 'searchable' => false, 'orderable' => false, "render" => function () use ($project, $filter, $section_id) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-warning\" href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=2&section=' + encodeURI('" . $section_id . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }];
            $columns[$section_id . '_missing'] = ['data' => $section_id . '_missing', 'name' => $section_id . '_missing', 'defaultContent' => 'N/A', 'title' => $sectionname . $missing_img, 'searchable' => false, 'orderable' => false, "render" => function () use ($project, $filter, $section_id) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-danger\" href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=0&section=' + encodeURI('" . $section_id . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }];
            $columns[$section_id . '_error'] = ['data' => $section_id . '_error', 'name' => $section_id . '_error', 'defaultContent' => 'N/A', 'title' => $sectionname . $error_img, 'searchable' => false, 'orderable' => false, "render" => function () use ($project, $filter, $section_id) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=3&section=' + encodeURI('" . $section_id . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }];
        }

        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'sampleresponsedatatables_' . time();
    }
}
