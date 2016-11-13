<?php

use App\Models\SurveyInput;
use App\Repositories\SurveyInputRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SurveyInputRepositoryTest extends TestCase
{
    use MakeSurveyInputTrait, ApiTestTrait, DatabaseTransactions;

    /**
     * @var SurveyInputRepository
     */
    protected $surveyInputRepo;

    public function setUp()
    {
        parent::setUp();
        $this->surveyInputRepo = App::make(SurveyInputRepository::class);
    }

    /**
     * @test create
     */
    public function testCreateSurveyInput()
    {
        $surveyInput = $this->fakeSurveyInputData();
        $createdSurveyInput = $this->surveyInputRepo->create($surveyInput);
        $createdSurveyInput = $createdSurveyInput->toArray();
        $this->assertArrayHasKey('id', $createdSurveyInput);
        $this->assertNotNull($createdSurveyInput['id'], 'Created SurveyInput must have id specified');
        $this->assertNotNull(SurveyInput::find($createdSurveyInput['id']), 'SurveyInput with given id must be in DB');
        $this->assertModelData($surveyInput, $createdSurveyInput);
    }

    /**
     * @test read
     */
    public function testReadSurveyInput()
    {
        $surveyInput = $this->makeSurveyInput();
        $dbSurveyInput = $this->surveyInputRepo->find($surveyInput->id);
        $dbSurveyInput = $dbSurveyInput->toArray();
        $this->assertModelData($surveyInput->toArray(), $dbSurveyInput);
    }

    /**
     * @test update
     */
    public function testUpdateSurveyInput()
    {
        $surveyInput = $this->makeSurveyInput();
        $fakeSurveyInput = $this->fakeSurveyInputData();
        $updatedSurveyInput = $this->surveyInputRepo->update($fakeSurveyInput, $surveyInput->id);
        $this->assertModelData($fakeSurveyInput, $updatedSurveyInput->toArray());
        $dbSurveyInput = $this->surveyInputRepo->find($surveyInput->id);
        $this->assertModelData($fakeSurveyInput, $dbSurveyInput->toArray());
    }

    /**
     * @test delete
     */
    public function testDeleteSurveyInput()
    {
        $surveyInput = $this->makeSurveyInput();
        $resp = $this->surveyInputRepo->delete($surveyInput->id);
        $this->assertTrue($resp);
        $this->assertNull(SurveyInput::find($surveyInput->id), 'SurveyInput should not exist in DB');
    }
}
