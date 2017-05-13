<?php

namespace App\Http\Controllers;

use App\DataTables\ObserverDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateObserverRequest;
use App\Http\Requests\UpdateObserverRequest;
use App\Repositories\ObserverRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;

class ObserverController extends AppBaseController
{
    /** @var  ObserverRepository */
    private $observerRepository;

    public function __construct(ObserverRepository $observerRepo)
    {
        $this->observerRepository = $observerRepo;
    }

    /**
     * Display a listing of the Observer.
     *
     * @param ObserverDataTable $observerDataTable
     * @return Response
     */
    public function index(ObserverDataTable $observerDataTable)
    {
        return $observerDataTable->render('observers.index');
    }

    /**
     * Show the form for creating a new Observer.
     *
     * @return Response
     */
    public function create()
    {
        return view('observers.create');
    }

    /**
     * Store a newly created Observer in storage.
     *
     * @param CreateObserverRequest $request
     *
     * @return Response
     */
    public function store(CreateObserverRequest $request)
    {
        $input = $request->all();

        $observer = $this->observerRepository->create($input);

        Flash::success('Observer saved successfully.');

        return redirect(route('observers.index'));
    }

    /**
     * Display the specified Observer.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $observer = $this->observerRepository->findWithoutFail($id);

        if (empty($observer)) {
            Flash::error('Observer not found');

            return redirect(route('observers.index'));
        }

        return view('observers.show')->with('observer', $observer);
    }

    /**
     * Show the form for editing the specified Observer.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $observer = $this->observerRepository->findWithoutFail($id);

        if (empty($observer)) {
            Flash::error('Observer not found');

            return redirect(route('observers.index'));
        }

        return view('observers.edit')->with('observer', $observer);
    }

    /**
     * Update the specified Observer in storage.
     *
     * @param  int              $id
     * @param UpdateObserverRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateObserverRequest $request)
    {
        $observer = $this->observerRepository->findWithoutFail($id);

        if (empty($observer)) {
            Flash::error('Observer not found');

            return redirect(route('observers.index'));
        }

        $observer = $this->observerRepository->update($request->all(), $id);

        Flash::success('Observer updated successfully.');

        return redirect(route('observers.index'));
    }

    /**
     * Remove the specified Observer from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $observer = $this->observerRepository->findWithoutFail($id);

        if (empty($observer)) {
            Flash::error('Observer not found');

            return redirect(route('observers.index'));
        }

        $this->observerRepository->delete($id);

        Flash::success('Observer deleted successfully.');

        return redirect(route('observers.index'));
    }
}
