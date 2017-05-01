<?php

use App\Models\SmsLog;
use App\Models\SurveyInput;
use App\Repositories\SmsLogRepository;
use Faker\Factory as Faker;

trait MakeSmsTrait
{
    /**
     * Create fake instance of Sms and save it in database
     *
     * @param array $smsFields
     * @return Sms
     */
    public function makeSms($smsFields = [])
    {
        /** @var SmsRepository $smsRepo */
        $smsRepo = App::make(SmsLogRepository::class);
        $theme = $this->fakeSmsData($smsFields);
        return $smsRepo->create($theme);
    }

    /**
     * Get fake instance of Sms
     *
     * @param array $smsFields
     * @return Sms
     */
    public function fakeSms($smsFields = [])
    {
        return new SmsLog($this->fakeSmsData($smsFields));
    }

    /**
     * Get fake data of Sms
     *
     * @param array $postFields
     * @return array
     */
    public function fakeSmsData($smsFields = [])
    {
        $fake = Faker::create();
        $survey_inputs = SurveyInput::pluck('inputid')->unique();
        $content = $fake->regexify('A1[0-9][0-4][0-9]');
        $count = $fake->randomDigitNotNull;
        for ($i = 0; $i < $count; $i++) {
            $content .= str_replace('_', '', strtoupper($fake->randomElement($survey_inputs->toArray())));
            $content .= $fake->regexify('[1-9]{0,1}');
        }
        return array_merge([
            'secret' => 'TO9gGcHl62ACCzlFd4wGM0FXAIBTk01O7PKnnhLwrV8vOMnloqY9acG6tPHR',
            'event' => 'incoming_message',
            'service_id' => $fake->uuid,
            'from_number' => $fake->phoneNumber,
            'from_number_e164' => $fake->e164PhoneNumber,
            'to_number' => $fake->phoneNumber,
            'content' => $content,
        ], $smsFields);
    }
}
