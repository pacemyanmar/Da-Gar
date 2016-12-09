<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateVoterAPIRequest;
use App\Http\Requests\API\SmsRequest;
use App\Http\Requests\API\UpdateVoterAPIRequest;
use App\Models\Voter;
use App\Repositories\SmsLogRepository;
use App\Repositories\VoterRepository;
use Illuminate\Http\Request;
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

    private $smsRepository;

    public function __construct(VoterRepository $voterRepo, SmsLogRepository $smsRepo)
    {
        $this->voterRepository = $voterRepo;
        $this->smsRepository = $smsRepo;
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

    public function sms(SmsRequest $request)
    {
        $secret = $request->only('secret');
        if ($secret['secret'] !== 'forever') {
            return $this->sendError('API Key is incorrect');
        }
        $content = $request->only('content');
        if (!empty($content['content'])) {
            $args_array = preg_split("/(name)|(nrc)|([,])+/im", $content['content'], null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);

            $voters = Voter::whereIn('name', $args_array)->orWhere(function ($query) use ($args_array) {
                $query->whereIn('nrc_id', $args_array);
            })->get();

            $input = $request->all();

            $input['name'] = $input['contact']['name'];
            $input['search_result'] = $voters;
            $input['error_message'] = (!empty($input['error_message'])) ? $input['error_message'] : 'No error';
            $sms_log = $this->smsRepository->create($input);
            $API_KEY = 'Vdb7rmfRA3B52lr4BRjTFAmEnrf8UH60'; // from https://telerivet.com/api/keys
            $PROJECT_ID = 'PJf516b1b959d05547';

            $telerivet = new \Telerivet_API($API_KEY);

            $project = $telerivet->initProjectById($PROJECT_ID);

// Send a SMS message
            $project->sendMessage(array(
                'to_number' => $input['from_number'],
                'content' => json_encode($voters, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ));
            return $this->sendResponse($voters, 'Voter Found');
        } else {
            return $this->sendError('Message content not found!');
        }

    }
}
