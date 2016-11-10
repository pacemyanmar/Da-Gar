<?php

use Faker\Factory as Faker;
use App\Models\Voter;
use App\Repositories\VoterRepository;

trait MakeVoterTrait
{
    /**
     * Create fake instance of Voter and save it in database
     *
     * @param array $voterFields
     * @return Voter
     */
    public function makeVoter($voterFields = [])
    {
        /** @var VoterRepository $voterRepo */
        $voterRepo = App::make(VoterRepository::class);
        $theme = $this->fakeVoterData($voterFields);
        return $voterRepo->create($theme);
    }

    /**
     * Get fake instance of Voter
     *
     * @param array $voterFields
     * @return Voter
     */
    public function fakeVoter($voterFields = [])
    {
        return new Voter($this->fakeVoterData($voterFields));
    }

    /**
     * Get fake data of Voter
     *
     * @param array $postFields
     * @return array
     */
    public function fakeVoterData($voterFields = [])
    {
        $fake = Faker::create();

        return array_merge([
            'name' => $fake->word,
            'gender' => $fake->word,
            'nrc_id' => $fake->word,
            'father' => $fake->word,
            'mother' => $fake->word,
            'address' => $fake->text,
            'dob' => $fake->word
        ], $voterFields);
    }
}
