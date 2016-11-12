<?php

namespace App\Http\Controllers;

use App\DataTables\VoterDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateVoterRequest;
use App\Http\Requests\UpdateVoterRequest;
use App\Repositories\VoterRepository;
use Flash;
use Illuminate\Http\Request;
use Response;

class VoterController extends AppBaseController
{
    /** @var  VoterRepository */
    private $voterRepository;

    public function __construct(VoterRepository $voterRepo)
    {
        $this->voterRepository = $voterRepo;
    }

    /**
     * Display a listing of the Voter.
     *
     * @param VoterDataTable $voterDataTable
     * @return Response
     */
    public function index(VoterDataTable $voterDataTable)
    {
        return $voterDataTable->render('voters.index');
    }

    /**
     * Show the form for creating a new Voter.
     *
     * @return Response
     */
    public function create()
    {
        return view('voters.create');
    }

    /**
     * Store a newly created Voter in storage.
     *
     * @param CreateVoterRequest $request
     *
     * @return Response
     */
    public function store(CreateVoterRequest $request)
    {
        $input = $request->all();

        $voter = $this->voterRepository->create($input);

        Flash::success('Voter saved successfully.');

        return redirect(route('voters.index'));
    }

    /**
     * Display the specified Voter.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            Flash::error('Voter not found');

            return redirect(route('voters.index'));
        }

        return view('voters.show')->with('voter', $voter);
    }

    /**
     * Show the form for editing the specified Voter.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            Flash::error('Voter not found');

            return redirect(route('voters.index'));
        }

        return view('voters.edit')->with('voter', $voter);
    }

    /**
     * Update the specified Voter in storage.
     *
     * @param  int              $id
     * @param UpdateVoterRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateVoterRequest $request)
    {
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            Flash::error('Voter not found');

            return redirect(route('voters.index'));
        }

        $voter = $this->voterRepository->update($request->all(), $id);

        Flash::success('Voter updated successfully.');

        return redirect(route('voters.index'));
    }

    /**
     * Remove the specified Voter from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            Flash::error('Voter not found');

            return redirect(route('voters.index'));
        }

        $this->voterRepository->delete($id);

        Flash::success('Voter deleted successfully.');

        return redirect(route('voters.index'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        $listall = $request->only('listall')['listall'];
        $query = $request->only('query')['query'];
        if(!$listall && empty($query) ) {
            return ['data'=> [],
                    'count'=>0];
        }
        $fields = [
            'name', 'father', 'address'
        ];
        return $this->voterRepository->vueTables($request, $fields);
    }
}
