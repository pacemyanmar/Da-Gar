<?php

use Faker\Factory as Faker;
use App\Models\Input;
use App\Repositories\InputRepository;

trait MakeInputTrait
{
    /**
     * Create fake instance of Input and save it in database
     *
     * @param array $inputFields
     * @return Input
     */
    public function makeInput($inputFields = [])
    {
        /** @var InputRepository $inputRepo */
        $inputRepo = App::make(InputRepository::class);
        $theme = $this->fakeInputData($inputFields);
        return $inputRepo->create($theme);
    }

    /**
     * Get fake instance of Input
     *
     * @param array $inputFields
     * @return Input
     */
    public function fakeInput($inputFields = [])
    {
        return new Input($this->fakeInputData($inputFields));
    }

    /**
     * Get fake data of Input
     *
     * @param array $postFields
     * @return array
     */
    public function fakeInputData($inputFields = [])
    {
        $fake = Faker::create();

        return array_merge([
            'type' => $fake->word,
            'name' => $fake->word,
            'label' => $fake->word,
            'default' => $fake->word,
            'sort' => $fake->randomDigitNotNull,
            'question_id' => $fake->randomDigitNotNull
        ], $inputFields);
    }
}
