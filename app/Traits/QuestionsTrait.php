<?php
namespace App\Traits;

use App\Models\SurveyInput;

trait QuestionsTrait
{
    /**
     * @param array $raw_ans raw json array from formBuilder input
     * @param string $qnum question number from input
     * @return array formatted answer
     */
    private function to_render($args = [], $request = false)
    {
        $raw_ans = ($request['raw_ans']) ? $request['raw_ans'] : '';
        $qnum = ($request['qnum']) ? $request['qnum'] : '';
        $layout = ($request['layout']) ? $request['layout'] : '';

        $project = $args['project'];
        $project_id = $project->id;
        $section_id = $args['section'];

        $ans = json_decode($raw_ans, true);
        $question = (array_key_exists('question', $args)) ? $args['question'] : null;
        $qsort = (!empty($question)) ? $question->sort : '999'; // set sort prefix to 999 if no question sort
        $in_index = (!empty($question)) ? $question->report : false;

        $double_entry = (!empty($question)) ? $question->double_entry : false;
        $answer = [];

        /**
         * Loop json decoded key and values pair from input answers
         */
        foreach ($ans as $k => $a) {
            // $k is loop index start from 0. $a is input
            /**
             * create unique name attribute for each input
             */
            $param = str_slug('p' . $project_id . $qnum . 'i' . $k);

            /**
             * assign className attribute using format_input method
             * remove checkbox or radio class name
             */

            if (!array_key_exists('name', $a)) {
                $a['name'] = $param;
            }

            if (!array_key_exists('id', $a)) {
                $a['id'] = $param;
            }

            if (!array_key_exists('className', $a)) {
                $a['className'] = '';
            }

            if (!array_key_exists('inputid', $a)) {
                $input_index = (array_key_exists('value', $a) && is_numeric($a['value'])) ? $a['value'] : $k;
                $input_index = $input_index + 1;
                $a['inputid'] = strtolower($qnum . '_' . $input_index);
            }

            if (!array_key_exists('section', $a)) {
                $a['section'] = $section_id;
            }

            if (!array_key_exists('in_index', $a)) {
                $a['in_index'] = ($in_index) ? $in_index : false;
            }

            if (!array_key_exists('double_entry', $a)) {
                $a['double_entry'] = ($double_entry) ? $double_entry : false;
            }

            if (!array_key_exists('sort', $a)) {
                $a['sort'] = $qsort . $k;
            }

            if (array_key_exists('goto', $a)) {
                $qnumid = str_slug('qnum' . $a['goto']);
                $a['extras']['goto'] = '#' . $qnumid;
            }

            if (array_key_exists('min', $a)) {
                $a['extras']['min'] = $a['min'];
            }

            if (array_key_exists('max', $a)) {
                $a['extras']['max'] = $a['max'];
            }

            if (array_key_exists('step', $a)) {
                $a['extras']['step'] = $a['step'];
            }

            $param_array = [
                'p' => $project_id,
                's' => $section_id,
                'q' => $qnum,
                'i' => $k,
            ];

            array_walk_recursive($a, array(&$this, 'format_input'), $param_array);

            /**
             * if input type is radio-group and layout is not matrix, change input type to "radio" and
             * assign unique id attribute.
             * all inputs from radio-group get same name attributes so no need to set manually again.
             */
            if ($a['type'] == 'radio-group' && $layout != 'matrix') {
                /**
                 * $a['values'] is radio options in radio group
                 * loop through $a['values'] and remove $a['values'] from $a array
                 * $j is options index start from 0 and $av is radio inputs
                 */
                foreach ($a['values'] as $j => $av) {
                    unset($a['values']);
                    $av['type'] = 'radio';
                    $av['id'] = $param . 'o' . $j;
                    $av['sort'] = $qsort . $j;
                    if (str_contains(strtolower($av['label']), 'other') || str_contains(strtolower($av['label']), 'text')) {
                        $av['other'] = true;
                    }
                    // merge $a and $av to get input array as $answer
                    $answer[] = array_merge($a, $av);
                }

                continue; // this is require to remove 'radio-group' as input type
            } elseif ($a['type'] == 'radio-group' && $layout == 'matrix') {
                /**
                 * if input type is radio-group and layout is matrix, add input type "radio" to each options and
                 * assign unique id attribute.
                 */
                $av = [];
                $a['type'] = 'matrix';
                foreach ($a['values'] as $j => $option) {
                    $av['type'] = 'radio';
                    $av['id'] = $param . 'o' . $j;
                    $av['sort'] = $qsort . $j;

                    $a['values'][$j] = array_merge($option, $av);
                }

            } elseif ($a['type'] == 'radio') {
                $a['name'] = $a['inputid'] = str_slug($qnum);
            } elseif ($a['type'] == 'checkbox') {
                $a['name'] = str_slug('p' . $project_id . $qnum . 'c');
            } elseif ($a['type'] == 'template') {
                // if layout is form16 or form18
                // set input type as layout
                switch ($layout) {
                    case 'form16':
                        $a['type'] = $layout;
                        $a['project'] = $project;
                        break;
                    case 'form18':
                        $a['type'] = $layout;
                        $a['project'] = $project;
                        break;
                    default:
                        $a['type'] = $a['subtype'];
                        break;
                }

                $a['inputid'] = str_replace('-', '_', $a['subtype']);
                unset($a['subtype']);
            } else {
                $a['id'] = $param;
            }
            $i = $k + 1;
            $answer[] = $a;
        }
        return $answer;
    }

    /**
     * get array of inputs Instance
     * @param  array $render json array of inputs
     * @return array         array of SurveyInput::class instances
     */
    public function getInputs($render)
    {
        $inputs = [];
        $label = [];
        $lang = config('app.fallback_locale');

        foreach ($render as $k => $input) {

            if ($input['type'] == 'matrix') {
                foreach ($input['values'] as $i => $value) {
                    if ($k == false) {
                        $label[$i] = $value['label'];
                    } else {
                        $value['label'] = $label[$i];
                    }

                    if (str_contains(strtolower($value['label']), 'other') || str_contains(strtolower($value['label']), 'type')) {
                        $value['other'] = true;
                    }
                    $value['sort'] = $k . $i;
                    $value['extras']['group'] = $input['label'];
                    $value = array_merge($input, $value);
                    if (str_contains(strtolower($value['label']), 'textonly')) {
                        $value['type'] = 'text';
                        $value['inputid'] = $value['inputid'] . $i;
                    }
                    //$input['label_trans'] = json_encode([$lang => $value['label']]);
                    $inputs[] = new SurveyInput($value);
                }
            } elseif ($input['type'] == 'form16') {
                $i = 0;
                $project = $input['project'];
                $parties = explode(',', $project->parties);
                $station = [];
                $advanced = [];
                foreach ($parties as $j => $party) {
                    $station['id'] = $input['id'] . strtolower($party) . '_station';
                    $station['name'] = $input['name'] . strtolower($party) . '_station';
                    $station['sort'] = $input['sort'] . $k . $i . ($j + 1);
                    $station['type'] = 'template';
                    $station['section'] = $input['section'];
                    $station['inputid'] = $station['className'] = strtolower($party) . '_station';
                    $station['label'] = ucwords($party) . 'Station';
                    $station['value'] = '';
                    $inputs[] = new SurveyInput($station);

                    $advanced['id'] = $input['id'] . strtolower($party) . '_advanced';
                    $advanced['name'] = $input['name'] . strtolower($party) . '_advanced';
                    $advanced['sort'] = $input['sort'] . $k . $i . ($j + 1);
                    $advanced['type'] = 'template';
                    $advanced['section'] = $input['section'];
                    $advanced['inputid'] = $advanced['className'] = strtolower($party) . '_advanced';
                    $advanced['label'] = ucwords($party) . 'Station';
                    $advanced['value'] = '';
                    $inputs[] = new SurveyInput($advanced);
                    $i++;
                }

                $remark = [];

                for ($j = 1; $j < 6; $j++) {
                    $remark['id'] = $input['id'] . 'rem' . $j;
                    $remark['name'] = $input['name'] . 'rem' . $j;
                    $remark['sort'] = $input['sort'] . $k . $i . $j;
                    $remark['type'] = 'template';
                    $remark['section'] = $input['section'];
                    $remark['inputid'] = $remark['className'] = 'rem' . $j;
                    $remark['label'] = 'Remark ' . $j;
                    $remark['value'] = '';
                    $inputs[] = new SurveyInput($remark);
                    $i++;
                }

            } elseif ($input['type'] == 'form18') {
                $i = 0;
                $project = $input['project'];
                $parties = explode(',', $project->parties);

                $advanced = [];
                foreach ($parties as $j => $party) {

                    $advanced['id'] = $input['id'] . strtolower($party) . '_advanced';
                    $advanced['name'] = $input['name'] . strtolower($party) . '_advanced';
                    $advanced['sort'] = $input['sort'] . $k . $i . ($j + 1);
                    $advanced['type'] = 'template';
                    $advanced['section'] = $input['section'];
                    $advanced['inputid'] = $advanced['className'] = strtolower($party) . '_advanced';
                    $advanced['label'] = ucwords($party) . 'Station';
                    $advanced['value'] = '';
                    $inputs[] = new SurveyInput($advanced);
                    $i++;
                }

                $remark = [];

                for ($j = 1; $j < 6; $j++) {
                    $remark['id'] = $input['id'] . 'rem' . $j;
                    $remark['name'] = $input['name'] . 'rem' . $j;
                    $remark['sort'] = $input['sort'] . $k . $i . $j;
                    $remark['type'] = 'template';
                    $remark['section'] = $input['section'];
                    $remark['inputid'] = $remark['className'] = 'rem' . $j;
                    $remark['label'] = 'Remark ' . $j;
                    $remark['value'] = '';
                    $inputs[] = new SurveyInput($remark);
                    $i++;
                }

            } else {
                if (!isset($input['value'])) {
                    $input['value'] = $k;
                }

                if (!isset($input['sort'])) {
                    $input['sort'] = $k;
                }

                //$input['label_trans'] = json_encode([$lang => $input['label']]);

                $inputs[] = new SurveyInput($input);
            }
        }
        return $inputs;
    }

    /**
     * Format name attribute for form input
     * @param mixed &$value
     * @param string $key
     * @return array
     */
    private static function format_input(&$value, $key, $param_array)
    {
        $input_id = str_slug('p' . $param_array['p'] . $param_array['q'] . 'i' . $param_array['i']);
        $input_class = str_slug('qnum' . $param_array['q']);
        //$input_class = str_slug('s' . $param_array['s'] . $param_array['q']);
        /**
        if ($key == 'name') {
        $value = $param;
        }
         */
        $qids = [];
        if ($key == 'skip') {
            $qnum_array = explode(' ', $value);
            foreach ($qnum_array as $qnum) {
                $qids[] = '.' . str_slug('qnum' . $qnum);
            }
            $value = implode(',', $qids);
        }

        if ($key == 'className') {
            $new_class = preg_replace('/checkbox|radio|group|\-/', '', $value);
            $value = $input_class . ' ' . $new_class;
        }
    }
}
