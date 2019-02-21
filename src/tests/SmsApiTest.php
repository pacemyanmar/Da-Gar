<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class SmsApiTest extends TestCase
{
    use MakeSmsTrait, ApiTestTrait, WithoutMiddleware;

    /**
     * @test
     */
    public function testSubmitSms()
    {
        $sms = $this->fakeSmsData();
        // json($method, $uri, array $data = [], array $headers = [])
        $response = $this->json('POST', '/api/v1/telerivet', $sms);

        $this->assertApiResponse($sms);
    }

    /**
     * @test
     */
    // public function testReadSms()
    // {
    //     $sms = $this->makeSms();
    //     $this->json('GET', '/api/v1/sms/' . $sms->id);

    //     $this->assertApiResponse($sms->toArray());
    // }

    /**
     * @test
     */
    // public function testUpdateSms()
    // {
    //     $sms = $this->makeSms();
    //     $editedSms = $this->fakeSmsData();

    //     $this->json('PUT', '/api/v1/sms/' . $sms->id, $editedSms);

    //     $this->assertApiResponse($editedSms);
    // }

    /**
     * @test
     */
    // public function testDeleteSms()
    // {
    //     $sms = $this->makeSms();
    //     $this->json('DELETE', '/api/v1/sms/' . $sms->iidd);

    //     $this->assertApiSuccess();
    //     $this->json('GET', '/api/v1/sms/' . $sms->id);

    //     $this->assertResponseStatus(404);
    // }
}
