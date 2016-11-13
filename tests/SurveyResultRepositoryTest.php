<?php

use App\Models\SurveyResult;
use App\Repositories\SurveyResultRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SurveyResultRepositoryTest extends TestCase
{
    use MakeSurveyResultTrait, ApiTestTrait, DatabaseTransactions;

    /**
     * @var SurveyResultRepository
     */
    protected $surveyResultRepo;

    public function setUp()
    {
        parent::setUp();
        $this->surveyResultRepo = App::make(SurveyResultRepository::class);
    }

    /**
     * @test create
     */
    public function testCreateSurveyResult()
    {
        $surveyResult = $this->fakeSurveyResultData();
        $createdSurveyResult = $this->surveyResultRepo->create($surveyResult);
        $createdSurveyResult = $createdSurveyResult->toArray();
        $this->assertArrayHasKey('id', $createdSurveyResult);
        $this->assertNotNull($createdSurveyResult['id'], 'Created SurveyResult must have id specified');
        $this->assertNotNull(SurveyResult::find($createdSurveyResult['id']), 'SurveyResult with given id must be in DB');
        $this->assertModelData($surveyResult, $createdSurveyResult);
    }

    /**
     * @test read
     */
    public function testReadSurveyResult()
    {
        $surveyResult = $this->makeSurveyResult();
        $dbSurveyResult = $this->surveyResultRepo->find($surveyResult->id);
        $dbSurveyResult = $dbSurveyResult->toArray();
        $this->assertModelData($surveyResult->toArray(), $dbSurveyResult);
    }

    /**
     * @test update
     */
    public function testUpdateSurveyResult()
    {
        $surveyResult = $this->makeSurveyResult();
        $fakeSurveyResult = $this->fakeSurveyResultData();
        $updatedSurveyResult = $this->surveyResultRepo->update($fakeSurveyResult, $surveyResult->id);
        $this->assertModelData($fakeSurveyResult, $updatedSurveyResult->toArray());
        $dbSurveyResult = $this->surveyResultRepo->find($surveyResult->id);
        $this->assertModelData($fakeSurveyResult, $dbSurveyResult->toArray());
    }

    /**
     * @test delete
     */
    public function testDeleteSurveyResult()
    {
        $surveyResult = $this->makeSurveyResult();
        $resp = $this->surveyResultRepo->delete($surveyResult->id);
        $this->assertTrue($resp);
        $this->assertNull(SurveyResult::find($surveyResult->id), 'SurveyResult should not exist in DB');
    }
}
