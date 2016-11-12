<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateVoterAPIRequest;
use App\Http\Requests\API\UpdateVoterAPIRequest;
use App\Models\Voter;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class VoterController
 * @package App\Http\Controllers\API
 */

class VoterAPIController extends AppBaseController
{
    /** @var  VoterRepository */
    private $voterRepository;

    public function __construct(VoterRepository $voterRepo)
    {
        $this->voterRepository = $voterRepo;
    }

    /**
     * Display a listing of the Voter.
     * GET|HEAD /voters
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->voterRepository->pushCriteria(new RequestCriteria($request));
        $this->voterRepository->pushCriteria(new LimitOffsetCriteria($request));
        $voters = $this->voterRepository->all();

        return $this->sendResponse($voters->toArray(), 'Voters retrieved successfully');
    }

    /**
     * Store a newly created Voter in storage.
     * POST /voters
     *
     * @param CreateVoterAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateVoterAPIRequest $request)
    {
        $input = $request->all();

        $voters = $this->voterRepository->create($input);

        return $this->sendResponse($voters->toArray(), 'Voter saved successfully');
    }

    /**
     * Display the specified Voter.
     * GET|HEAD /voters/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Voter $voter */
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            return $this->sendError('Voter not found');
        }

        return $this->sendResponse($voter->toArray(), 'Voter retrieved successfully');
    }

    /**
     * Update the specified Voter in storage.
     * PUT/PATCH /voters/{id}
     *
     * @param  int $id
     * @param UpdateVoterAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateVoterAPIRequest $request)
    {
        $input = $request->all();

        /** @var Voter $voter */
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            return $this->sendError('Voter not found');
        }

        $voter = $this->voterRepository->update($input, $id);

        return $this->sendResponse($voter->toArray(), 'Voter updated successfully');
    }

    /**
     * Remove the specified Voter from storage.
     * DELETE /voters/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Voter $voter */
        $voter = $this->voterRepository->findWithoutFail($id);

        if (empty($voter)) {
            return $this->sendError('Voter not found');
        }

        $voter->delete();

        return $this->sendResponse($id, 'Voter deleted successfully');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        return $this->voterRepository->vueTables($request);
    }
}
