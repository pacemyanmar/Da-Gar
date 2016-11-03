<?php

use App\Models\Question;
use App\Repositories\QuestionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuestionRepositoryTest extends TestCase
{
    use MakeQuestionTrait, ApiTestTrait, DatabaseTransactions;

    /**
     * @var QuestionRepository
     */
    protected $questionRepo;

    public function setUp()
    {
        parent::setUp();
        $this->questionRepo = App::make(QuestionRepository::class);
    }

    /**
     * @test create
     */
    public function testCreateQuestion()
    {
        $question = $this->fakeQuestionData();
        $createdQuestion = $this->questionRepo->create($question);
        $createdQuestion = $createdQuestion->toArray();
        $this->assertArrayHasKey('id', $createdQuestion);
        $this->assertNotNull($createdQuestion['id'], 'Created Question must have id specified');
        $this->assertNotNull(Question::find($createdQuestion['id']), 'Question with given id must be in DB');
        $this->assertModelData($question, $createdQuestion);
    }

    /**
     * @test read
     */
    public function testReadQuestion()
    {
        $question = $this->makeQuestion();
        $dbQuestion = $this->questionRepo->find($question->id);
        $dbQuestion = $dbQuestion->toArray();
        $this->assertModelData($question->toArray(), $dbQuestion);
    }

    /**
     * @test update
     */
    public function testUpdateQuestion()
    {
        $question = $this->makeQuestion();
        $fakeQuestion = $this->fakeQuestionData();
        $updatedQuestion = $this->questionRepo->update($fakeQuestion, $question->id);
        $this->assertModelData($fakeQuestion, $updatedQuestion->toArray());
        $dbQuestion = $this->questionRepo->find($question->id);
        $this->assertModelData($fakeQuestion, $dbQuestion->toArray());
    }

    /**
     * @test delete
     */
    public function testDeleteQuestion()
    {
        $question = $this->makeQuestion();
        $resp = $this->questionRepo->delete($question->id);
        $this->assertTrue($resp);
        $this->assertNull(Question::find($question->id), 'Question should not exist in DB');
    }
}
