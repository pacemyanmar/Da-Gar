<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Traits\QuestionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Response;

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
        $input['css_id'] = str_slug('s' . $section_id . $input['qnum']);

        // $lang = config('app.fallback_locale');

        // $input['qnum_trans'] = json_encode([$lang => $input['qnum']]);

        // $input['question_trans'] = json_encode([$lang => $input['question']]);

        $question = $this->questionRepository->create($input);

        $args = [
            'project' => $project,
            'question' => $question,
            'section' => $section_id,
        ];

        $render = $input['render'] = $this->to_render($args, $input);
        $input['raw_ans'] = str_replace("'", "&#39;", $input['raw_ans']);
        $inputs = $this->getInputs($render);

        $question->surveyInputs()->saveMany($inputs);

        if (Schema::hasTable($project->dbname)) {
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
        $form_input['css_id'] = str_slug('s' . $section_id . $form_input['qnum']);

        $form_input['raw_ans'] = str_replace("'", "&#39;", $form_input['raw_ans']);

        $form_input['qstatus'] = 'modified';

        // $lang = config('app.fallback_locale');

        // $form_input['qnum_trans'] = json_encode([$lang => $form_input['qnum']]);

        // $form_input['question_trans'] = json_encode([$lang => $form_input['question']]);

        $new_question = $this->questionRepository->update($form_input, $id);

        $args = [
            'question' => $new_question,
            'project' => $project,
            'section' => $section_id,
        ];

        $render = $this->to_render($args, $form_input);

        $inputs = $this->getInputs($render);

        $new_question->surveyInputs()->delete();
        $new_question->surveyInputs()->saveMany($inputs);

        $project = $new_question->project;
        if (Schema::hasTable($project->dbname)) {
            $project->status = 'modified';
            $project->save();
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
