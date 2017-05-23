<?php

namespace App\Http\Controllers;

use App\DataTables\LogicalCheckDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateLogicalCheckRequest;
use App\Http\Requests\UpdateLogicalCheckRequest;
use App\Repositories\LogicalCheckRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;

class LogicalCheckController extends AppBaseController
{
    /** @var  LogicalCheckRepository */
    private $logicalCheckRepository;

    public function __construct(LogicalCheckRepository $logicalCheckRepo)
    {
        $this->logicalCheckRepository = $logicalCheckRepo;
    }

    /**
     * Display a listing of the LogicalCheck.
     *
     * @param LogicalCheckDataTable $logicalCheckDataTable
     * @return Response
     */
    public function index(LogicalCheckDataTable $logicalCheckDataTable)
    {
        return $logicalCheckDataTable->render('logical_checks.index');
    }

    /**
     * Show the form for creating a new LogicalCheck.
     *
     * @return Response
     */
    public function create()
    {
        return view('logical_checks.create');
    }

    /**
     * Store a newly created LogicalCheck in storage.
     *
     * @param CreateLogicalCheckRequest $request
     *
     * @return Response
     */
    public function store(CreateLogicalCheckRequest $request)
    {
        $input = $request->all();

        $logicalCheck = $this->logicalCheckRepository->create($input);

        Flash::success('Logical Check saved successfully.');

        return redirect(route('logicalChecks.index'));
    }

    /**
     * Display the specified LogicalCheck.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $logicalCheck = $this->logicalCheckRepository->findWithoutFail($id);

        if (empty($logicalCheck)) {
            Flash::error('Logical Check not found');

            return redirect(route('logicalChecks.index'));
        }

        return view('logical_checks.show')->with('logicalCheck', $logicalCheck);
    }

    /**
     * Show the form for editing the specified LogicalCheck.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $logicalCheck = $this->logicalCheckRepository->findWithoutFail($id);

        if (empty($logicalCheck)) {
            Flash::error('Logical Check not found');

            return redirect(route('logicalChecks.index'));
        }

        return view('logical_checks.edit')->with('logicalCheck', $logicalCheck);
    }

    /**
     * Update the specified LogicalCheck in storage.
     *
     * @param  int              $id
     * @param UpdateLogicalCheckRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateLogicalCheckRequest $request)
    {
        $logicalCheck = $this->logicalCheckRepository->findWithoutFail($id);

        if (empty($logicalCheck)) {
            Flash::error('Logical Check not found');

            return redirect(route('logicalChecks.index'));
        }

        $logicalCheck = $this->logicalCheckRepository->update($request->all(), $id);

        Flash::success('Logical Check updated successfully.');

        return redirect(route('logicalChecks.index'));
    }

    /**
     * Remove the specified LogicalCheck from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $logicalCheck = $this->logicalCheckRepository->findWithoutFail($id);

        if (empty($logicalCheck)) {
            Flash::error('Logical Check not found');

            return redirect(route('logicalChecks.index'));
        }

        $this->logicalCheckRepository->delete($id);

        Flash::success('Logical Check deleted successfully.');

        return redirect(route('logicalChecks.index'));
    }
}
