<?php

namespace App\Http\Controllers;

use App\DataTables\SmsLogDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateSmsLogRequest;
use App\Http\Requests\UpdateSmsLogRequest;
use App\Repositories\SmsLogRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;

class SmsLogController extends AppBaseController
{
    /** @var  SmsLogRepository */
    private $smsLogRepository;

    public function __construct(SmsLogRepository $smsLogRepo)
    {
        $this->smsLogRepository = $smsLogRepo;
    }

    /**
     * Display a listing of the SmsLog.
     *
     * @param SmsLogDataTable $smsLogDataTable
     * @return Response
     */
    public function index(SmsLogDataTable $smsLogDataTable)
    {
        return $smsLogDataTable->render('sms_logs.index');
    }

    /**
     * Show the form for creating a new SmsLog.
     *
     * @return Response
     */
    public function create()
    {
        return view('sms_logs.create');
    }

    /**
     * Store a newly created SmsLog in storage.
     *
     * @param CreateSmsLogRequest $request
     *
     * @return Response
     */
    public function store(CreateSmsLogRequest $request)
    {
        $input = $request->all();

        $smsLog = $this->smsLogRepository->create($input);

        Flash::success('Sms Log saved successfully.');

        return redirect(route('smsLogs.index'));
    }

    /**
     * Display the specified SmsLog.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $smsLog = $this->smsLogRepository->findWithoutFail($id);

        if (empty($smsLog)) {
            Flash::error('Sms Log not found');

            return redirect(route('smsLogs.index'));
        }

        return view('sms_logs.show')->with('smsLog', $smsLog);
    }

    /**
     * Show the form for editing the specified SmsLog.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $smsLog = $this->smsLogRepository->findWithoutFail($id);

        if (empty($smsLog)) {
            Flash::error('Sms Log not found');

            return redirect(route('smsLogs.index'));
        }

        return view('sms_logs.edit')->with('smsLog', $smsLog);
    }

    /**
     * Update the specified SmsLog in storage.
     *
     * @param  int              $id
     * @param UpdateSmsLogRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSmsLogRequest $request)
    {
        $smsLog = $this->smsLogRepository->findWithoutFail($id);

        if (empty($smsLog)) {
            Flash::error('Sms Log not found');

            return redirect(route('smsLogs.index'));
        }

        $smsLog = $this->smsLogRepository->update($request->all(), $id);

        Flash::success('Sms Log updated successfully.');

        return redirect(route('smsLogs.index'));
    }

    /**
     * Remove the specified SmsLog from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $smsLog = $this->smsLogRepository->findWithoutFail($id);

        if (empty($smsLog)) {
            Flash::error('Sms Log not found');

            return redirect(route('smsLogs.index'));
        }

        $this->smsLogRepository->delete($id);

        Flash::success('Sms Log deleted successfully.');

        return redirect(route('smsLogs.index'));
    }
}
