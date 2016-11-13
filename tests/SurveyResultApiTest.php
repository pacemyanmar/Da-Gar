<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SurveyResultApiTest extends TestCase
{
    use MakeSurveyResultTrait, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function testCreateSurveyResult()
    {
        $surveyResult = $this->fakeSurveyResultData();
        $this->json('POST', '/api/v1/surveyResults', $surveyResult);

        $this->assertApiResponse($surveyResult);
    }

    /**
     * @test
     */
    public function testReadSurveyResult()
    {
        $surveyResult = $this->makeSurveyResult();
        $this->json('GET', '/api/v1/surveyResults/'.$surveyResult->id);

        $this->assertApiResponse($surveyResult->toArray());
    }

    /**
     * @test
     */
    public function testUpdateSurveyResult()
    {
        $surveyResult = $this->makeSurveyResult();
        $editedSurveyResult = $this->fakeSurveyResultData();

        $this->json('PUT', '/api/v1/surveyResults/'.$surveyResult->id, $editedSurveyResult);

        $this->assertApiResponse($editedSurveyResult);
    }

    /**
     * @test
     */
    public function testDeleteSurveyResult()
    {
        $surveyResult = $this->makeSurveyResult();
        $this->json('DELETE', '/api/v1/surveyResults/'.$surveyResult->iidd);

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/surveyResults/'.$surveyResult->id);

        $this->assertResponseStatus(404);
    }
}
