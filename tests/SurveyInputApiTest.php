<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SurveyInputApiTest extends TestCase
{
    use MakeSurveyInputTrait, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function testCreateSurveyInput()
    {
        $surveyInput = $this->fakeSurveyInputData();
        $this->json('POST', '/api/v1/surveyInputs', $surveyInput);

        $this->assertApiResponse($surveyInput);
    }

    /**
     * @test
     */
    public function testReadSurveyInput()
    {
        $surveyInput = $this->makeSurveyInput();
        $this->json('GET', '/api/v1/surveyInputs/'.$surveyInput->id);

        $this->assertApiResponse($surveyInput->toArray());
    }

    /**
     * @test
     */
    public function testUpdateSurveyInput()
    {
        $surveyInput = $this->makeSurveyInput();
        $editedSurveyInput = $this->fakeSurveyInputData();

        $this->json('PUT', '/api/v1/surveyInputs/'.$surveyInput->id, $editedSurveyInput);

        $this->assertApiResponse($editedSurveyInput);
    }

    /**
     * @test
     */
    public function testDeleteSurveyInput()
    {
        $surveyInput = $this->makeSurveyInput();
        $this->json('DELETE', '/api/v1/surveyInputs/'.$surveyInput->iidd);

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/surveyInputs/'.$surveyInput->id);

        $this->assertResponseStatus(404);
    }
}
