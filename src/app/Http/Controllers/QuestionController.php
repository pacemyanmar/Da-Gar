<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Traits\QuestionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Response;
use Spatie\TranslationLoader\LanguageLine;

class QuestionController extends AppBaseController
{
    use QuestionsTrait;

    private $projectRepository;
    /** @var  QuestionRepository */
    private $questionRepository;

    private $inputRepository;

    public function __construct(ProjectRepository $projectRepo, QuestionRepository $questionRepo, SurveyInputRepository $inputRepo)
    {
        $this->middleware('auth');
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->inputRepository = $inputRepo;
    }

    /**
     * Store a newly created Question in storage.
     *
     * @param CreateQuestionRequest $request
     *
     * @return Response
     */
    public function store(CreateQuestionRequest $request)
    {
        $input = $request->all();
        $project_id = $request->only('project_id')['project_id'];
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            return $this->sendResponse($project_id, 'Project not found.');
        }
        $section_id = $request->only('section')['section'];

        $section = (isset($project->sections[$section_id])) ? $project->sections[$section_id] : '';

        if (!empty($section)) {
            $input['double_entry'] = (isset($section['double'])) ? $section['double'] : false;
        }

        $unique = uniqid();
        $short_unique = substr($unique, 0, 3);

        if(!$request->input('qnum') && $request->input('layout') == 'description') {
            $input['qnum'] = 'desc_'.$short_unique;
        }


        $input['css_id'] = str_slug('qnum' . $input['qnum']);

        // $lang = config('app.fallback_locale');

        // $input['qnum_trans'] = json_encode([$lang => $input['qnum']]);

        // $input['question_trans'] = json_encode([$lang => $input['question']]);
        $input['raw_ans'] = strip_tags($input['raw_ans']);

        $question = Question::create($input);

        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');
        $language_line = LanguageLine::firstOrNew([
            'group' => 'questions',
            'key' => $question->id.$question->qnum
        ]);

        $language_line->text = [$primary_locale => $question->question, $second_locale => $question->question];
        $language_line->save();

        $args = [
            'project' => $project,
            'question' => $question,
            'section' => $section_id,
        ];

        $render = $input['render'] = $this->to_render($args, $input);

        $input['raw_ans'] = str_replace("'", "&#39;", $input['raw_ans']);
        $inputs = $this->getInputs($render);

        $question->surveyInputs()->saveMany($inputs);

        if ($request->input('qnum') && $project->status == 'published') {
            $project->status = 'modified';
            $project->save();
        }

        return $this->sendResponse($question->toArray(), 'Question saved successfully');
    }

    /**
     * Update the specified Question in storage.
     *
     * @param  int              $id
     * @param UpdateQuestionRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateQuestionRequest $request)
    {
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            return $this->sendError('Question not found');
        }

        $form_input = $request->all();

        $project_id = $request->only('project_id')['project_id'];
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            return $this->sendResponse($project_id, 'Project not found.');
        }

        $section_id = $request->only('section')['section'];

        $section = (isset($project->sections[$section_id])) ? $project->sections[$section_id] : '';
        $double_entry = $form_input['double_entry'] = (isset($form_input['double_entry'])) ? $form_input['double_entry'] : false;
        if (!empty($section)) {
            $form_input['double_entry'] = (isset($section['double'])) ? $section['double'] : $double_entry;
        }


        $short_unique = time();

        if(!$request->input('qnum') && $request->input('layout') == 'description') {
            $form_input['qnum'] = 'desc_'.$short_unique;
        }
        
        $form_input['css_id'] = str_slug('qnum' . $form_input['qnum']);
        $raw_ans = json_decode($form_input['raw_ans']);
        array_walk_recursive($raw_ans, function($item, $key){ $item->label = strip_tags(html_entity_decode($item->label)); return $item;});
        $form_input['raw_ans'] = json_encode($raw_ans);
        $form_input['raw_ans'] = str_replace("'", "&#39;", $form_input['raw_ans']);

        $need_rebuild = false;

        if ($question->qnum != $form_input['qnum']) {
            $need_rebuild = true;
        }

        if ($question->raw_ans != $form_input['raw_ans']) {
            $need_rebuild = true;
        }

        if ($need_rebuild) {
            $form_input['qstatus'] = 'modified';
        }

        // $lang = config('app.fallback_locale');

        // $form_input['qnum_trans'] = json_encode([$lang => $form_input['qnum']]);

        // $form_input['question_trans'] = json_encode([$lang => $form_input['question']]);

        $new_question = $this->questionRepository->update($form_input, $id);

        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');
        $language_line = LanguageLine::firstOrNew([
            'group' => 'questions',
            'key' => $new_question->id.$new_question->qnum
        ]);

        $language_line->text = [$primary_locale => $new_question->question, $second_locale => $new_question->question];
        $language_line->save();

        if ($need_rebuild) {
            $args = [
                'question' => $new_question,
                'project' => $project,
                'section' => $section_id,
            ];

            $render = $this->to_render($args, $form_input);

            $inputs = $this->getInputs($render);
            $new_question->surveyInputs()->delete();
            $new_question->surveyInputs()->saveMany($inputs);
            $new_question->save();

            $project = $new_question->project;
            if ($request->input('qnum') && $project->status == 'published') {
                $project->status = 'modified';
                $project->save();
            }
        }

        return $this->sendResponse($question->toArray(), 'Question updated successfully');
    }

    /**
     * Remove the specified Question from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Question $question */
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            return $this->sendError('Question not found');
        }
        if ($question->project->status != 'new') {
            if (Schema::hasTable($question->project->dbname)) {
                $inputs = $question->surveyInputs;
                foreach ($inputs as $input) {
                    if (Schema::hasColumn($question->project->dbname, $input->inputid)) {
                        Schema::table($question->project->dbname, function ($table) use ($input) {
                            $table->dropColumn($input->inputid);
                        });
                    }
                }
            }
        }
        $question->surveyInputs()->delete();
        $question->delete();

        return redirect()->back();
    }

    public function sort(Request $request)
    {
        $sort = $request->only('sort');
        $section = $request->only('section');
        foreach ($sort['sort'] as $key => $qid) {
            $question = $this->questionRepository->findWithoutFail($qid);
            $question->sort = $section['section'] . $key;
            $question->save();
        }
    }
}
