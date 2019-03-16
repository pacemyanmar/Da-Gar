<?php

namespace App\Http\Controllers;

use App\DataTables\BulkSmsDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateBulkSmsRequest;
use App\Http\Requests\UpdateBulkSmsRequest;
use App\Models\BulkSms;
use App\Repositories\BulkSmsRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Statement;
use Response;

class BulkSmsController extends AppBaseController
{
    /** @var  BulkSmsRepository */
    private $bulkSmsRepository;

    public function __construct(BulkSmsRepository $bulkSmsRepo)
    {
        $this->middleware('auth');

        $this->bulkSmsRepository = $bulkSmsRepo;
    }

    /**
     * Display a listing of the BulkSms.
     *
     * @param BulkSmsDataTable $bulkSmsDataTable
     * @return Response
     */
    public function index(BulkSmsDataTable $bulkSmsDataTable)
    {
        return $bulkSmsDataTable->render('bulk_sms.index');
    }

    /**
     * Show the form for creating a new BulkSms.
     *
     * @return Response
     */
    public function create()
    {
        return view('bulk_sms.create');
    }

    /**
     * Store a newly created BulkSms in storage.
     *
     * @param CreateBulkSmsRequest $request
     *
     * @return Response
     */
    public function store(CreateBulkSmsRequest $request)
    {
        $input = $request->all();

        $input['status'] = 'new';

        $bulkSms = $this->bulkSmsRepository->create($input);

        Flash::success('Bulk Sms saved successfully.');

        return redirect(route('bulkSms.index'));
    }

    /**
     * Display the specified BulkSms.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $bulkSms = $this->bulkSmsRepository->findWithoutFail($id);

        if (empty($bulkSms)) {
            Flash::error('Bulk Sms not found');

            return redirect(route('bulkSms.index'));
        }

        return view('bulk_sms.show')->with('bulkSms', $bulkSms);
    }

    /**
     * Show the form for editing the specified BulkSms.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $bulkSms = $this->bulkSmsRepository->findWithoutFail($id);

        if (empty($bulkSms)) {
            Flash::error('Bulk Sms not found');

            return redirect(route('bulkSms.index'));
        }

        return view('bulk_sms.edit')->with('bulkSms', $bulkSms);
    }

    /**
     * Update the specified BulkSms in storage.
     *
     * @param  int              $id
     * @param UpdateBulkSmsRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateBulkSmsRequest $request)
    {
        $bulkSms = $this->bulkSmsRepository->findWithoutFail($id);

        if (empty($bulkSms)) {
            Flash::error('Bulk Sms not found');

            return redirect(route('bulkSms.index'));
        }

        $inputs = $request->all();
        $inputs['status'] = 'new';
        $bulkSms = $this->bulkSmsRepository->update($inputs, $id);

        Flash::success('Bulk Sms updated successfully.');

        return redirect(route('bulkSms.index'));
    }

    /**
     * Remove the specified BulkSms from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $bulkSms = $this->bulkSmsRepository->findWithoutFail($id);

        if (empty($bulkSms)) {
            Flash::error('Bulk Sms not found');

            return redirect(route('bulkSms.index'));
        }

        $this->bulkSmsRepository->delete($id);

        Flash::success('Bulk Sms deleted successfully.');

        return redirect(route('bulkSms.index'));
    }

    public function import(Request $request)
    {
        if ($request->file('smsfile')->isValid()) {
            $smsfile = $request->smsfile->path();
            $reader = Reader::createFromPath($smsfile);
            $reader->setHeaderOffset(0);
            $records = (new Statement())->process($reader);
            $records->getHeader();
            foreach ($records as $record) {
                $record = array_change_key_case($record);

                $sms_phone = BulkSms::firstOrNew(['phone' => $record['phone']]);
                $sms_phone->name = $record['name'];

                // Do not sent same message twice
                if($sms_phone->message != $record['message']) {
                    $sms_phone->message = $record['message'];
                    $sms_phone->status = 'new';
                }

                $sms_phone->save();
            }

            Flash::success('Bulk Sms uploaded successfully.');

            return redirect(route('bulkSms.index'));
        }
    }

    public function send()
    {

    }
}
