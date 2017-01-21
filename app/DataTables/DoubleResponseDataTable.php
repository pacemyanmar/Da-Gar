<?php

namespace App\DataTables;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Services\DataTable;

class DoubleResponseDataTable extends DataTable
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
        //->addColumn('action', 'path.to.action.view')
            ->make(true, true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $project = $this->project;
        $sample = Sample::query();
        $project_inputs = $project->inputs;
        $origin_db = $project->dbname;
        $double_db = $project->dbname . '_double';
        $columns = [];
        foreach ($project_inputs as $input) {
            $column = $input->inputid;
            $columnName = preg_replace('/s[0-9]+/', '', $column, 1);
            $columns[] = $origin_db . '.' . $column . ' AS ori_' . $columnName . ',' . $double_db . '.' . $column . ' AS dou_' . $columnName;
        }

        $select_columns = implode(',', $columns);
        $sample->select('sample_datas.idcode', 'samples.form_id', DB::raw($select_columns));

        $sample->leftjoin('sample_datas', function ($join) {
            $join->on('samples.sample_data_id', 'sample_datas.id');
        });

        $sample->leftjoin($project->dbname, function ($join) use ($project) {
            $join->on('samples.id', '=', $project->dbname . '.sample_id');
        })
            ->leftjoin($project->dbname . '_double', function ($join) use ($project) {
                $join->on('samples.id', '=', $project->dbname . '_double.sample_id');
            })
            ->where('project_id', $project->id);

        return $this->applyScopes($sample);
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
            ->ajax([
                'type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token(),
                ],

                'data' => '{"_method":"GET"}',
            ])
        //->addAction(['width' => '80px'])
            ->parameters($this->getBuilderParameters());
    }

    protected function getBuilderParameters()
    {
        return [
            'dom' => 'Brtip',
            'ordering' => false,
            'searching' => false,
            //'autoWidth' => false,
            //'sServerMethod' => 'POST',
            'scrollX' => true,
            //'fixedColumns' => true,
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
                'print',
                'reset',
                'reload',
                [
                    'extend' => 'collection',
                    'text' => '<i class="fa fa-download"></i> ' . trans('messages.export'),
                    'buttons' => [
                        'csv',
                        'excel',
                        'pdf',
                    ],
                ],
                'colvis',
            ],
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
        $project_inputs = $project->inputs;
        $origin_db = $project->dbname;
        $double_db = $project->dbname . '_double';
        $columns = ['idcode', 'form_id'];
        $visibality = true;
        foreach ($project_inputs as $k => $input) {
            if ($k > 30) {
                $visibality = false;
            }
            if ($k > 90) {
                break;
            }
            $column = $input->inputid;
            $columnName = preg_replace('/s[0-9]+/', '', $column, 1);
            $columns['ori_' . $columnName] = ['data' => 'ori_' . $columnName, 'name' => 'ori_' . $columnName, 'title' => title_case('(1) ' . $columnName), 'defaultContent' => 'N', 'searchable' => false, 'visibality' => $visibality];
            $columns['dou_' . $columnName] = ['data' => 'dou_' . $columnName, 'name' => 'dou_' . $columnName, 'title' => title_case('(2) ' . $columnName), 'defaultContent' => 'N', 'searchable' => false, 'visibality' => $visibality];
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
        return 'doubleresponsedatatables_' . time();
    }
}
