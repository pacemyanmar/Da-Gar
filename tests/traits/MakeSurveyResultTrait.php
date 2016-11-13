<?php

use Faker\Factory as Faker;
use App\Models\SurveyResult;
use App\Repositories\SurveyResultRepository;

trait MakeSurveyResultTrait
{
    /**
     * Create fake instance of SurveyResult and save it in database
     *
     * @param array $surveyResultFields
     * @return SurveyResult
     */
    public function makeSurveyResult($surveyResultFields = [])
    {
        /** @var SurveyResultRepository $surveyResultRepo */
        $surveyResultRepo = App::make(SurveyResultRepository::class);
        $theme = $this->fakeSurveyResultData($surveyResultFields);
        return $surveyResultRepo->create($theme);
    }

    /**
     * Get fake instance of SurveyResult
     *
     * @param array $surveyResultFields
     * @return SurveyResult
     */
    public function fakeSurveyResult($surveyResultFields = [])
    {
        return new SurveyResult($this->fakeSurveyResultData($surveyResultFields));
    }

    /**
     * Get fake data of SurveyResult
     *
     * @param array $postFields
     * @return array
     */
    public function fakeSurveyResultData($surveyResultFields = [])
    {
        $fake = Faker::create();

        return array_merge([
            'value' => $fake->text,
            'qnum' => $fake->word,
            'sort' => $fake->randomDigitNotNull,
            'samplable_id' => $fake->randomDigitNotNull,
            'samplable_type' => $fake->word,
            'survey_input_id' => $fake->randomDigitNotNull,
            'project_id' => $fake->randomDigitNotNull
        ], $surveyResultFields);
    }
}
