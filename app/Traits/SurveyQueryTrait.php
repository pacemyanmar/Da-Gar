<?php
namespace App\Traits;

use App\Models\LocationMeta;
use App\Models\Project;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

trait SurveyQueryTrait {

    private $type;

    protected $sample_select = [];

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

    public function getSelectColumns($inputs = true) {

        $sample_columns = array_column($this->makeSampleColumns(), 'name');

        $section_columns = array_column($this->makeSectionColumns(),'name');

        $extras_columns = array_column($this->makeExtrasColumns(), 'name');

        if($inputs) {
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

            return array_merge($sample_columns, $section_columns, $extras_columns, array_values($input_columns));
        } else {
            return array_merge($sample_columns, $section_columns, $extras_columns);
        }

    }


    public function getSelectQuery() {

    }

    public function getDatatablesColumns($inputs = true) {
        if($inputs) {
            return array_merge($this->makeSampleColumns(), $this->makeExtrasColumns(), $this->makeSectionColumns(), $this->makeInputsColumns());
        } else {
            return array_merge($this->makeSampleColumns(), $this->makeExtrasColumns(), $this->makeSectionColumns());
        }
    }

    public function makeSectionColumns()
    {
        $auth = Auth::user();
        $sections = $this->project->sections->sortBy('sort');
        $section_columns = [];
        foreach ($sections as $k => $section) {
            $section_num = $section->sort;
            if($auth->role->role_name == 'doublechecker' || $this->type == 'double') {
                $base_dbname = 'pj_s'.$section_num.'_dbl';
            } else {
                $base_dbname = 'pj_s'.$section_num;
            }

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
                'className' => 'statuscolumns',
                'orderable' => false,
                'searchable' => true,
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

    public function makeExtrasColumns() {
        $sections = $this->project->sections->sortBy('sort');
        $extras_columns = [];
        $update_user = [];
        foreach ($sections as $k => $section) {
            $section_num = $section->sort;
            $base_dbname = 'pj_s'.$section_num;
            $sectionshort = ' R' . ($section_num + 1) . ' ';
            $update_user[] = 'IF('.$base_dbname.'.update_user_id, "'.$sectionshort.'","")';
        }

        $query = 'CONCAT('.implode(',',$update_user).') AS corrected_sections';
        $extras_columns['corrected_sections'] = [
            'name' => $query,
            'data' => 'corrected_sections',
            'orderable' => false,
            'searchable' => true,
            'width' => '40px',
            'title' => 'Corrected Sections',
        ];
        return $extras_columns;
    }

    public function makeSampleColumns() {
        // get application current locale
        $locale = App::getLocale();

        $auth = Auth::user();

        $locationMetas = $this->project->load(['locationMetas' => function($q){
            //$q->withTrashed();
            $q->orderBy('sort','ASC');
        }])->locationMetas;

        $columns = [];
        $columns['samples_id'] = [
            'name' => 'samples.id as samples_id',
            'data' => 'samples_id',
            'title' => trans('samples.samples_id'),
            'orderable' => false,
            'defaultContent' => 'N/A',
            'visible' => false,
            'width' => '80px',
        ];
        foreach ($locationMetas as $location) {

            if($location->field_type == 'primary') {
                $primaryKey = str_dbcolumn($location->label);
                $columns[$location->field_name] = [
                    'name' => 'sdv.id as '.$primaryKey,
                    'data' => $primaryKey,
                    'className' => $location->filter_type.' '.$primaryKey,
                    'title' => trans('samples.'.$location->field_name),
                    'orderable' => false,
                    'defaultContent' => 'N/A',
                    'visible' => true,
                    'width' => '80px',
                ];
                if($this->project->copies > 1) {
                    $columns['form_id'] = [
                        'name' => 'samples.form_id',
                        'data' => 'form_id',
                        'className' => $location->filter_type.' form_id',
                        'title' => trans('samples.form_id'),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'visible' => true,
                        'width' => '80px',
                    ];
                }
            } else {
                if($location->export) {
                    $columns[$location->field_name] = [
                        'name' => 'sdv.' . $location->field_name,
                        'data' => $location->field_name,
                        'className' => $location->filter_type . ' ' . $location->field_name,
                        'title' => trans('samples.' . $location->field_name),
                        'orderable' => false,
                        'defaultContent' => 'N/A',
                        'visible' => $location->show_index,
                        'width' => '80px',
                    ];
                }
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

                if($this->type == 'double') {
                    $base_dbname = 'pj_s'.$section_num.'_dbl';
                } else {
                    $base_dbname = 'pj_s'.$section_num;
                }

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
                            if($input->type == 'text') {
                                $title = title_case($input->inputid);
                            } else {
                                $title = $question->qnum . ' ' . $input->value;
                            }

                        } else {
                            $title = $question->qnum;
                        }
                        break;
                }

                $input_columns[$column] = [
                    'name' => $base_dbname. '.' . $column,
                    'data' => $column, 'title' => strtoupper($title),
                    'class' => 'result', 'orderable' => false,
                    'width' => '80px', 'type' => $input->type
                ];

                if($input->other) {
                    $input_columns[$column.'_other'] = [
                        'name' => $base_dbname. '.' . $column.'_other',
                        'data' => $column.'_other', 'title' => strtoupper($title). ' Other',
                        'class' => 'result', 'orderable' => false,
                        'visible' => false,
                        'width' => '80px', 'type' => 'text'
                    ];
                }

                if(config('sms.double_entry')) {
                    $input_columns[$column . '_dstatus'] = [
                        'name' => $base_dbname. '_dbl' . '.' . $column,
                        'data' => $column . '_dstatus', 'title' => strtoupper($title) . ' Matched',
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