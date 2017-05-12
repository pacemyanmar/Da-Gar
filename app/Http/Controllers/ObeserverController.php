<?php

namespace App\Http\Controllers;

use App\DataTables\ObeserverDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateObeserverRequest;
use App\Http\Requests\UpdateObeserverRequest;
use App\Repositories\ObeserverRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;

class ObeserverController extends AppBaseController
{
    /** @var  ObeserverRepository */
    private $obeserverRepository;

    public function __construct(ObeserverRepository $obeserverRepo)
    {
        $this->obeserverRepository = $obeserverRepo;
    }

    /**
     * Display a listing of the Obeserver.
     *
     * @param ObeserverDataTable $obeserverDataTable
     * @return Response
     */
    public function index(ObeserverDataTable $obeserverDataTable)
    {
        return $obeserverDataTable->render('obeservers.index');
    }

    /**
     * Show the form for creating a new Obeserver.
     *
     * @return Response
     */
    public function create()
    {
        return view('obeservers.create');
    }

    /**
     * Store a newly created Obeserver in storage.
     *
     * @param CreateObeserverRequest $request
     *
     * @return Response
     */
    public function store(CreateObeserverRequest $request)
    {
        $input = $request->all();

        $obeserver = $this->obeserverRepository->create($input);

        Flash::success('Obeserver saved successfully.');

        return redirect(route('obeservers.index'));
    }

    /**
     * Display the specified Obeserver.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $obeserver = $this->obeserverRepository->findWithoutFail($id);

        if (empty($obeserver)) {
            Flash::error('Obeserver not found');

            return redirect(route('obeservers.index'));
        }

        return view('obeservers.show')->with('obeserver', $obeserver);
    }

    /**
     * Show the form for editing the specified Obeserver.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $obeserver = $this->obeserverRepository->findWithoutFail($id);

        if (empty($obeserver)) {
            Flash::error('Obeserver not found');

            return redirect(route('obeservers.index'));
        }

        return view('obeservers.edit')->with('obeserver', $obeserver);
    }

    /**
     * Update the specified Obeserver in storage.
     *
     * @param  int              $id
     * @param UpdateObeserverRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateObeserverRequest $request)
    {
        $obeserver = $this->obeserverRepository->findWithoutFail($id);

        if (empty($obeserver)) {
            Flash::error('Obeserver not found');

            return redirect(route('obeservers.index'));
        }

        $obeserver = $this->obeserverRepository->update($request->all(), $id);

        Flash::success('Obeserver updated successfully.');

        return redirect(route('obeservers.index'));
    }

    /**
     * Remove the specified Obeserver from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $obeserver = $this->obeserverRepository->findWithoutFail($id);

        if (empty($obeserver)) {
            Flash::error('Obeserver not found');

            return redirect(route('obeservers.index'));
        }

        $this->obeserverRepository->delete($id);

        Flash::success('Obeserver deleted successfully.');

        return redirect(route('obeservers.index'));
    }
}
