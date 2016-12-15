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
    private function to_render($args = [])
    {
        $raw_ans = $args['raw_ans'];
        $project_id = $args['project_id'];
        $qnum = $args['qnum'];
        $layout = $args['layout'];
        $section = $args['section'];
        $ans = json_decode($raw_ans, true);
        $answer = [];
        /**
         * Loop json decoded key and values pair from input answers
         */
        foreach ($ans as $k => $a) {
            // $k is loop index start from 0. $a is input
            /**
             * create unique name attribute for each input
             */
            $param = str_slug('p' . $project_id . '-s' . $section . '-' . $qnum . '-i' . $k);
            /**
             * assign name attribute using format_input method
             * remove checkbox or radio class name
             */
            array_walk_recursive($a, array(&$this, 'format_input'), $param);

            if (!array_key_exists('className', $a)) {
                $a['className'] = '';
            }

            if (!array_key_exists('inputid', $a)) {
                $a['inputid'] = strtolower('s' . $section . $qnum . 'i' . $k);
            }
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
                    $av['id'] = $param . '-o' . $j;
                    // merge $a and $av to get input array as $answer
                    $answer[] = array_merge($a, $av);
                }

                continue;
            } elseif ($a['type'] == 'radio-group' && $layout == 'matrix') {
                /**
                 * if input type is radio-group and layout is matrix, add input type "radio" to each options and
                 * assign unique id attribute.
                 */
                $av = [];
                $a['type'] = 'matrix';
                foreach ($a['values'] as $j => $option) {
                    $av['type'] = 'radio';
                    $av['id'] = $param . '-o' . $j;
                    $a['values'][$j] = array_merge($option, $av);
                }

            } elseif ($a['type'] == 'radio') {
                $a['id'] = $param;
                $a['name'] = str_slug('p' . $project_id . '-s' . $section . '-' . $qnum . '-r');
                $a['inputid'] = strtolower('s' . $section . $qnum . 'ir');
            } else {
                $a['id'] = $param;
            }
            $i = $k + 1;
            $answer[] = $a;
        }
        return $answer;
    }

    public function getInputs($render)
    {
        $inputs = [];
        foreach ($render as $k => $input) {

            if ($input['type'] == 'matrix') {
                foreach ($input['values'] as $i => $value) {
                    $value['name'] = $input['name'];
                    $value['inputid'] = $input['inputid'];
                    $value['sort'] = $k . $i;
                    $inputs[] = new SurveyInput($value);
                }
            } else {
                if (!isset($input['value'])) {
                    $input['value'] = $k;
                }

                if (!isset($input['sort'])) {
                    $input['sort'] = $k;
                }

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
    private static function format_input(&$value, $key, $param)
    {
        if ($key == 'name') {
            $value = $param;
        }

        if ($key == 'className') {
            $value = preg_replace('/\s[checkbox|radio]\s/', ' ', $value);
        }
    }
}
