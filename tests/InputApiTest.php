<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InputApiTest extends TestCase
{
    use MakeInputTrait, ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function testCreateInput()
    {
        $input = $this->fakeInputData();
        $this->json('POST', '/api/v1/inputs', $input);

        $this->assertApiResponse($input);
    }

    /**
     * @test
     */
    public function testReadInput()
    {
        $input = $this->makeInput();
        $this->json('GET', '/api/v1/inputs/'.$input->id);

        $this->assertApiResponse($input->toArray());
    }

    /**
     * @test
     */
    public function testUpdateInput()
    {
        $input = $this->makeInput();
        $editedInput = $this->fakeInputData();

        $this->json('PUT', '/api/v1/inputs/'.$input->id, $editedInput);

        $this->assertApiResponse($editedInput);
    }

    /**
     * @test
     */
    public function testDeleteInput()
    {
        $input = $this->makeInput();
        $this->json('DELETE', '/api/v1/inputs/'.$input->iidd);

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/inputs/'.$input->id);

        $this->assertResponseStatus(404);
    }
}
