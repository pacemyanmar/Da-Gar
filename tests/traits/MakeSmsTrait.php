<?php

use Faker\Factory as Faker;
use App\Models\Sms;
use App\Repositories\SmsRepository;

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
        $smsRepo = App::make(SmsRepository::class);
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
        return new Sms($this->fakeSmsData($smsFields));
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

        return array_merge([
            'id' => $fake->word,
            'service_id' => $fake->word,
            'from_number' => $fake->word,
            'to_number' => $fake->word,
            'name' => $fake->word,
            'content' => $fake->word,
            'error_message' => $fake->text,
            'search_result' => $fake->text,
            'phone' => $fake->text,
            'created_at' => $fake->date('Y-m-d H:i:s'),
            'updated_at' => $fake->date('Y-m-d H:i:s')
        ], $smsFields);
    }
}
