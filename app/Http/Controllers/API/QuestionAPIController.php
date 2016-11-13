<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateQuestionAPIRequest;
use App\Http\Requests\API\UpdateQuestionAPIRequest;
use App\Models\Question;
use App\Models\SurveyInput;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Traits\QuestionsTrait;
use Illuminate\Http\Request;
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
    /** @var  QuestionRepository */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepo, SurveyInputRepository $inputRepo)
    {
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

        $args = [
                'raw_ans' => $request->only('raw_ans')['raw_ans'],
                'qnum' => $request->only('qnum')['qnum'],
                'layout' => $request->only('layout')['layout'],
                'project_id' => $request->only('project_id')['project_id'],
                'section' => $request->only('section')['section']
                ];
        $render = $input['render'] = $this->to_render($args);       

        $questions = $this->questionRepository->create($input);
        $inputs = [];
        foreach($render as $k => $input) {

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
        /**
        if(!empty($raw_answers)) {
            $answers = [];
            foreach($raw_answers as $answer) {
                $answer['class_name'] = $answer['className'];
                $answer['project_id'] = $questions->project->id;
                $answer['question_id'] = $questions->id;
                $answer['user_id'] = Auth::user()->getAuthIdentifier(); 
                $answers[] = $answer;
            }

            DB::table('answers')->insert($answers);
        }
        */

        return $this->sendResponse($questions->toArray(), 'Question saved successfully');
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
        $questions = $this->questionRepository->findWithoutFail($id);

        if (empty($questions)) {
            return $this->sendError('Question not found');
        }
        $project_id = $request->only('project_id')['project_id'];
        $form_input = $request->all();
        $args = [
                'raw_ans' => $request->only('raw_ans')['raw_ans'],
                'qnum' => $request->only('qnum')['qnum'],
                'layout' => $request->only('layout')['layout'],
                'project_id' => $request->only('project_id')['project_id'],
                'section' => $request->only('section')['section']
                ];
        $render = $form_input['render'] = $this->to_render($args);

        $question = $this->questionRepository->update($form_input, $id);

        $inputs = [];
        foreach($render as $k => $input) {

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

        $question->surveyInputs()->delete();
        $question->surveyInputs()->saveMany($inputs);

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
