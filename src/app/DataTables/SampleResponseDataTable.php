<?php

namespace App\DataTables;

use App\Models\Sample;
use App\Traits\SurveyQueryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;

class SampleResponseDataTable extends DataTable
{
    use SurveyQueryTrait;

    private $filter;

    private $type;

    private $track_column;

    private $track_value;

    private $section;

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

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setTrack($column,$value)
    {
        $this->track_column = $column;
        $this->track_value = $value;
        return $this;
    }

    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return datatables()
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

        $project_sample_db = $project->dbname.'_samples';

        $childTable = $project->dbname;
        $auth = Auth::user();

        if ($project->status != 'new') {
            if ($this->section) {
                $filter_section = $this->section - 1;

                if($this->type == 'double') {
                    $dbname = 'pj_s'.$filter_section.'_dbl';
                } else {
                    $dbname = 'pj_s'.$filter_section;
                }
                $total = "SUM(IF(" . $dbname . ".section" . $filter_section . "status, 1, 0))";

                $sectionColumns = $this->makeSectionColumns();

                // modify column name to use in sql query TABLE.COLUMN format
                array_walk($sectionColumns, function (&$column, $index) {
                    $columnStr = 'SUM(IF(' . $column['name'] . ' = 0 OR ' . $column['name'] . ' IS NULL, 1, 0)) AS ' . $column['data'] . '_missing';
                    $columnStr .= ', SUM(IF(' . $column['name'] . ' = 1, 1, 0)) AS ' . $column['data'] . '_complete';
                    $columnStr .= ', SUM(IF(' . $column['name'] . ' = 2, 1, 0)) AS ' . $column['data'] . '_incomplete';
                    $columnStr .= ', SUM(IF(' . $column['name'] . ' = 3, 1, 0)) AS ' . $column['data'] . '_error';
                    $column = $columnStr;
                });

                $sectionColumnsStr = implode(',', $sectionColumns);

            } else {

                $status = [];
                $complete = [];
                $incomplete = [];
                $missing = [];
                $error = [];
                $reported = [];
                $all_incomplete = [];
                $missing_except_self = [];

                foreach ($project->sections as $section) {
                    if($this->type == 'double') {
                        $dbname = 'pj_s'.$section->sort.'_dbl';
                    } else {
                        $dbname = 'pj_s'.$section->sort;
                    }
                    $status[] = 'IF( ' . $dbname . '.section' . $section->sort . 'status IS NOT NULL OR ' . $dbname . '.section' . $section->sort . 'status != 0, 1, 0) ';
                    $complete[] = 'IF( ' . $dbname . '.section' . $section->sort . 'status = 1, 1, 0) ';
                    $incomplete[] = 'IF( ' . $dbname . '.section' . $section->sort . 'status = 2, 1, 0) ';
                    // IF(pj_s0.section0status = 0 OR pj_s0.section0status IS NULL,1,0)
                    $missing[] = 'IF( ' . $dbname . '.section' . $section->sort . 'status IS NULL OR ' . $dbname . '.section' . $section->sort . 'status = 0, 1, 0) ';
                    $missing_except_self[] = $dbname . '.section' . $section->sort . 'status IS NULL OR ' . $dbname . '.section' . $section->sort . 'status = 0 ';
                    $error[] = 'IF( ' . $dbname . '.section' . $section->sort . 'status = 3, 1, 0) ';
                    $reported[] = '( ' . $dbname .'.sample_id is not null AND samples.id = ' . $dbname .'.sample_id )';
                    // IF(pj_s0.section0status = 2 OR pj_s1.section1status = 2, 1, 0)
                    $all_incomplete[] = $dbname . '.section' . $section->sort . 'status = 2';
                }

                foreach ($project->sections as $section) {
                    if ($this->type == 'double') {
                        $dbname = 'pj_s' . $section->sort . '_dbl';
                    } else {
                        $dbname = 'pj_s' . $section->sort;
                    }


                    $any_other_missing[$section->sort] = "IF( ". $dbname . ".section" . $section->sort . "status = 1 AND  IF(".implode(" OR ", $missing_except_self).",1,0), 1, 0)";
                    $one_incomplete[] = implode(' OR ', $incomplete);
                }

                // IF(pj_s0.section0status = 2 OR pj_s1.section1status = 2, 1, 0)
                $all_incomplete_expression = "IF(".implode(' OR ', $all_incomplete).", 1, 0)";

                $sections_status = implode(' + ', $status);
                $total = "SUM( IF(" . $sections_status . ",1,0) )";

                $complete_status = implode (' * ', $complete);
                $completed ="SUM( " . $complete_status . " )";

                // if at least one section incomplete, $incomplete_status will be greater than zero
                $incomplete_status = implode (' + ', $incomplete);
                $incompleted ="SUM( ".implode(" OR ", $any_other_missing)." OR ".implode(" OR ", $one_incomplete). " )";

                $missing_status = implode (' * ', $missing);
                $missed ="SUM( " . $missing_status . " ) ";

                $error_status = implode (' + ', $error);
                $incorrect ="SUM( IF(" . $error_status . ",1,0) )";

                $reported = implode( ' AND ', $reported);

                $reported_locations = "COUNT( DISTINCT CASE WHEN ".$reported." THEN sdv.id ELSE 0 END )-SUM(DISTINCT CASE WHEN ".$reported." THEN 0 ELSE 1 END)";
            }
            switch ($this->filter) {
                case 'user':
                    # code...
                    $filter = 'user';
                    $query->select('user.name AS ' . $filter,
                        DB::raw('SUM(IF(samples.id,1,0)) AS alltotal, ' . $total . ' AS total'),
                        DB::raw($sectionColumnsStr));
                    $query->groupBy($filter);
                    break;

                default:
                    # code...
                    $filter = $this->filter;
                    if ($this->section) {

                        $query->select('sdv.' . $filter,
                            DB::raw('SUM(IF(samples.id,1,0)) AS alltotal, ' . $total . ' AS total'),
                            DB::raw('GROUP_CONCAT(DISTINCT user.name) as user_name',
                                'GROUP_CONCAT(DISTINCT update_user.name) as update_user',
                                'GROUP_CONCAT(DISTINCT qc_user.name) as qc_user'),
                            DB::raw($sectionColumnsStr));
                    } else {
                        $query->select('sdv.' . $filter,
                            DB::raw('count(DISTINCT(sdv.id)) AS ltotal'),
                            DB::raw($completed. ' AS complete'),
                            DB::raw($incompleted. ' AS incomplete'),
                            DB::raw($missed. ' AS missing'),
                            DB::raw($incorrect. ' AS error'),
                            DB::raw($reported_locations.' AS rlocations'),
                            DB::raw('SUM(IF(samples.id,1,0)) AS alltotal, ' . $total . ' AS total'),
                            DB::raw('SUM(IF(samples.channel = "sms",1,0)) AS sms,SUM(IF(samples.channel = "web",1,0)) AS web'),
                            DB::raw('GROUP_CONCAT(DISTINCT user.name) as user_name',
                                'GROUP_CONCAT(DISTINCT update_user.name) as update_user',
                                'GROUP_CONCAT(DISTINCT qc_user.name) as qc_user'));
                    }

                    $query->groupBy('sdv.' . $filter);
                    break;
            }

            $query->leftjoin($project_sample_db.' as sdv', function ($join) {
                $join->on('samples.sample_data_id', 'sdv.id');
            });

            foreach ($project->sections as $k => $section) {
                if($this->type == 'double') {
                    $dbname = 'pj_s'.$section->sort.'_dbl';
                    $section_table = $childTable . '_s' . $section->sort.'_dbl';
                } else {
                    $dbname = 'pj_s'.$section->sort;
                    $section_table = $childTable . '_s' . $section->sort;
                }



                $query->leftjoin($section_table . ' AS ' . $dbname, function ($join) use ($dbname) {
                    $join->on('samples.id', '=', $dbname . '.sample_id');
                });


            }
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
        if($this->track_value) {
            $query->where($this->track_column,'=',$this->track_value);
        }
        //$query->where('sdv.sample', '<>', '0');

//        $query->orderBy('samples.sample_data_id', 'ASC');
//        $query->orderBy('samples.form_id', 'ASC');

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
            'class' => 'table table-striped table-bordered table-responsive',
        ];
        return $this->builder()
            ->setTableAttributes($tableAttributes)
            ->columns($this->getColumns())
            ->ajax(['type' => 'POST', 'data' => '{"_method":"GET"}'])
            ->parameters($this->getBuilderParameters());
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
            "$filter" => ['data' => "$filter",
                'name' => 'sdv.' . $filter,
                'orderable' => false,
                'defaultContent' => 'N/A',
                "render" => function () use ($project, $filter) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                        if(data){
                                              return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'>' + data + '</a>';
                                          } else {
                                            return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "=none> None </a>';
                                          }
                                    } else {
                                      return data;
                                    }
                                  }";
            }]
            //'user_name' => ['data' => 'user_name', 'name' => 'user.name', 'defaultContent' => 'N/A'],
            //'update_user' => ['data' => 'update_user', 'name' => 'update_user.name', 'defaultContent' => 'N/A'],
        ];


        $complete_img = "<img data-toggle='tooltip' data-placement='top' title='Complete' data-container='body' src='" . asset('images/complete.png') . "'>";
        $incomplete_img = "<img data-toggle='tooltip' data-placement='top' title='Incomplete' data-container='body' src='" . asset('images/incomplete.png') . "'>";
        $missing_img = "<img data-toggle='tooltip' data-placement='top' title='Missing' data-container='body' src='" . asset('images/missing.png') . "'>";
        $error_img = "<img data-toggle='tooltip' data-placement='top' title='Error' data-container='body' src='" . asset('images/error.png') . "'>";

        $columns["alltotal"] = ['data' => 'alltotal', 'name' => 'alltotal', 'title' => 'All Forms', 'orderable' => false, "render" => function () use ($project, $filter) {
            return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'&alltotal=1>' + data + '</a>';
                                    } else {
                                      return data;
                                    }
                                  }";
        }];
        $columns["total"] = [
            'data' => 'total',
            'name' => 'total',
            'title' => 'Response',
            'orderable' => false,
            "render" => function () use ($project, $filter) {
                return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'&total=1>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(1) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
            }
        ];
        if ($this->section) {
            $sectionColumns = [];
            foreach ($project->sections as $k => $section) {

                $section_key = $section->sort;
                $section_filter = $this->section - 1;
                if ($this->section && $section_filter != $section_key) {
                    continue;
                }

                $section_id = 'section' . $section_key . 'status';
                //$sectionname = $section->sectionname;
                //$sectionname = "<span data-toggle='tooltip' data-placement='top' title='$sectionname' data-container='body'> <i class='fa fa-info-circle'></i>Sect$section_key  </span>";
                $sectionname = '';

                $columns[$section_id . '_complete'] = ['data' => $section_id . '_complete',
                    'name' => $section_id . '_complete',
                    'defaultContent' => 'N/A',
                    'title' => $sectionname . $complete_img,
                    'searchable' => false,
                    'orderable' => false,
                    "render" => function () use ($project, $filter, $section_id, $section_key) {
                        return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-success\" href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=1&section=' + encodeURI('" . $section_key . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                    }];
                $columns[$section_id . '_incomplete'] = ['data' => $section_id . '_incomplete',
                    'name' => $section_id . '_incomplete',
                    'defaultContent' => 'N/A',
                    'title' => $sectionname . $incomplete_img,
                    'searchable' => false,
                    'orderable' => false,
                    "render" => function () use ($project, $filter, $section_id, $section_key) {
                        return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-warning\" href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=2&section=' + encodeURI('" . $section_key . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                    }];

                $columns[$section_id . '_error'] = ['data' => $section_id . '_error',
                    'name' => $section_id . '_error',
                    'defaultContent' => 'N/A',
                    'title' => $sectionname . $error_img,
                    'searchable' => false,
                    'orderable' => false,
                    "render" => function () use ($project, $filter, $section_id, $section_key) {
                        return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=3&section=' + encodeURI('" . $section_key . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                    }];
                $columns[$section_id . '_missing'] = ['data' => $section_id . '_missing',
                    'name' => $section_id . '_missing',
                    'defaultContent' => 'N/A',
                    'title' => $sectionname . $missing_img,
                    'searchable' => false,
                    'orderable' => false,
                    "render" => function () use ($project, $filter, $section_id, $section_key) {
                        return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-danger\" href=" . route('projects.surveys.index', [$project->id]) . "/?" . $filter . "='+ encodeURI(full." . $filter . ") +'&status=0&section=' + encodeURI('" . $section_key . "') + '>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(0) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                    }];
            }
        } else {

            $columns['ltotal'] = [
                'data' => 'ltotal',
                'name' => 'ltotal',
                'title' => 'Locations'];
            $columns['rlocation'] = [
                'data' => 'rlocations',
                'name' => 'rlocations',
                'title' => 'Reported Locations',
                'defaultContent' => 'N/A'
                ];
            $columns['sms'] = [
                'data' => 'sms',
                'name' => 'sms',
                'title' => 'SMS',
                'defaultContent' => 'N/A'
            ];
            $columns['web'] = [
                'data' => 'web',
                'name' => 'web',
                'title' => 'Web',
                'defaultContent' => 'N/A'
            ];

            $columns['complete'] = [
                'data' => 'complete',
                'name' => 'complete',
                'defaultContent' => 'N/A',
                "render" => function () use ($project, $filter) {
                    return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-success\" href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'&totalstatus=complete>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(1) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                }
            ];
            $columns['incomplete'] = [
                'data' => 'incomplete',
                'name' => 'incomplete',
                'defaultContent' => 'N/A',
                "render" => function () use ($project, $filter) {
                    return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-warning\" href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'&totalstatus=incomplete>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(1) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                }
            ];
            $columns['missing'] = [
                'data' => 'missing',
                'name' => 'missing',
                'defaultContent' => 'N/A',
                "render" => function () use ($project, $filter) {
                    return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a class=\"text-danger\" href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'&totalstatus=missing>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(1) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                }
            ];
            $columns['error'] = [
                'data' => 'error',
                'name' => 'error',
                'defaultContent' => 'N/A',
                "render" => function () use ($project, $filter) {
                    return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                      return '<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&" . $filter . "='+ encodeURI(full." . $filter . ") +'&totalstatus=incorrect>' + data + '<br> (' +parseFloat((parseInt(data, 10) * 100)/ parseInt(full.alltotal, 10)).toFixed(1) + '%) </a>';
                                    } else {
                                      return data;
                                    }
                                  }";
                }
            ];
        }

        return $columns;
    }

    /**
     * Get default builder parameters.
     *
     * @return array
     */
    protected function getBuilderParameters()
    {
        $project = $this->project;
        if ($this->section) {
            $dom = 'p';
            $scrollX = false;
        } else {
            $dom = 'tp';
            $scrollX = true;
        }
        $builder =  [
            'dom' => $dom,
            'scrollX' => $scrollX,
            'ordering' => false,
            'pageLength' => 200,
            'fixedColumns' => ['leftColumns' => 2],
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
                            // Disable TBODY scoll bars
                            $('.dataTables_scrollBody').css({
                                'overflow': 'hidden',
                                'border': '0'
                            });
                        
                            // Enable TFOOT scoll bars
                            $('.dataTables_scrollFoot').css('overflow', 'auto');
                        
                            // Sync TFOOT scrolling with TBODY
                            $('.dataTables_scrollFoot').on('scroll', function () {
                                $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
                            }); 
                            this.api().columns([0]).every(function () {
                                var column = this;
                                var br = document.createElement(\"br\");
                                var input = document.createElement(\"input\");
                                input.className = 'form-control';
                                input.style.width = '60px';
                                $(br).appendTo($(column.header()));
                                $(input).appendTo($(column.header()))
                                .on('change', function () {
                                    column.search($(this).val(), false, false, true).draw();
                                });
                            });
                        }"
        ];

        if(!$this->section) {

            $builder["footerCallback"] = " function ( row, data, start, end, display ) {
                            var api = this.api();
                            total = api
                                .column( 1 )
                                .data()
                                .reduce( function (a, b) {
                                    return parseInt(a, 10) + parseInt(b, 10);
                                }, 0 );
                                
                                
                            total_location = api
                                .column( 3 )
                                .data()
                                .reduce( function (a, b) {
                                    return parseInt(a, 10) + parseInt(b, 10);
                                }, 0 );    
                                
                            api.columns([1,2,7,8,9,10]).every(function(){
                                  var column = this;
                                  
                                  var sum = column
                                      .data()
                                      .reduce(function (a, b) {
                                         a = parseInt(a, 10);
                                         if(isNaN(a)){ a = 0; }

                                         b = parseInt(b, 10);
                                         if(isNaN(b)){ b = 0; }

                                         return a + b;
                                      });

                                  $(column.footer()).html('<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&totalstatus='+column.dataSrc()+'>' + sum + ' (' + parseFloat((sum * 100)/ total).toFixed(1) + '%)</a>');
                              });
                              
                            api.columns([3]).every(function(){
                                  var column = this;
                                  
                                  var sum = column
                                      .data()
                                      .reduce(function (a, b) {
                                         a = parseInt(a, 10);
                                         if(isNaN(a)){ a = 0; }

                                         b = parseInt(b, 10);
                                         if(isNaN(b)){ b = 0; }

                                         return a + b;
                                      });

                                  $(column.footer()).html('<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&totalstatus='+column.dataSrc()+'>' + sum + '</a>');
                              });
                              
                              api.columns([4]).every(function(){
                                  var column = this;
                                  
                                  var sum = column
                                      .data()
                                      .reduce(function (a, b) {
                                         a = parseInt(a, 10);
                                         if(isNaN(a)){ a = 0; }

                                         b = parseInt(b, 10);
                                         if(isNaN(b)){ b = 0; }

                                         return a + b;
                                      });

                                  $(column.footer()).html('<a href=" . route('projects.surveys.index', [$project->id]) . "/?nosample=1&totalstatus='+column.dataSrc()+'>' + sum + ' (' + parseFloat((sum * 100)/ total_location).toFixed(1) + '%)</a>');
                              });
                              
                              api.columns([5,6]).every(function(){
                                  var column = this;
                                  
                                  var sum = column
                                      .data()
                                      .reduce(function (a, b) {
                                         a = parseInt(a, 10);
                                         if(isNaN(a)){ a = 0; }

                                         b = parseInt(b, 10);
                                         if(isNaN(b)){ b = 0; }

                                         return a + b;
                                      });

                                  $(column.footer()).html(sum);
                              });

                            $(api.column(0).footer()).html('Total');
                        }";
        }

        return $builder;
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
