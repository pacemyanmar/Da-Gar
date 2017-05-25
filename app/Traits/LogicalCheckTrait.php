<?php

namespace App\Traits;


use App\Models\Question;
use App\Models\Section;


trait LogicalCheckTrait {

    /**
     * @param $result_arr   [section_id] => [question_id => [inputid => response] ] ]
     * @param $result       App\Models\SurveyResult
     * @param $project      App\Models\Project
     * @param $sample       App\Models\Sample
     * @return mixed
     */
    private function logicalCheck($result_arr, $result, $project, $sample)
    {

        $error = [];
        while (list ($section_id, $questions) = each($result_arr)) {
            $section = Section::find($section_id);
            $question_status = [];
            foreach ($questions as $question_id => $inputs) {
                $question = Question::find($question_id);

                // for checkbox and radio
                $required_response_with_value = $question->surveyInputs->filter(function ($input, $key) {
                    return (!$input->optional && $input->value);
                })->pluck('inputid')->toArray();

                // for text based inputs
                $required_response_empty_value = $question->surveyInputs->filter(function ($input, $key) {
                    return (!$input->optional && empty($input->value));
                })->pluck('inputid')->toArray();


                $intersect_with_value = array_intersect($required_response_with_value, array_keys(array_filter($inputs))); // if this is greater than zero question is complete
                $intersect_no_value = array_intersect($required_response_empty_value, array_keys(array_filter($inputs)));

                if (count($intersect_with_value) > 0 || (!empty($required_response_empty_value) && $required_response_empty_value == $intersect_no_value)) {
                    $question_complete = true;
                    $question_status[$section_id][$question->qnum] = 'complete';
                } else {
                    $question_complete = false;
                }


                // To Do:: check logical error only after each question complete
                $logics = $project->logics;
                $discard = false;
                if(!empty($logics) && $question_complete) {

                    foreach ($logics as $logic) {
                        $left = $logic->leftval;
                        $operator = $logic->operator; // equal or greater than, less than, mutual include, mutual exclude ( = , > , < , muic, muex)
                        $right = $logic->rightval;
                        $scope = $logic->scope; // in a question or cross questions or cross sections ( q , xq, xs )
                        switch ($operator) {
                            case 'muex':
                                if ($scope == 'q') {

                                    $left_ids = explode(',', $left);
                                    $left_ids_trimmed = array_map('trim', $left_ids);
                                    $left_arr = array_fill_keys($left_ids_trimmed, '');
                                    $left_values = array_filter(array_intersect_key($inputs,$left_arr));

                                    $right_ids = explode(',', $right);
                                    $right_ids_trimmed = array_map('trim', $right_ids);
                                    $right_arr = array_fill_keys($right_ids_trimmed, '');
                                    $right_values = array_filter(array_intersect_key($inputs,$right_arr));

                                    if (!empty($left_values) && !empty($right_values)) {
                                        $error[$section_id][] = $question->qnum;
                                        $discard = true;
                                    }

                                    unset($left_ids);
                                    unset($left_ids_trimmed);
                                    unset($left_arr);
                                    unset($left_values);

                                    unset($right_ids);
                                    unset($right_ids_trimmed);
                                    unset($right_arr);
                                    unset($right_values);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }

                if(!$discard) {
                    foreach($inputs as $inputid => $response) {
                        $result->{$inputid} = $response;
                    }
                }

                if(!$question_complete) {
                    if (!empty($question->observation_type) && !in_array($sample->data->observer_field, $question->observation_type)) {

                        $question_status[$section_id][$question->qnum] = 'complete';
                    } elseif (empty($intersect_with_value) && empty($intersect_no_value)) {
                        $question_status[$section_id][$question->qnum] = 'missing';

                        $error[$section_id][] = $question->qnum;
                    } else {
                        $question_status[$section_id][$question->qnum] = 'incomplete';
                        $error[$section_id][] = $question->qnum;
                    }
                }

                unset($required_response_with_value);
                unset($required_response_empty_value);

                unset($intersect_with_value);
            } // question loop

            $questions_status = array_unique(array_values($question_status[$section_id]));

            if(in_array('incomplete', $questions_status) || (count($questions_status) > 1 && count(array_intersect(['missing','complete'], $questions_status)) > 0) ) {
                $section_status = 2; // incomplete
            } elseif(count($questions_status) == 1 && $questions_status[0] == 'missing') {
                $section_status = 0; // missing
            } elseif(count($questions_status) == 1 && $questions_status[0] == 'complete') {
                $section_status = 1; // complete
            } elseif(!in_array('incomplete', $questions_status) && in_array('error', $questions_status)) {
                $section_status = 3; // error
            } else {
                $section_status = 0
                ;
            }


            $skey = $section->sort + 1;
            $result->{'section' . $skey . 'status'} = $section_status;

        }

        $checked['results'] = $result;
        $checked['error'] = $error;

        return $checked;
    }
}
