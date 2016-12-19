<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateQuestionAPIRequest;
use App\Http\Requests\API\UpdateQuestionAPIRequest;
use App\Models\Question;
use App\Repositories\ProjectRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Traits\QuestionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class QuestionController
 * @package App\Http\Controllers\API
 */

class QuestionAPIController extends AppBaseController
{
    use QuestionsTrait;

    private $projectRepository;
    /** @var  QuestionRepository */
    private $questionRepository;

    private $inputRepository;

    public function __construct(ProjectRepository $projectRepo, QuestionRepository $questionRepo, SurveyInputRepository $inputRepo)
    {
        $this->projectRepository = $projectRepo;
        $this->questionRepository = $questionRepo;
        $this->inputRepository = $inputRepo;
    }

    /**
     * Display a listing of the Question.
     * GET|HEAD /questions
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->questionRepository->pushCriteria(new RequestCriteria($request));
        $this->questionRepository->pushCriteria(new LimitOffsetCriteria($request));
        $questions = $this->questionRepository->all();

        return $this->sendResponse($questions->toArray(), 'Questions retrieved successfully');
    }

    /**
     * Store a newly created Question in storage.
     * POST /questions
     *
     * @param CreateQuestionAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateQuestionAPIRequest $request)
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
            $input['double'] = (isset($section['double'])) ? $section['double'] : false;
        }

        $args = [
            'raw_ans' => $request->only('raw_ans')['raw_ans'],
            'qnum' => $request->only('qnum')['qnum'],
            'layout' => $request->only('layout')['layout'],
            'project' => $project,
            'section' => $section_id,
        ];
        $render = $input['render'] = $this->to_render($args);

        $question = $this->questionRepository->create($input);

        $inputs = $this->getInputs($render);

        $question->surveyInputs()->saveMany($inputs);

        return $this->sendResponse($question->toArray(), 'Question saved successfully');
    }

    /**
     * Display the specified Question.
     * GET|HEAD /questions/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Question $question */
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            return $this->sendError('Question not found');
        }

        return $this->sendResponse($question->toArray(), 'Question retrieved successfully');
    }

    /**
     * Update the specified Question in storage.
     * PUT/PATCH /questions/{id}
     *
     * @param  int $id
     * @param UpdateQuestionAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateQuestionAPIRequest $request)
    {
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            return $this->sendError('Question not found');
        }

        $form_input = $request->all();
        $input = $request->all();

        $project_id = $request->only('project_id')['project_id'];
        $project = $this->projectRepository->findWithoutFail($project_id);

        if (empty($project)) {
            return $this->sendResponse($project_id, 'Project not found.');
        }

        $section_id = $request->only('section')['section'];

        $section = (isset($project->sections[$section_id])) ? $project->sections[$section_id] : '';

        if (!empty($section)) {
            $input['double'] = (isset($section['double'])) ? $section['double'] : false;
        }

        $args = [
            'raw_ans' => $request->only('raw_ans')['raw_ans'],
            'question' => $question,
            'qnum' => $request->only('qnum')['qnum'],
            'layout' => $request->only('layout')['layout'],
            'project' => $project,
            'section' => $section_id,
        ];

        $render = $form_input['render'] = $this->to_render($args);

        $new_question = $this->questionRepository->update($form_input, $id);

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
     * DELETE /questions/{id}
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

        $question->delete();

        return $this->sendResponse($id, 'Question deleted successfully');
    }
}
