<?php

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
            ->create(['project_id' => $project->id]);
        /**
         * 'raw_ans' => $raw_ans,
        'qnum' => $qnum,
        'layout' => $layout,
        'section' => $section,
        'project_id' => $this->definitions
         */

        foreach ($questions as $question) {
            $render = $this->to_render(
                [
                    'question' => $question,
                    'section' => $question->section,
                    'project' => $project,
                ],
                [
                    'qnum' => $question->qnum,
                    'layout' => $question->layout,
                    'raw_ans' => $question->raw_ans,
                ]
            );
            $inputs = $this->getInputs($render);
            $q = $question->surveyInputs()->saveMany($inputs);
        }

    }
}
