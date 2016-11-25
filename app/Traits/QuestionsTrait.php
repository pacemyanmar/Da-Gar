<?php
namespace App\Traits;

trait QuestionsTrait {
	/**
     * @param array $raw_ans raw json array from formBuilder input
     * @param string $qnum question number from input
     * @return array formatted answer
     */
    private function to_render($args = []) {
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
        foreach($ans as $k => $a) {
            /**
             * create unique name attribute for each input
             */
            $param = str_slug('p'.$project_id.'-s'.$section.'-'.$qnum.'-i'.$k);
            /**
             * assign name attribute using format_input method
             * remove checkbox or radio class name
             */
            array_walk_recursive($a, array(&$this,'format_input'), $param);

            if(!array_key_exists('className', $a)) {
                $a['className'] = '';
            }
            /**
             * if input type is radio-group and layout is not matrix, change input type to "radio" and
             * assign unique id attribute.
             */
            if($a['type'] == 'radio-group' && $layout != 'matrix') {
                foreach($a['values'] as $j => $av) {
                    unset($a['values']);
                    $av['type'] = 'radio';
                    $av['id'] = $param.'-o'.$j;
                    $answer[] = array_merge($a, $av);
                }

            } elseif($a['type'] == 'radio-group' && $layout == 'matrix') {
            /**
             * if input type is radio-group and layout is matrix, add input type "radio" to each options and 
             * assign unique id attribute.
             */ 
                $av = [];
                foreach($a['values'] as $j => $option) {
                    $av['type'] = 'radio';
                    $av['id'] = $param.'-o'.$j;
                    $a['values'][$j] = array_merge($option,$av);
                }

            } elseif($a['type'] == 'radio') {
                $a['id'] = $param;
                $a['name'] = str_slug('p'.$project_id.'-s'.$section.'-'.$qnum.'-r');
            }else {
                $a['id'] = $param;                
            }
            $i = $k + 1;
            $a['inputid'] = $qnum.'-'.$i;
            $answer[] = $a;
        }
        return $answer;
    }

    /**
     * Format name attribute for form input
     * @param mixed &$value 
     * @param string $key 
     * @return array
     */
    private static function format_input(&$value, $key, $param) {
        if($key == 'name') {
            $value = $param;
        }

        if($key == 'className') {
            $value = preg_replace('/[checkbox|radio]/', '', $value);
        }
    }
}