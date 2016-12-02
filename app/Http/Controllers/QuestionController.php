<?php

namespace App\Http\Controllers;

use App\DataTables\QuestionDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\SurveyInput;
use App\Repositories\QuestionRepository;
use App\Repositories\SurveyInputRepository;
use App\Traits\QuestionsTrait;
use Flash;
use Response;

class QuestionController extends AppBaseController
{
    use QuestionsTrait;
    /** @var  QuestionRepository */
    private $questionRepository;

    private $inputRepository;

    public function __construct(QuestionRepository $questionRepo, SurveyInputRepository $inputRepo)
    {
        $this->questionRepository = $questionRepo;
        $this->inputRepository = $inputRepo;
    }

    /**
     * Display a listing of the Question.
     *
     * @param QuestionDataTable $questionDataTable
     * @return Response
     */
    public function index(QuestionDataTable $questionDataTable)
    {
        return $questionDataTable->render('questions.index');
    }

    /**
     * Show the form for creating a new Question.
     *
     * @return Response
     */
    public function create()
    {
        return view('questions.create');
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
        $formInput = $request->all();

        $args = [
            'raw_ans' => $request->only('raw_ans')['raw_ans'],
            'qnum' => $request->only('qnum')['qnum'],
            'layout' => $request->only('layout')['layout'],
            'project_id' => $request->only('project_id')['project_id'],
            'section' => $request->only('section')['section'],
        ];
        $render = $formInput['render'] = $this->to_render($args);

        $question = $this->questionRepository->create($formInput);

        $inputs = [];
        foreach ($render as $k => $input) {

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
        /**
        if(!empty($raw_answers)) {
        $answers = [];
        foreach($raw_answers as $answer) {
        $answer['class_name'] = $answer['className'];
        $answer['project_id'] = $question->project->id;
        $answer['question_id'] = $question->id;
        $answer['user_id'] = Auth::user()->getAuthIdentifier();
        $answers[] = $answer;
        }

        DB::table('answers')->insert($answers);
        }
         */

        Flash::success('Questions saved successfully.');

        return redirect(route('questions.index'));
    }

    /**
     * Display the specified Question.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            Flash::error('Question not found');

            return redirect(route('questions.index'));
        }

        return view('questions.show')->with('question', $question);
    }

    /**
     * Show the form for editing the specified Question.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            Flash::error('Question not found');

            return redirect(route('questions.index'));
        }

        return view('questions.edit')->with('question', $question);
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
            Flash::error('Question not found');

            return redirect(route('questions.index'));
        }

        $input = $request->all();

        $args = [
            'raw_ans' => $request->only('raw_ans')['raw_ans'],
            'qnum' => $request->only('qnum')['qnum'],
            'layout' => $request->only('layout')['layout'],
            'project_id' => $request->only('project_id')['project_id'],
            'section' => $request->only('section')['section'],
        ];
        $render = $input['render'] = $this->to_render($args);

        $question = $this->questionRepository->update($input, $id);

        $inputs = [];
        foreach ($render as $k => $input) {

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

        $question->surveyInputs()->delete();
        $question->surveyInputs()->saveMany($inputs);

        Flash::success('Question updated successfully.');

        return redirect(route('questions.index'));
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
        $question = $this->questionRepository->findWithoutFail($id);

        if (empty($question)) {
            Flash::error('Question not found');

            return redirect(route('questions.index'));
        }

        $this->questionRepository->delete($id);

        Flash::success('Question deleted successfully.');

        return redirect(route('questions.index'));
    }
}
