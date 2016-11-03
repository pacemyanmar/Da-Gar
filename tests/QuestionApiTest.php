<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuestionApiTest extends TestCase
{
    use MakeQuestionTrait, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function testCreateQuestion()
    {
        $question = $this->fakeQuestionData();
        $this->json('POST', '/api/v1/questions', $question);

        $this->assertApiResponse($question);
    }

    /**
     * @test
     */
    public function testReadQuestion()
    {
        $question = $this->makeQuestion();
        $this->json('GET', '/api/v1/questions/'.$question->id);

        $this->assertApiResponse($question->toArray());
    }

    /**
     * @test
     */
    public function testUpdateQuestion()
    {
        $question = $this->makeQuestion();
        $editedQuestion = $this->fakeQuestionData();

        $this->json('PUT', '/api/v1/questions/'.$question->id, $editedQuestion);

        $this->assertApiResponse($editedQuestion);
    }

    /**
     * @test
     */
    public function testDeleteQuestion()
    {
        $question = $this->makeQuestion();
        $this->json('DELETE', '/api/v1/questions/'.$question->iidd);

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/questions/'.$question->id);

        $this->assertResponseStatus(404);
    }
}
