<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSmsAPIRequest;
use App\Http\Requests\API\UpdateSmsAPIRequest;
use App\Repositories\SmsLogRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Krucas\Settings\Facades\Settings;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class SmsController
 * @package App\Http\Controllers\API
 */

class SmsAPIController extends AppBaseController
{
    /** @var  SmsRepository */
    private $smsRepository;

    public function __construct(SmsLogRepository $smsRepo)
    {
        $this->smsRepository = $smsRepo;
    }

    public function telerivet(Request $request)
    {
        $secret = $request->input('secret');
        $api_key = Settings::get('api_key');
        if ($secret != $api_key) {
            return Response::json('Forbidden', 403);
        }

        $messages = [
            'event', // incoming_message or send_status
            'id', //telerivet's unique ID for message max 34 characters
            'message_type', // sms, mms, ussd or call
            'content', // SMS message or MMS content
            'from_number', // phone number sent from
            'from_number_e164', // E.164 format phone number
            'to_number', // telerivet reciver phone number
            'time_created', // time telerivet recived message
            'time_sent', // time set from mobile provider
            'contact_id', // unique id of contact
            'phone_id', // id of recieved phone
            'service_id', // telerivet service ID
            'project_id', // telerivet project ID

        ];

        $reply = [
            'content', // SMS message to send
            'to_number', // optional to number, default will use same incoming phone
            'message_type', // optinal sms or ussd. default is sms.
            'status_url', // optional send status notification url hook
            'status_secret', // optional notification url secret
            'route_id', // phone route to send message, default will use same
        ];

        // To Do
        // get location code and project id from message
        // get project collection
        // get all inputs and inputid
        // loop and find each value for each inputid
        // generate reply message
        // get all existing results for location
        // check logical error
        // save result
        $event = $request->input('event');
        if ($event == 'incoming_message') {
            $message = $request->input('content'); // P1000S1AA1AB2AC3
            $message = preg_replace('/[^0-9a-zA-Z]/', '', $message);

            $response = $this->parseMessage($message);
        }

        return Response::json($response);
    }

    private function parseMessage($message)
    {
        preg_match('/P(\d+)/', $message, $match);

        if (array_key_exists(1, $match)) {
            return $location_code = $match[1];
        } else {
            return 'Location not found!';
        }
    }

    /**
     * Display a listing of the Sms.
     * GET|HEAD /sms
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->smsRepository->pushCriteria(new RequestCriteria($request));
        $this->smsRepository->pushCriteria(new LimitOffsetCriteria($request));
        $sms = $this->smsRepository->all();

        return $this->sendResponse($sms->toArray(), 'Sms retrieved successfully');
    }

    /**
     * Store a newly created Sms in storage.
     * POST /sms
     *
     * @param CreateSmsAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateSmsAPIRequest $request)
    {
        $input = $request->all();

        $sms = $this->smsRepository->create($input);

        return $this->sendResponse($sms->toArray(), 'Sms saved successfully');
    }

    /**
     * Display the specified Sms.
     * GET|HEAD /sms/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Sms $sms */
        $sms = $this->smsRepository->findWithoutFail($id);

        if (empty($sms)) {
            return $this->sendError('Sms not found');
        }

        return $this->sendResponse($sms->toArray(), 'Sms retrieved successfully');
    }

    /**
     * Update the specified Sms in storage.
     * PUT/PATCH /sms/{id}
     *
     * @param  int $id
     * @param UpdateSmsAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSmsAPIRequest $request)
    {
        $input = $request->all();

        /** @var Sms $sms */
        $sms = $this->smsRepository->findWithoutFail($id);

        if (empty($sms)) {
            return $this->sendError('Sms not found');
        }

        $sms = $this->smsRepository->update($input, $id);

        return $this->sendResponse($sms->toArray(), 'Sms updated successfully');
    }

    /**
     * Remove the specified Sms from storage.
     * DELETE /sms/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Sms $sms */
        $sms = $this->smsRepository->findWithoutFail($id);

        if (empty($sms)) {
            return $this->sendError('Sms not found');
        }

        $sms->delete();

        return $this->sendResponse($id, 'Sms deleted successfully');
    }
}
