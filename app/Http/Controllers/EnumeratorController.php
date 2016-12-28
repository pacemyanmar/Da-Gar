<?php

namespace App\Http\Controllers;

use App\DataTables\EnumeratorDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateEnumeratorRequest;
use App\Http\Requests\UpdateEnumeratorRequest;
use App\Repositories\EnumeratorRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;

class EnumeratorController extends AppBaseController
{
    /** @var  EnumeratorRepository */
    private $enumeratorRepository;

    public function __construct(EnumeratorRepository $enumeratorRepo)
    {
        $this->enumeratorRepository = $enumeratorRepo;
    }

    /**
     * Display a listing of the Enumerator.
     *
     * @param EnumeratorDataTable $enumeratorDataTable
     * @return Response
     */
    public function index(EnumeratorDataTable $enumeratorDataTable)
    {
        return $enumeratorDataTable->render('enumerators.index');
    }

    /**
     * Show the form for creating a new Enumerator.
     *
     * @return Response
     */
    public function create()
    {
        return view('enumerators.create');
    }

    /**
     * Store a newly created Enumerator in storage.
     *
     * @param CreateEnumeratorRequest $request
     *
     * @return Response
     */
    public function store(CreateEnumeratorRequest $request)
    {
        $input = $request->all();

        $enumerator = $this->enumeratorRepository->create($input);

        Flash::success('Enumerator saved successfully.');

        return redirect(route('enumerators.index'));
    }

    /**
     * Display the specified Enumerator.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        return view('enumerators.show')->with('enumerator', $enumerator);
    }

    /**
     * Show the form for editing the specified Enumerator.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        return view('enumerators.edit')->with('enumerator', $enumerator);
    }

    /**
     * Update the specified Enumerator in storage.
     *
     * @param  int              $id
     * @param UpdateEnumeratorRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateEnumeratorRequest $request)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        $enumerator = $this->enumeratorRepository->update($request->all(), $id);

        Flash::success('Enumerator updated successfully.');

        return redirect(route('enumerators.index'));
    }

    /**
     * Remove the specified Enumerator from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        $this->enumeratorRepository->delete($id);

        Flash::success('Enumerator deleted successfully.');

        return redirect(route('enumerators.index'));
    }
}
