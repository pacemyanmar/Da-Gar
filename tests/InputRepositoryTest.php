<?php

use App\Models\Input;
use App\Repositories\InputRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InputRepositoryTest extends TestCase
{
    use MakeInputTrait, ApiTestTrait, DatabaseTransactions;

    /**
     * @var InputRepository
     */
    protected $inputRepo;

    public function setUp()
    {
        parent::setUp();
        $this->inputRepo = App::make(InputRepository::class);
    }

    /**
     * @test create
     */
    public function testCreateInput()
    {
        $input = $this->fakeInputData();
        $createdInput = $this->inputRepo->create($input);
        $createdInput = $createdInput->toArray();
        $this->assertArrayHasKey('id', $createdInput);
        $this->assertNotNull($createdInput['id'], 'Created Input must have id specified');
        $this->assertNotNull(Input::find($createdInput['id']), 'Input with given id must be in DB');
        $this->assertModelData($input, $createdInput);
    }

    /**
     * @test read
     */
    public function testReadInput()
    {
        $input = $this->makeInput();
        $dbInput = $this->inputRepo->find($input->id);
        $dbInput = $dbInput->toArray();
        $this->assertModelData($input->toArray(), $dbInput);
    }

    /**
     * @test update
     */
    public function testUpdateInput()
    {
        $input = $this->makeInput();
        $fakeInput = $this->fakeInputData();
        $updatedInput = $this->inputRepo->update($fakeInput, $input->id);
        $this->assertModelData($fakeInput, $updatedInput->toArray());
        $dbInput = $this->inputRepo->find($input->id);
        $this->assertModelData($fakeInput, $dbInput->toArray());
    }

    /**
     * @test delete
     */
    public function testDeleteInput()
    {
        $input = $this->makeInput();
        $resp = $this->inputRepo->delete($input->id);
        $this->assertTrue($resp);
        $this->assertNull(Input::find($input->id), 'Input should not exist in DB');
    }
}
