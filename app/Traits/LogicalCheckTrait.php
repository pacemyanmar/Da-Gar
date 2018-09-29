<?php

namespace App\Traits;


use App\Models\Question;
use App\Models\Section;
use App\Models\SurveyResult;


trait LogicalCheckTrait
{
    protected $errorBag;
    protected $skipBag = [];
    private $sectionErrorBag;
    protected $sectionStatus;

    protected function processUserInput($questions, $results)
    {
        $result_arr = [];

        $question_result = [];

        $allResults = [];
        foreach ($questions as $question) {
            $qid = $question->id;
            $inputs = $question->surveyInputs;

            $question_inputs = $question->surveyInputs->pluck('value', 'inputid');

            $question_has_result_submitted = array_intersect_key((array)$results, $question_inputs->toArray());


            if (count($question_has_result_submitted) > 0) {
                if (!array_key_exists($question->id, $question_result)) {
                    $question_result[$question->id] = true;
                }
            }

            foreach ($inputs as $input) {
                $inputid = $input->inputid;
                // $result = submitted form data
                // look for individual inputid in $result array submitted or not
                if (array_key_exists($input->inputid, $results)) {
                    // if found, question is summitted and set checkbox values to zero if false
                    if ($input->type == 'checkbox') {
                        $result_arr[$qid][$inputid] = ($results[$inputid]) ? $results[$inputid] : 0;
                    } else {
                        // if value is string 0 or some value not false
                        $result_arr[$qid][$inputid] = ($results[$inputid] === '0' || $results[$inputid]) ? $results[$inputid] : null;
                    }
                } else {

                    if ($input->type == 'checkbox') {

                        if (count($question_has_result_submitted) > 0) {
                            $result_arr[$qid][$inputid] = 0;
                        } else {
                            $result_arr[$qid][$inputid] = null;
                        }

                    } else {
                        $result_arr[$qid][$inputid] = null;
                    }

                }
                if($input->other) {
                    $result_arr[$qid][$inputid.'_other'] = (array_key_exists($inputid.'_other', $results))?$results[$inputid.'_other']:null;
                }

                $this->logicalCheck($input, $result_arr[$qid][$inputid]);
            }

            if(array_key_exists($question->qnum, $this->errorBag)) {
                $this->getQuestionStatus($this->errorBag[$question->qnum], $question->qnum);
            }

            if(array_key_exists($qid, $result_arr)) {
                $allResults += $result_arr[$qid];
            }
        }

        return $allResults;

    }

    protected function logicalCheck($input, $value)
    {
        // $status => 1 : complete, 2 : missing, 3 : error, 0 :unknown or empty

        if($value === '0' || $value || $input->optional) {
            // if value not empty, set status as complete
            $this->errorBag[$input->question->qnum][$input->id] = 1;
            if($input->skip) {
                $qnums = array_filter(explode('.qnum', $input->skip));
                array_walk($qnums, function(&$qnum, $key) {
                    $qnum = preg_replace('/[^a-zA-Z0-9]+/','', $qnum);
                });
                if(in_array($input->type,['checkbox','radio'])) {
                    if($value == $input->value) {
                        $this->skipBag += array_flip($qnums);
                    }
                } else {
                    $this->skipBag += array_flip($qnums);
                }
            }
        }

        if(!$input->optional && $value !== '0' && !$value) {
            // if value is null and input is required, set status as missing
            $this->errorBag[$input->question->qnum][$input->id] = 2;
        }

        if(!empty($this->skipBag) && array_key_exists(strtolower($input->question->qnum), $this->skipBag)) {
            $this->errorBag[$input->question->qnum][$input->id] = 1;
        }
    }

    private function getQuestionStatus($qError, $qnum)
    {

        $unique_error = array_unique($qError);
        $error_count = count($unique_error);

        if (1 == $error_count) {
            $this->sectionErrorBag[$qnum] = array_shift($qError); //this can be any of 1,2,3
        }

        if ($error_count > 1) {
            // if no error and missing, set status as missing (2)
            if (!in_array(3, $qError) && in_array(2, $qError)) {
                $this->sectionErrorBag[$qnum] = 2; // missing or incomplete
            }
            // if at least 1 input complete, set question status as complete (1)
            if (in_array(1, $qError)) {
                $this->sectionErrorBag[$qnum] = 1;
            }
            // if there is error on one input, set question as error (3)
            if (in_array(3, $qError)) {
                $this->sectionErrorBag[$qnum] = 3; // error
            }

        }
    }

    private function getSectionStatus()
    {
        $unique_error = array_unique($this->sectionErrorBag);
        $error_count = count($unique_error);
        // if all questions have same status, set that status to section
        // section must be set as complete only after all questions complete
        if (1 == $error_count) {
            $error_code = array_shift($this->sectionErrorBag); //this can be any of 1,2,3
            if($error_code == 2) {
                return 0;
            } else {
                return $error_code;
            }
        }
        // if questions have different status
        if ($error_count > 1) {
            // if one question has error, set section status as error (3)
            if (in_array(3, $this->sectionErrorBag)) {
                return 3; // error
            }
            // if no error and if one question missing/incomplete, set section status as incomplete (2)
            if (!in_array(3, $this->sectionErrorBag) && in_array(2, $this->sectionErrorBag)) {
                return 2; // missing or incomplete
            }

        }
    }


    /**
     * private $originTable;
     *
     * private $doubleTable;
     *
     * private $saveSample;
     *
     * private $saveResults;
     */

    private function saveResults($table)
    {
        $sample = $this->sample;

        $sample->setRelatedTable($table);

        $surveyResult = $sample->resultWithTable()->first();

        if (empty($surveyResult)) {

            $surveyResult = new SurveyResult();

        }

        $surveyResult->setTable($table);

        if(Auth()->user()) {

            if (Auth()->user()->role->role_name == 'doublechecker') {
                $sample->qc_user_id = Auth()->user()->id;
            }

            if ($surveyResult->user_id && (in_array(Auth()->user()->code, [998, 999]) || Auth()->user()->role->level > 5)) {
                $sample->update_user_id = $surveyResult->update_user_id = Auth()->user()->id;
            } else {
                $sample->user_id = $surveyResult->user_id = Auth()->user()->id;
            }
        } else {
            $sample->user_id = 2;
        }

        $sample->save();

        $surveyResult->sample()->associate($sample);

        $surveyResult->{$this->section} = $this->sectionStatus = $this->getSectionStatus();

        $surveyResult->sample_type = $this->sampleType;

        $surveyResult->forceFill($this->results);

        $surveyResult->save();
    }


    /**
     * @param $result_arr [section_id] => [question_id => [inputid => response] ] ]
     * @param $result       App\Models\SurveyResult
     * @param $project      App\Models\Project
     * @param $sample       App\Models\Sample
     * @return mixed
     */
    private function logicalCheckAll($result_arr, $result, $project, $sample)
    {

        $error = [];

        $all_inputs = $this->array_flatten($result_arr);

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


                $intersect_with_value = array_intersect($required_response_with_value, array_keys(array_filter($inputs,function($value) {
                    return ($value !== null && $value !== false && $value !== '');
                })));
                // if this is greater than zero question is complete
                $intersect_no_value = array_intersect($required_response_empty_value, array_keys(array_filter($inputs,function($value) {
                    return ($value !== null && $value !== false && $value !== '');
                })));

//                array_walk($inputs, function(&$value, $key, $result){
//                    if(empty($value)) {
//                        $value = $result->{$key};
//                    }
//                }, $result);

                if (!empty($question->observation_type) && !in_array($sample->data->observer_field, $question->observation_type)) {
                    $question_complete = true;
                    $question_status[$section_id][$question->qnum] = '';
                } elseif (count($intersect_with_value) > 0 || (!empty($required_response_empty_value) && $required_response_empty_value == $intersect_no_value)) {
                    $question_complete = true;
                    $question_status[$section_id][$question->qnum] = 'complete';
                } else {
                    $question_complete = false;
                }


                // To Do:: check logical error only after each question complete
                $logics = $project->logics;
                $discard = false;
                if (!empty($logics) && $question_complete) {

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

                                    array_walk($left_arr, function (&$value, $key, $result) {
                                        $value = $result->{$key};
                                    }, $result);


                                    $left_values = array_filter(array_intersect_key($inputs, $left_arr));

                                    $right_ids = explode(',', $right);
                                    $right_ids_trimmed = array_map('trim', $right_ids);
                                    $right_arr = array_fill_keys($right_ids_trimmed, '');
                                    array_walk($right_arr, function (&$value, $key, $result) {
                                        $value = $result->{$key};
                                    }, $result);
                                    $right_values = array_filter(array_intersect_key($inputs, $right_arr));

                                    if (!empty($left_values) && !empty($right_values)) {
                                        $error[$section_id][] = $question->qnum;
                                        $discard = true;
                                        $question_complete = false;
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
                            case 'between':

                                    $leftval = ($all_inputs[$left])? $all_inputs[$left]:$result->{$left};
                                    $right_values = explode(',', $right);
                                    if (!is_numeric($right_values[0])) {
                                        $min = ($all_inputs[$right_values[0]])? $all_inputs[$right_values[0]]:$result->{$right_values[0]};
                                    } else {
                                        $min = $right_values[0];
                                    }

                                    if (!is_numeric($right_values[1])) {
                                        $max = ($all_inputs[$right_values[1]])? $all_inputs[$right_values[1]]:$result->{$right_values[1]};
                                    } else {
                                        $max = $right_values[1];
                                    }

                                if(array_key_exists($left, $result_arr[$section_id][$question->id]) || array_key_exists($right_values[0], $result_arr[$section_id][$question->id]) || array_key_exists($right_values[1], $result_arr[$section_id][$question->id])) {
                                    if($leftval) {
                                        if ($max > $min) {

                                            if ($leftval > $max || $leftval < $min) {
                                                if ($scope != 'q') {
                                                    $question_status[$section_id][$question->qnum] = 'error';
                                                }
                                                $error[$section_id][] = $question->qnum;
                                                $discard = true;
                                            }
                                        }

                                        if ($min > $max) {
                                            if ($leftval < $max || $leftval > $min) {
                                                if ($scope != 'q') {
                                                    $question_status[$section_id][$question->qnum] = 'error';
                                                }
                                                $error[$section_id][] = $question->qnum;
                                                $discard = true;
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'min':
                                $leftval = ($all_inputs[$left])? $all_inputs[$left]:$result->{$left};

                                if($leftval) {

                                    if (array_key_exists($left, $result_arr[$section_id][$question->id]) && $leftval < $right) {
                                        if ($scope != 'q') {
                                            $question_status[$section_id][$question->qnum] = 'error';
                                        }
                                        $error[$section_id][] = $question->qnum;
                                        $discard = true;
                                    }
                                }
                                break;
                            case 'max':
                                $leftval = ($all_inputs[$left])? $all_inputs[$left]:$result->{$left};

                                if($leftval) {

                                    if (array_key_exists($left, $result_arr[$section_id][$question->id]) && $leftval > $right) {
                                        if ($scope != 'q') {
                                            $question_status[$section_id][$question->qnum] = 'error';
                                        }
                                        $error[$section_id][] = $question->qnum;
                                        $discard = true;
                                    }
                                }
                                break;
                            case 'equalto':
                                if($scope != 'q') {
                                    $leftval = ($all_inputs[$left]) ? $all_inputs[$left] : $result->{$left};
                                } else {
                                    $leftval = ($all_inputs[$left]) ? $all_inputs[$left] : null;
                                }
                                if($leftval) {
                                    if (array_key_exists($left, $result_arr[$section_id][$question->id]) && $leftval != $right) {
                                        $question_status[$section_id][$question->qnum] = 'error';
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }

                if (!$discard) {
                    foreach ($inputs as $inputid => $response) {
                        $result->{$inputid} = $response;
                    }
                }

                if (!$question_complete) {
                    if (empty($intersect_with_value) && empty($intersect_no_value)) {
                        $question_status[$section_id][$question->qnum] = 'missing';
                        if(!$question->optional) {
                            $error[$section_id][] = $question->qnum;
                        }
                    } else {
                        $question_status[$section_id][$question->qnum] = 'incomplete';
                        if(!$question->optional) {
                            $error[$section_id][] = $question->qnum;
                        }
                    }
                }

                unset($required_response_with_value);
                unset($required_response_empty_value);

                unset($intersect_with_value);
                unset($intersect_no_value);
            } // question loop

            $questions_status = array_filter(array_unique(array_values($question_status[$section_id])));

            if (in_array('incomplete', $questions_status) || (count($questions_status) > 1 && count(array_intersect(['missing', 'complete'], $questions_status)) > 1)) {
                $section_status = 2; // incomplete
            } elseif (count($questions_status) == 1 && $questions_status[0] == 'missing') {
                $section_status = 0; // missing
            } elseif (count($questions_status) == 1 && $questions_status[0] == 'complete') {
                $section_status = 1; // complete
            } elseif (!in_array('incomplete', $questions_status) && in_array('error', $questions_status)) {
                $section_status = 3; // error
            } else {
                $section_status = 0;
            }

            $skey = $section->sort + 1;
            $result->{'section' . $skey . 'status'} = $section_status;

        }

        $this->error_bag = $error;

        return $result;
    }



    private function array_flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a,$b) use (&$return) { $return[$b] = $a; });
        return $return;
    }

}
