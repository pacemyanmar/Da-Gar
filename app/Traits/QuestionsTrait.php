<?php
namespace App\Traits;

use App\Models\SurveyInput;
use Spatie\TranslationLoader\LanguageLine;

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
            $input_index = $k + 1;
            /**
             * create unique name attribute for each input
             */
            $param = str_slug('p' . $project_id . $qnum . 'i' . $input_index);

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
                $qindex = (array_key_exists('value', $a) && is_numeric($a['value'])) ? $a['value'] : $input_index;

                $a['inputid'] = trim(strtolower($qnum . '_' . $qindex));
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
                $a['sort'] = $qsort . $input_index;
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
                'i' => $input_index,
            ];

            array_walk_recursive($a, array(&$this, 'format_input'), $param_array);

            /**
             * if input type is radio-group and layout is not matrix, change input type to "radio" and
             * assign unique id attribute.
             * all inputs from radio-group get same name attributes so no need to set manually again.
             */
            if ($a['type'] == 'radio-group' && !in_array($layout, ['matrix','household'])) {
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
            } elseif ($a['type'] == 'checkbox-group' && $layout != 'matrix') {
                /**
                 * $a['values'] is radio options in radio group
                 * loop through $a['values'] and remove $a['values'] from $a array
                 * $j is options index start from 0 and $av is radio inputs
                 */
                foreach ($a['values'] as $j => $av) {
                    unset($a['values']);
                    $av['type'] = 'checkbox';
                    $av['id'] = $param . 'o' . $j;
                    $av['sort'] = $qsort . $j;
                    if (str_contains(strtolower($av['label']), 'other') || str_contains(strtolower($av['label']), 'text')) {
                        $av['other'] = true;
                    }
                    // merge $a and $av to get input array as $answer
                    $answer[] = array_merge($a, $av);
                }

                continue; // this is require to remove 'checkbox-group' from input type
            } elseif ($a['type'] == 'radio-group' && in_array($layout, ['matrix','household'])) {
                /**
                 * if input type is radio-group and layout is matrix, add input type "radio" to each options and
                 * assign unique id attribute.
                 */
                $av = [];
                $a['type'] = $layout;
                foreach ($a['values'] as $j => $option) {
                    $av['type'] = 'radio';
                    $av['id'] = $param . 'o' . $j;
                    $av['sort'] = $qsort . $j;

                    $a['values'][$j] = array_merge($option, $av);
                }

            } elseif ($a['type'] == 'radio') {
                $a['name'] = $a['inputid'] = trim(str_slug($qnum.'r'));
            } elseif ($a['type'] == 'single') {
                $a['type'] = 'radio';
                $a['name'] = $a['inputid'] = trim(str_slug($qnum.'r'));
            } elseif ($a['type'] == 'checkbox') {
                $a['name'] = str_slug('p' . $project_id . $qnum . 'c');
            } elseif ($a['type'] == 'check') {
                $a['type'] = 'checkbox';
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

                $a['inputid'] = trim(str_replace('-', '_', $a['subtype']));
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

        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');

        foreach ($render as $k => $input) {

            if ( in_array($input['type'], ['matrix','household']) ) {

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
                    //if (str_contains(strtolower($value['value']), 'text')) {
                    if (in_array(strtolower($value['value']), ['text', 'count', 'other'])) {
                        $value['type'] = 'text';
                        $value['inputid'] = trim($value['inputid'] . strtolower($value['value']));
                    }
                    //$input['label_trans'] = json_encode([$lang => $value['label']]);
                    $value['status'] = 'new';
                    $inputs[] = new SurveyInput($value);

                    $language_line = LanguageLine::firstOrNew([
                        'group' => 'options',
                        'key' => $value['id']
                    ]);

                    $language_line->text = [$primary_locale => $value['label'], $second_locale => $value['label']];
                    $language_line->save();

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
                    $station['inputid'] = $station['className'] = trim(strtolower($party) . '_station');
                    $station['label'] = ucwords($party) . 'Station';
                    $station['value'] = '';
                    $inputs[] = new SurveyInput($station);

                    $advanced['id'] = $input['id'] . strtolower($party) . '_advanced';
                    $advanced['name'] = $input['name'] . strtolower($party) . '_advanced';
                    $advanced['sort'] = $input['sort'] . $k . $i . ($j + 1);
                    $advanced['type'] = 'template';
                    $advanced['section'] = $input['section'];
                    $advanced['inputid'] = $advanced['className'] = trim(strtolower($party) . '_advanced');
                    $advanced['label'] = ucwords($party) . 'Station';
                    $advanced['value'] = '';
                    $inputs[] = new SurveyInput($advanced);

                    $language_line = LanguageLine::firstOrNew([
                        'group' => 'options',
                        'key' => $advanced['id']
                    ]);

                    $language_line->text = [$primary_locale => $advanced['label'], $second_locale => (array_key_exists('translation',$advanced))?$advanced['translation']:$advanced['label']];
                    $language_line->save();

                    $i++;
                }

                $remark = [];

                for ($j = 1; $j < 6; $j++) {
                    $remark['id'] = $input['id'] . 'rem' . $j;
                    $remark['name'] = $input['name'] . 'rem' . $j;
                    $remark['sort'] = $input['sort'] . $k . $i . $j;
                    $remark['type'] = 'template';
                    $remark['section'] = $input['section'];
                    $remark['inputid'] = $remark['className'] = trim('rem' . $j);
                    $remark['label'] = 'Remark ' . $j;
                    $remark['value'] = '';
                    $remark['status'] = 'new';
                    $inputs[] = new SurveyInput($remark);

                    $language_line = LanguageLine::firstOrNew([
                        'group' => 'options',
                        'key' => $remark['id']
                    ]);

                    $language_line->text = [$primary_locale => $remark['label'], $second_locale => (array_key_exists('translation',$remark))?$remark['translation']:$remark['label']];
                    $language_line->save();

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
                    $advanced['inputid'] = $advanced['className'] = trim(strtolower($party) . '_advanced');
                    $advanced['label'] = ucwords($party) . 'Station';
                    $advanced['value'] = '';
                    $advanced['status'] = 'new';
                    $inputs[] = new SurveyInput($advanced);

                    $language_line = LanguageLine::firstOrNew([
                        'group' => 'options',
                        'key' => $advanced['id']
                    ]);

                    $language_line->text = [$primary_locale => $advanced['label'], $second_locale => (array_key_exists('translation',$advanced))?$advanced['translation']:$advanced['label']];
                    $language_line->save();

                    $i++;
                }

                $remark = [];

                for ($j = 1; $j < 6; $j++) {
                    $remark['id'] = $input['id'] . 'rem' . $j;
                    $remark['name'] = $input['name'] . 'rem' . $j;
                    $remark['sort'] = $input['sort'] . $k . $i . $j;
                    $remark['type'] = 'template';
                    $remark['section'] = $input['section'];
                    $remark['inputid'] = $remark['className'] = trim('rem' . $j);
                    $remark['label'] = 'Remark ' . $j;
                    $remark['value'] = '';
                    $remark['status'] = 'new';
                    $inputs[] = new SurveyInput($remark);

                    $language_line = LanguageLine::firstOrNew([
                        'group' => 'options',
                        'key' => $remark['id']
                    ]);

                    $language_line->text = [$primary_locale => $remark['label'], $second_locale => (array_key_exists('translation',$remark))?$remark['translation']:$remark['label']];
                    $language_line->save();

                    $i++;
                }

            } else {
                if (!isset($input['value'])) {
                    switch ($input['type']) {
                        case 'text':
                        case 'textarea':
                        case 'number':
                            $input['value'] = '';
                            break;

                        default:
                            $input['value'] = $k + 1;
                            break;
                    }
                }

                if (!isset($input['sort'])) {
                    $input['sort'] = $k;
                }

                //$input['label_trans'] = json_encode([$lang => $input['label']]);
                $input['status'] = 'new';

                $language_line = LanguageLine::firstOrNew([
                    'group' => 'options',
                    'key' => $input['id']
                ]);

                $language_line->text = [$primary_locale => $input['label'], $second_locale => (array_key_exists('translation',$input))?$input['translation']:$input['label']];
                $language_line->save();

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
