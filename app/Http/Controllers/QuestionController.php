<?php

namespace App\Http\Controllers;

use App\DataTables\QuestionDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests;
use App\Http\Requests\CreateQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Repositories\QuestionRepository;
use App\Traits\QuestionsTrait;
use Flash;
use Response;

class QuestionController extends AppBaseController
{
    use QuestionsTrait;
    /** @var  QuestionRepository */
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepo)
    {
        $this->questionRepository = $questionRepo;
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
        $input = $request->all();

        $args = [
                'raw_ans' => $request->only('raw_ans')['raw_ans'],
                'qnum' => $request->only('qnum')['qnum'],
                'layout' => $request->only('layout')['layout'],
                'project_id' => $request->only('project_id')['project_id'],
                'section' => $request->only('section')['section']
                ];
        $input['render'] = $this->to_render($args);       

        $questions = $this->questionsRepository->create($input);
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

        Flash::success('Question saved successfully.');

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

        $question = $this->questionRepository->update($request->all(), $id);

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
