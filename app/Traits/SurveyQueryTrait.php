<?php
namespace App\Traits;

use App\Models\Project;
use Illuminate\Support\Facades\App;

trait SurveyQueryTrait {

    protected $sample_select = [
        'samples_id' => 'samples.id as samples_id',
        'form_id' => 'samples.form_id',
        'location_code' => 'sample_datas_view.location_code',
        'ps_code' => 'sample_datas_view.ps_code',
        'type' => 'sample_datas_view.type',
        'dbgroup' => 'sample_datas_view.dbgroup',
        'sample' => 'sample_datas_view.sample',
        'area_type' => 'sample_datas_view.area_type',
        'level1' => 'sample_datas_view.level1',
        'level1_trans' => 'sample_datas_view.level1_trans',
        'level2' => 'sample_datas_view.level2',
        'level2_trans' => 'sample_datas_view.level2_trans',
        'level3' => 'sample_datas_view.level3',
        'level3_trans' => 'sample_datas_view.level3_trans',
        'level4' => 'sample_datas_view.level4',
        'level4_trans' => 'sample_datas_view.level4_trans',
        'level5' => 'sample_datas_view.level5',
        'level5_trans' => 'sample_datas_view.level5_trans',
        'parties' => 'sample_datas_view.parties',
        'user_id' => 'user.name',
        'observer_name' => 'sample_datas_view.full_name',


    ];

    protected $columns_select = [];

    protected $project;

    protected $dbname;

    protected $tableColumns;

    protected $tableBaseColumns;

    protected $tableSectionColumns;

    protected $sampleType;

    protected $joinMethod;

    /**
     * Project Setter
     * @param  App\Models\Project $project [Project Models from route]
     * @return $this ( App\DataTables\SurveyResultsDataTable )
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        $this->dbname = $project->dbname;
        return $this;
    }

    /**
     * Survey type setter (country|region)
     * @param string $surveyType [country|region]
     * @return $this ( App\DataTables\SurveyResultsDataTable )
     */
    public function setSampleType($sampleType)
    {
        $this->sampleType = $sampleType;
        return $this;
    }

    public function setJoinMethod($join)
    {
        $this->joinMethod = $join;
        return $this;
    }

    public function getSelectColumns() {

        $sample_columns = array_column($this->makeSampleColumns(), 'name');

        $section_columns = array_column($this->makeSectionColumns(),'name');

        $input_columns = $this->makeInputsColumns();

        array_walk($input_columns, function (&$column, $column_name) {
            $old_column = $column;
            if(array_key_exists('type', $old_column)) {
                switch ($old_column['type']) {
                    case 'checkbox':
                        $column = 'IF(' . $old_column['name'] . ',1,IFNULL('.$old_column['name'].',NULL)) AS ' . $column_name;
                        break;
                    case 'double_entry':
                        $column = 'IF(' . $old_column['name']. ' = ' . $old_column['origin_name']. ', 1, 0) AS ' . $column_name;
                        break;
                    default:
                        $column = $old_column['name'];
                        break;
                }
            } else {
                $column = $old_column['name'];
            }
        });

        return array_merge($sample_columns, $section_columns, array_values($input_columns));
    }


    public function getSelectQuery() {

    }

    public function getDatatablesColumns() {
        return array_merge($this->makeSampleColumns(), $this->makeSectionColumns(), $this->makeInputsColumns());
    }

    public function makeSectionColumns() {
        $sections = $this->project->sections->sortBy('sort');
        $section_columns = [];
        foreach ($sections as $k => $section) {
            $section_num = $section->sort;
            $base_dbname = $this->dbname .'_section'.$section_num;
            $sectionColumn = 'section' . $section_num . 'status';
            $sectionname = $section['sectionname'];
            $sectionshort = 'R' . ($section_num + 1) . '';
            // if string long to show in label show as tooltip
            //if (mb_strlen($section['sectionname']) > $wordcount) {

            $sectionname = "<span data-toggle='tooltip' data-placement='top' title='$sectionname' data-container='body'> $sectionshort <i class='fa fa-info-circle'></i></span>";
            //}

            $section_columns[$sectionColumn] = [
                'name' => $base_dbname . '.' . $sectionColumn,
                'data' => $sectionColumn,
                'orderable' => false,
                'searchable' => false,
                'width' => '40px',
                'render' => function () {
                    return "function(data,type,full,meta){
                        var html;
                        if(type === 'display') {
                            if(data == 1) {
                                html = '<span class=\"glyphicon glyphicon-ok text-success\"></span>';
                            } else if(data == 2) {
                                html = '<span class=\"glyphicon glyphicon-ban-circle text-warning\"></span>';
                            } else if(data == 3) {
                                html = '<span class=\"glyphicon glyphicon-alert text-info\"></span>';
                            } else {
                                html = '<span class=\"glyphicon glyphicon-remove text-danger\"></span>';
                            }
                        } else {
                            html = data;
                        }

                        return html;
                    }";
                },
                'title' => $sectionname,
            ];


        }
        return $section_columns;
    }

    public function makeSampleColumns() {
        // get application current locale
        $locale = App::getLocale();

        $columns = [];
        foreach($this->sample_select as $column => $dbcolumn) {
            switch ($column) {
                case 'user_id':
                    $columns['user_id'] = [
                        'name' => 'user.name',
                        'data' => 'username',
                        'title' => trans('messages.user'),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'visible' => false,
                        'width' => '80px',
                    ];
                    break;
                case 'observer_name':
                    $columns['full_name'] = [
                        'name' => 'sample_datas_view.full_name',
                        'data' => 'full_name',
                        'title' => trans('sample.observer_id'),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'width' => '90px',
                        'render' => function () use ($locale) {
                            $data = ($locale == config('app.fallback_locale'))? 'data':'full.full_name_trans';
                            return "function(data,type,full,meta){
                                    var html;
                                    if(type === 'display') {

                                        if(full.full_name_trans) {
                                            html = $data;
                                        } else {
                                            html =data;
                                        }
                                    } else {
                                        html = data;
                                    }

                                    return html;
                                }";
                        },
                    ];
                    break;
                case 'location_code':
                    $columns[$column] = [
                        'name' => 'sample_datas_view.'.$column,
                        'data' => $column,
                        'title' => trans('sample.'.$column),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'visible' => false,
                        'width' => '90px',
                    ];
                    break;
                case 'call_primary':
                case 'incident_center':
                case 'sms_time':
                case 'observer_field':
                    $columns[$column] = [
                        'name' => 'sample_datas_view.'.$column,
                        'data' => $column,
                        'title' => trans('sample.'.$column),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'width' => '90px',
                    ];
                    break;
                case 'mobile':
                    $columns['phone_1'] = [
                        'name' => 'sample_datas_view.phone_1',
                        'data' => 'phone_1',
                        'title' => trans('messages.mobile'),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'width' => '90px',
                    ];
                    break;
                case 'sms_primary':
                    $columns[$column] = [
                        'name' => 'sample_datas_view.'.$column,
                        'data' => $column,
                        'title' => trans('sample.'.$column),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'width' => '90px',
                        'visible' => false
                    ];
                    break;
                case 'level1':
                case 'level3':
                    $columns[$column] = [
                        'name' => 'sample_datas_view.'.$column,
                        'data' => $column,
                        'title' => trans('sample.'.$column),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'width' => '90px',
                        'render' => function () use ($locale, $column) {
                            $data = ($locale == config('app.fallback_locale'))? 'data':'full.'.$column.'_trans';
                            return "function(data,type,full,meta){
                                    var html;
                                    if(type === 'display') {

                                        if(full.".$column."_trans) {
                                            html = $data;
                                        } else {
                                            html =data;
                                        }
                                    } else {
                                        html = data;
                                    }

                                    return html;
                                }";
                        },
                    ];
                    break;
                default:
                    $columns[$column] = [
                        'name' => $dbcolumn,
                        'data' => $column,
                        'title' => trans('messages.' . strtolower($column)),
                        'orderable' => false,
                        'visible' => false,
                        'width' => '120px',
                    ];
                    break;
            }
        }
        return $columns;
    }

    public function makeInputsColumns() {
        $project = $this->project;

        $project->load(['inputs' => function ($query) {
            $query->where('status', 'published')->orderBy('sort', 'asc');
        }]);

        $project_questions = $project->questions->sortBy('sort');

        $input_columns = [];

        foreach($project_questions as $question) {
            $inputs = $question->surveyInputs->sortBy('sort');

            $section_num = $question->sectionDb->sort;
            foreach($inputs as $input) {
                $column = $input->inputid;

                //$title = preg_replace('/s[0-9]+|ir/', '', $column);
                //$title = strtoupper(preg_replace('/i/', '_', $title));
                switch ($input->type) {
                    case 'radio':
                        $title = $question->qnum;
                        break;
                    case 'checkbox':
                        $title = $question->qnum . ' ' . $input->value;
                        break;
                    case 'template':
                        $title = $input->label;
                        break;
                    default:
                        if($inputs->count() > 1) {
                            $title = $question->qnum . ' ' . $input->value;
                        } else {
                            $title = $question->qnum;
                        }
                        break;
                }

                $base_dbname = $this->dbname .'_section'.$section_num;

                $input_columns[$column] = [
                    'name' => $base_dbname. '.' . $column,
                    'data' => $column, 'title' => $title,
                    'class' => 'result', 'orderable' => false,
                    'width' => '80px', 'type' => $input->type
                ];
                if(config('sms.double_entry')) {
                    $input_columns[$column . '_status'] = [
                        'name' => $base_dbname. '_dbl' . '.' . $column,
                        'data' => $column . '_status', 'title' => $title . '_status',
                        'orderable' => false, 'visible' => false,
                        'type' => 'double_entry',
                        'origin_name' => $base_dbname. '.' . $column
                    ];
                }

                if (!$question->report) {
                    $input_columns[$column]['visible'] = false;
                }
            }
            unset($inputs);

        }

        return $input_columns;
    }
}