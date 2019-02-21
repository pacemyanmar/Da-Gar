<?php

use App\Models\Sms;
use App\Repositories\SmsRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SmsRepositoryTest extends TestCase
{
    use MakeSmsTrait, ApiTestTrait, DatabaseTransactions;

    /**
     * @var SmsRepository
     */
    protected $smsRepo;

    public function setUp()
    {
        parent::setUp();
        $this->smsRepo = App::make(SmsRepository::class);
    }

    /**
     * @test create
     */
    public function testCreateSms()
    {
        $sms = $this->fakeSmsData();
        $createdSms = $this->smsRepo->create($sms);
        $createdSms = $createdSms->toArray();
        $this->assertArrayHasKey('id', $createdSms);
        $this->assertNotNull($createdSms['id'], 'Created Sms must have id specified');
        $this->assertNotNull(Sms::find($createdSms['id']), 'Sms with given id must be in DB');
        $this->assertModelData($sms, $createdSms);
    }

    /**
     * @test read
     */
    public function testReadSms()
    {
        $sms = $this->makeSms();
        $dbSms = $this->smsRepo->find($sms->id);
        $dbSms = $dbSms->toArray();
        $this->assertModelData($sms->toArray(), $dbSms);
    }

    /**
     * @test update
     */
    public function testUpdateSms()
    {
        $sms = $this->makeSms();
        $fakeSms = $this->fakeSmsData();
        $updatedSms = $this->smsRepo->update($fakeSms, $sms->id);
        $this->assertModelData($fakeSms, $updatedSms->toArray());
        $dbSms = $this->smsRepo->find($sms->id);
        $this->assertModelData($fakeSms, $dbSms->toArray());
    }

    /**
     * @test delete
     */
    public function testDeleteSms()
    {
        $sms = $this->makeSms();
        $resp = $this->smsRepo->delete($sms->id);
        $this->assertTrue($resp);
        $this->assertNull(Sms::find($sms->id), 'Sms should not exist in DB');
    }
}
