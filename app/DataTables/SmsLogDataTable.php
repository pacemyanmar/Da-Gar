<?php

namespace App\DataTables;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Request;
use Yajra\Datatables\Services\DataTable;

class SmsLogDataTable extends DataTable
{
    public $project;

    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
            //->addColumn('action', 'sms_logs.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $select = ['sms_logs.created_at','form_code', 'from_number', 'to_number', 'event', 'content', 'status_message', 'status', 'project_id'];
        $smsLogs = SmsLog::query();
        if ($this->project) {
            $smsLogs->where('project_id', $this->project->id);
            $filter_section = Request::input('section');

            if($filter_section) {
                if($filter_section == 'unknown') {
                    $smsLogs->whereNull('section');
                } else {
                    $smsLogs->where('section', $filter_section);
                }

            }

            if ($this->project->training) {
                $smsLogs->join($this->project->dbname . '_training', 'sms_logs.result_id', '=', $this->project->dbname . '_training.id');
            } else {
                $smsLogs->join($this->project->dbname, 'sms_logs.sample_id', '=', $this->project->dbname . '.sample_id');
            }
            $inputs = $this->project->inputs->pluck('inputid')->unique();
            foreach ($inputs as $inputid) {
                if ($this->project->training) {
                    $select[] = $this->project->dbname . '_training.' . $inputid;
                } else {
                    $select[] = $this->project->dbname . '.' . $inputid;
                }
            }
        }
        $smsLogs->select($select);
        $smsLogs->orderBy('sms_logs.created_at', 'desc');
        return $this->applyScopes($smsLogs);
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
            //->addAction(['width' => '10%', 'title' => trans('messages.action')])
            ->ajax([
                'type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token(),
                ],

                'data' => '{"_method":"GET"}',])
            ->parameters([
                'dom' => 'Brtip',
                'ordering' => false,
                'scrollX' => true,
                'fixedColumns' => true,
                'processing' => false,
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
                    //'print',
                    'reset',
                    'reload',
//                    [
//                        'extend' => 'collection',
//                        'text' => '<i class="fa fa-download"></i> ' . trans('messages.export'),
//                        'buttons' => [
//                            'csv',
//                            'excel',
//                            'pdf',
//                        ],
//                    ],
                    'colvis',
                ],
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    private function getColumns()
    {
        $columns = [
            //'id' => ['name' => 'id', 'data' => 'id'],
            //'service_id' => ['name' => 'service_id', 'data' => 'service_id'],
            'timestamp' => ['name' => 'created_at', 'data' => 'created_at', 'orderable' => false, 'width' => 80],
            'from_number' => ['name' => 'from_number', 'data' => 'from_number', 'width' => 100, 'orderable' => false],
            'to_number' => ['name' => 'to_number', 'data' => 'to_number', 'width' => 100, 'orderable' => false],

        ];
        $columns['content'] = ['name' => 'content', 'data' => 'content', 'width' => 150, 'orderable' => false, "render" => function () {
            return "function ( data, type, full, meta ) {
                                    return data
                                  }, createdCell: function (td, cellData, rowData, row, col) { if(rowData.status == 'error') { $(td).addClass('danger'); } }"; // this is really dirty hack to work createdCell
        }];
        if ($this->project) {
            $sections = $this->project->sectionsDb->sortBy('sort');
            $filter_section = Request::input('section');
            foreach ($sections as $key => $section) {
                $visible = (($key + 1) == $filter_section);
                $inputs = $section->inputs->sortBy('sort')->pluck('sort', 'inputid')->unique();
                foreach ($inputs as $inputid => $sort) {
                    $columns[$inputid] = ['name' => $inputid, 'data' => $inputid, 'title' => strtoupper(studly_case($inputid)), 'visible' => $visible, 'orderable' => false, 'width' => 30, "render" => function () {
                        return "function ( data, type, full, meta ) {
                                    return data
                                  }, createdCell: function (td, cellData, rowData, row, col) { if(!cellData) { $(td).addClass('danger'); } }"; // this is really dirty hack to work createdCell
                    }];
                }
            }
        }

        $columns['form_code'] = ['name' => 'form_code', 'data' => 'form_code', 'width' => 100, 'orderable' => false];
        $columns['status_message'] = ['name' => 'status_message', 'data' => 'status_message', 'width' => '400', 'orderable' => false];
        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'smsLogs';
    }
}
