<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VoterApiTest extends TestCase
{
    use MakeVoterTrait, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function testCreateVoter()
    {
        $voter = $this->fakeVoterData();
        $this->json('POST', '/api/v1/voters', $voter);

        $this->assertApiResponse($voter);
    }

    /**
     * @test
     */
    public function testReadVoter()
    {
        $voter = $this->makeVoter();
        $this->json('GET', '/api/v1/voters/'.$voter->id);

        $this->assertApiResponse($voter->toArray());
    }

    /**
     * @test
     */
    public function testUpdateVoter()
    {
        $voter = $this->makeVoter();
        $editedVoter = $this->fakeVoterData();

        $this->json('PUT', '/api/v1/voters/'.$voter->id, $editedVoter);

        $this->assertApiResponse($editedVoter);
    }

    /**
     * @test
     */
    public function testDeleteVoter()
    {
        $voter = $this->makeVoter();
        $this->json('DELETE', '/api/v1/voters/'.$voter->iidd);

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/voters/'.$voter->id);

        $this->assertResponseStatus(404);
    }
}
