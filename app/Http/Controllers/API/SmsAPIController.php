<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSmsAPIRequest;
use App\Http\Requests\API\UpdateSmsAPIRequest;
use App\Models\Project;
use App\Models\ProjectPhone;
use App\Models\SmsLog;
use App\Models\SurveyResult;
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
        $response = [];
        if ($event == 'incoming_message') {
            $message = $request->input('content'); // P1000S1AA1AB2AC3
            //$message = preg_replace('/[^0-9a-zA-Z]/', '', $message);
            $to_number = $request->input('to_number');
            $response = $this->parseMessage($message, $to_number);
        }

        $smsLog = new SmsLog;
        $smsLog->event = $event;
        $smsLog->message_type = $request->input('message_type');
        $smsLog->service_id = $request->input('id');
        $smsLog->from_number = $request->input('from_number');
        $smsLog->from_number_e164 = $request->input('from_number_e164');
        $smsLog->to_number = $request->input('to_number');
        $smsLog->content = $request->input('content'); // incoming message

        $smsLog->form_code = $response['form_code'];
        $smsLog->status_message = $response['message']; // reply message
        $smsLog->status = $response['status'];

        $smsLog->section = (array_key_exists('section', $response)) ? $response['section'] : null; // not actual id from database, just ordering number from form
        $smsLog->result_id = (array_key_exists('result_id', $response)) ? $response['result_id'] : null;
        $smsLog->project_id = (array_key_exists('project_id', $response)) ? $response['project_id'] : null;

        $smsLog->remark = '';
        $smsLog->save();
        return Response::json($smsLog->status_message);
    }

    private function parseMessage($message, $to_number = '')
    {
        // look for Form Code and PCODE/Location code
        preg_match('/^([A-Z]+)(\d+)/', $message, $pcode);

        if (count($pcode) === 3) {

            $projects = Project::all();
            if ($projects->count() === 1) {
                // if project is only one project use this project
                $project = Project::first();
            } elseif (!empty($to_number)) {
                // if to_number exists, look for project with phone number first
                $projectPhone = ProjectPhone::where('phone', $to_number)->first();
                if ($projectPhone) {
                    $project = $projectPhone->project;
                }
            } else {
                // look for project by unique_code, first letter of SMS message
                $project = Project::where('unique_code', $pcode[1])->first();
                if (empty($project)) {
                    $reply['message'] = 'Please check SMS format. Project code not found!';
                    $reply['status'] = 'error';
                    return $reply;
                }
            }
            $reply['form_code'] = $pcode[1] . $pcode[2];
            $location_code = $pcode[2];

            $sample_data = $project->samplesData->where('idcode', $location_code)->first();
            if (empty($sample_data)) {
                $reply['message'] = 'Please check location code in SMS. No such code found in database!';
                $reply['status'] = 'error';
                return $reply;
            }

            $dbname = $project->dbname;
            if ($project->type != 'sample2db') {
                // look for Form ID
                preg_match('/FNNN(\d+)/', $message, $form_id); // this is temporary form number code
                if ($form_id) {
                    $form_number = $form_id[1];
                } else {
                    $form_number = 1;
                }
                $sample = $sample_data->samples->where('sample_data_type', $project->dblink)->where('form_id', $form_number)->first();
                $sample->setRelatedTable($dbname);

                $result = $sample->resultWithTable()->first();

            }

            if (empty($result)) {
                $result = new SurveyResult;
                $result->setTable($dbname);
            }

            $message = strtolower($message);
            // get all sections in a project
            $sections = $project->sectionsDb->sortBy('sort');
            $error_inputs = [];
            $result_inputs = [];
            $skey = 1;

            foreach ($sections as $section) {
                $valid_response = 0; // initial valid response in this section
                $optional = 0; // initial count for optional inputs
                // get all inputs in a section
                $inputs = $section->inputs->groupBy('inputid');
                $section_inputs = [];
                $section_error_inputs = [];
                foreach ($inputs as $inputid => $input) {

                    $inputkey = str_replace('_', '', $inputid);
                    // check input is optional
                    $is_input_optional = $input->contains(function ($input, $key) {
                        return $input->optional;
                    });
                    if ($is_input_optional) {
                        $optional++;
                    }
                    // look for numeric answers
                    preg_match('/' . $inputkey . '(\d+)/', $message, $response_match);
                    if (array_key_exists(1, $response_match)) {
                        $response = $response_match[1];
                    } else {
                        // look for open text answers
                        $test_response = preg_match('/' . $inputkey . '@(.*)/', $message, $response_match);

                        if ($test_response) {
                            $response = $response_match[1];
                        }
                    }

                    // if there is response
                    if (isset($response)) {
                        // input value is equal to $response or
                        // input value is zero or empty string

                        $is_value_valid = $input->contains(function ($input, $key) use ($inputid, $response) {
                            return ($input->inputid == $inputid && ($input->value == $response || empty($input->value)));
                        });

                        if ($is_value_valid) {
                            $section_inputs[$inputid] = $response;

                            $result->{$inputid} = $response;

                            // check input complete and count incremental
                            if (!$is_input_optional) {
                                $valid_response++;
                            }
                        } else {
                            $section_error_inputs[] = strtoupper($inputkey);
                        }
                        // unset $response  to avoid loop overwrite empty elements with previous value
                        unset($response);
                    } else {
                        if (!$is_input_optional) {
                            $section_error_inputs[] = strtoupper($inputkey);
                        }
                    }
                } // after inputs loop
                // To check section status here
                // get all inputs with optional status
                $unique_inputs = $section->inputs->mapWithKeys(function ($item) {
                    return [$item->inputid => $item->optional];
                });

                $required_inputs = $unique_inputs->filter(function ($optional, $key) {
                    // optional not false
                    return !$optional;
                });

                if (!empty($section_inputs)) {

                    if ($required_inputs->count() === $valid_response) {
                        $result->{'section' . $skey . 'status'} = 1;
                    } else {
                        $result->{'section' . $skey . 'status'} = 2;
                    }
                    $reply['section'] = $skey;

                    $result_inputs = $section_inputs;
                } else {

                    $result->{'section' . $skey . 'status'} = 0;

                }

                $error_inputs = $section_error_inputs;

                unset($optional); // unset optional to avoid unexpect outcomes when checking section status
                $skey++;
            } // after section loop

            $result->sample()->associate($sample);
            $result->sample = $sample->data->sample;
            $result->user_id = 1; // need to change this
            $result->save();
            if (!empty($error_inputs)) {
                $reply['message'] = implode(', ', $error_inputs) . ' have problem. Please check SMS format.';
                $reply['status'] = 'error';
            } else {
                $reply['message'] = 'Success!';
                $reply['status'] = 'success';
            }
            $reply['result_id'] = $result->id;
            $reply['project_id'] = $project->id;

            return $reply;
        } else {
            $reply['message'] = 'Please check SMS format. Location code not found!';
            $reply['status'] = 'error';
            $reply['form_code'] = 'unknown';
            return $reply;
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
