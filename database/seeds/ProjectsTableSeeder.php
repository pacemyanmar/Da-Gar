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
        $seeder = $this;
        $project = factory(App\Models\Project::class, 1)->create()->each(function ($project) use ($seeder) {

            $sections = factory(App\Models\Section::class, 5)->create(['project_id' => $project->id]);
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
                $render = $seeder->to_render(
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
                $inputs = $seeder->getInputs($render);
                $q = $question->surveyInputs()->saveMany($inputs);
            }
        });

    }
}
