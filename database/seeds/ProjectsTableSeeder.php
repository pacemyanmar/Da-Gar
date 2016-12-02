<?php

use App\Models\SurveyInput;
use App\Traits\QuestionsTrait;
use Illuminate\Database\Seeder;

class ProjectsTableSeeder extends Seeder
{
    use QuestionsTrait;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $project = factory(App\Models\Project::class)->create();
        $questions = factory(App\Models\Question::class, 'question', 30)
            ->create(['project_id' => $project->id])
            ->each(function ($question) use ($project) {
                $question->update(['render' => $this->to_render([
                    'qnum' => $question->qnum,
                    'layout' => $question->layout,
                    'section' => $question->section,
                    'project_id' => $project->id,
                    'raw_ans' => $question->raw_ans,
                ])]);
            });
        /**
         * 'raw_ans' => $raw_ans,
        'qnum' => $qnum,
        'layout' => $layout,
        'section' => $section,
        'project_id' => $this->definitions



         */

        foreach ($questions as $question) {
            $render = $question->render;
            $inputs = [];
            foreach ($render as $k => $input) {
                //remove className from array
                unset($input['className']);
                if ($input['type'] == 'radio-group') {
                    foreach ($input['values'] as $i => $value) {
                        $value['name'] = $input['name'];
                        $value['sort'] = $question->sort . $k;
                        $inputs[] = new SurveyInput($value);
                    }
                } else {
                    if (!isset($input['value'])) {
                        $input['value'] = $k;
                    }

                    if (!isset($input['sort'])) {
                        $input['sort'] = $question->sort . $k;
                    }

                    $inputs[] = new SurveyInput($input);
                }
            }

            $question->surveyInputs()->saveMany($inputs);
        }
    }
}
