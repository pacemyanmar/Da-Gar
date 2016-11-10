<?php

use App\Models\Voter;
use App\Repositories\VoterRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VoterRepositoryTest extends TestCase
{
    use MakeVoterTrait, ApiTestTrait, DatabaseTransactions;

    /**
     * @var VoterRepository
     */
    protected $voterRepo;

    public function setUp()
    {
        parent::setUp();
        $this->voterRepo = App::make(VoterRepository::class);
    }

    /**
     * @test create
     */
    public function testCreateVoter()
    {
        $voter = $this->fakeVoterData();
        $createdVoter = $this->voterRepo->create($voter);
        $createdVoter = $createdVoter->toArray();
        $this->assertArrayHasKey('id', $createdVoter);
        $this->assertNotNull($createdVoter['id'], 'Created Voter must have id specified');
        $this->assertNotNull(Voter::find($createdVoter['id']), 'Voter with given id must be in DB');
        $this->assertModelData($voter, $createdVoter);
    }

    /**
     * @test read
     */
    public function testReadVoter()
    {
        $voter = $this->makeVoter();
        $dbVoter = $this->voterRepo->find($voter->id);
        $dbVoter = $dbVoter->toArray();
        $this->assertModelData($voter->toArray(), $dbVoter);
    }

    /**
     * @test update
     */
    public function testUpdateVoter()
    {
        $voter = $this->makeVoter();
        $fakeVoter = $this->fakeVoterData();
        $updatedVoter = $this->voterRepo->update($fakeVoter, $voter->id);
        $this->assertModelData($fakeVoter, $updatedVoter->toArray());
        $dbVoter = $this->voterRepo->find($voter->id);
        $this->assertModelData($fakeVoter, $dbVoter->toArray());
    }

    /**
     * @test delete
     */
    public function testDeleteVoter()
    {
        $voter = $this->makeVoter();
        $resp = $this->voterRepo->delete($voter->id);
        $this->assertTrue($resp);
        $this->assertNull(Voter::find($voter->id), 'Voter should not exist in DB');
    }
}
