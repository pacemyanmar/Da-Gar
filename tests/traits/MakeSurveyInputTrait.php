<?php

use App\Models\SurveyInput;
use App\Repositories\SurveyInputRepository;
use Faker\Factory as Faker;

trait MakeSurveyInputTrait
{
    /**
     * Create fake instance of SurveyInput and save it in database
     *
     * @param array $surveyInputFields
     * @return SurveyInput
     */
    public function makeSurveyInput($surveyInputFields = [])
    {
        /** @var SurveyInputRepository $surveyInputRepo */
        $surveyInputRepo = App::make(SurveyInputRepository::class);
        $theme = $this->fakeSurveyInputData($surveyInputFields);
        return $surveyInputRepo->create($theme);
    }

    /**
     * Get fake instance of SurveyInput
     *
     * @param array $surveyInputFields
     * @return SurveyInput
     */
    public function fakeSurveyInput($surveyInputFields = [])
    {
        return new SurveyInput($this->fakeSurveyInputData($surveyInputFields));
    }

    /**
     * Get fake data of SurveyInput
     *
     * @param array $postFields
     * @return array
     */
    public function fakeSurveyInputData($surveyInputFields = [])
    {
        $fake = Faker::create();

        return array_merge([
            'type' => $fake->word,
            'name' => $fake->word,
            'label' => $fake->word,
            'value' => $fake->word,
            'sort' => $fake->randomDigitNotNull,
            'question_id' => $fake->randomDigitNotNull,
        ], $surveyInputFields);
    }
}
