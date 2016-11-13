<?php

use App\Models\SurveyInput;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$questions = factory(App\Models\Question::class, 30)->make();
        $project = factory(App\Models\Project::class)->create();
        $project->questions()->saveMany($questions);
        foreach($questions as $question) {
            $render = $question->render;
            $inputs = [];
            foreach($render as $k => $input) {
                //remove className from array
                unset($input['className']);
                if($input['type'] == 'radio-group') {
                    foreach($input['values'] as $i => $value) {
                        $value['name'] = $input['name'];
                        $value['sort'] = $k.$i;
                        $inputs[] = new SurveyInput($value);
                    } 
                } else {
                    if(!isset($input['value'])) $input['value'] = $k;
                    if(!isset($input['sort'])) $input['sort'] = $k;
                    $inputs[] = new SurveyInput($input);
                }
            }
            
            $question->surveyInputs()->saveMany($inputs);
        }
    }
}
