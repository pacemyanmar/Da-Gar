<?php

namespace App\Http\Controllers;

use App\DataTables\SampleDataDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateSampleDataRequest;
use App\Http\Requests\UpdateSampleDataRequest;
use App\Repositories\SampleDataRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;

class SampleDataController extends AppBaseController
{
    /** @var  SampleDataRepository */
    private $sampleDataRepository;

    public function __construct(SampleDataRepository $sampleDataRepo)
    {
        $this->sampleDataRepository = $sampleDataRepo;
    }

    /**
     * Display a listing of the SampleData.
     *
     * @param SampleDataDataTable $sampleDataDataTable
     * @return Response
     */
    public function index(SampleDataDataTable $sampleDataDataTable)
    {
        return $sampleDataDataTable->render('sample_datas.index');
    }

    /**
     * Show the form for creating a new SampleData.
     *
     * @return Response
     */
    public function create()
    {
        return view('sample_datas.create');
    }

    /**
     * Store a newly created SampleData in storage.
     *
     * @param CreateSampleDataRequest $request
     *
     * @return Response
     */
    public function store(CreateSampleDataRequest $request)
    {
        $input = $request->all();

        $sampleData = $this->sampleDataRepository->create($input);

        Flash::success('Sample Data saved successfully.');

        return redirect(route('sampleDatas.index'));
    }

    /**
     * Display the specified SampleData.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        return view('sample_datas.show')->with('sampleData', $sampleData);
    }

    /**
     * Show the form for editing the specified SampleData.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        return view('sample_datas.edit')->with('sampleData', $sampleData);
    }

    /**
     * Update the specified SampleData in storage.
     *
     * @param  int              $id
     * @param UpdateSampleDataRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSampleDataRequest $request)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        $sampleData = $this->sampleDataRepository->update($request->all(), $id);

        Flash::success('Sample Data updated successfully.');

        return redirect(route('sampleDatas.index'));
    }

    /**
     * Remove the specified SampleData from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        $this->sampleDataRepository->delete($id);

        Flash::success('Sample Data deleted successfully.');

        return redirect(route('sampleDatas.index'));
    }
}
