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
use Telerivet\Exceptions\TelerivetAPIException;
use Telerivet\TelerivetAPI;

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
        $app_secret = Settings::get('app_secret');
        $header = ['Content-Type' => 'application/json'];


        $from_number = $request->input('from_number');
        if(empty($from_number)) {
            return $this->sendError('from_number required.');
        }


        $reply = [
            //'content', // SMS message to send
            'to_number' => $from_number, // optional to number, default will use same incoming phone
            // 'message_type', // optinal sms or ussd. default is sms.
            // 'status_url', // optional send status notification url hook
            // 'status_secret', // optional notification url secret
            // 'route_id', // phone route to send message, default will use same
        ];

        if ($secret != $app_secret) {
            $reply['content'] = 'Forbidden';
            $this->sendToTelerivet($reply); // need to make asycronous
            return $this->sendError('Forbidden');
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

        if(empty($event)) {
            return $this->sendError('Event type required.');
        }
        $content = $request->input('content'); // P1000S1AA1AB2AC3

        if(empty($content)) {
            return $this->sendError('Content is empty.');
        }
        //$message = preg_replace('/[^0-9a-zA-Z]/', '', $message);
        $to_number = $request->input('to_number');
        if(empty($to_number)) {
            return $this->sendError('to_number required.');
        }

        $message_type = $request->input('message_type');
//        if(empty($message_type)) {
//            return $this->sendError('message type required.');
//        }

        $service_id = $request->input('id');

        $response = [];
        if ($event == 'incoming_message' || $event == 'default') {

            $response = $this->parseMessage($content, $to_number);
            $status = 'new';
        }

        $smsLog = new SmsLog;
        $smsLog->event = $event;
        $smsLog->message_type = $message_type;
        $smsLog->service_id = $service_id;
        $smsLog->from_number = $from_number;
        $smsLog->from_number_e164 = $request->input('from_number_e164');
        $smsLog->project_id = $request->input('project_id');
        $smsLog->to_number = $to_number;
        $smsLog->content = $content; // incoming message
        $smsLog->sms_status = (isset($status)) ? $status : null;

        $smsLog->status_message = $response['message']; // reply message
        $smsLog->status = $response['status'];

        $smsLog->form_code = (array_key_exists('form_code', $response)) ? $response['form_code'] : 'Not Valid';

        $smsLog->section = (array_key_exists('section', $response)) ? $response['section'] : null; // not actual id from database, just ordering number from form
        $smsLog->result_id = (array_key_exists('result_id', $response)) ? $response['result_id'] : null;
        $smsLog->sample_id = (array_key_exists('sample_id', $response)) ? $response['sample_id'] : null;


        $smsLog->remark = '';
        $smsLog->save();
        $reply['content'] = $smsLog->status_message;

        $this->sendToTelerivet($reply); // need to make asycronous

        return $this->sendResponse($reply, 'Message processed successfully');
    }

    private function sendToTelerivet($reply)
    {
        // from https://telerivet.com/api/keys
        if (Settings::has('api_key')) {
            $API_KEY = Settings::get('api_key');
        } else {
            return $this->sendError('API_KEY not found in your settings!');
        }
        if (Settings::has('project_id')) {
            $PROJECT_ID = Settings::get('project_id');
        } else {
            return $this->sendError('SMS PROJECT_ID not found in your settings!');
        }

        $telerivet = new TelerivetAPI($API_KEY);
        $project = $telerivet->initProjectById($PROJECT_ID);
        try {
            // Send a SMS message
            $project->sendMessage(array(
                'to_number' => $reply['to_number'],
                'content' => json_encode($reply['content'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ));
        } catch (TelerivetAPIException $e) {
            return $this->sendError($e->getMessage());
        }
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
            }
            if (empty($project)) {
                $reply['message'] = 'Please check SMS format. Project code not found!';
                $reply['status'] = 'error';
                return $reply;
            }


            if ($project->status != 'published') {
                $reply['message'] = 'Not ready! Please call to data center immediately.';
                $reply['status'] = 'error';
                return $reply;
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
                $reply['sample_id'] = $sample->id;
                $sample->setRelatedTable($dbname);

                $result = $sample->resultWithTable($dbname)->first();

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

                $optional = 0; // initial count for optional inputs
                $questions = $section->questions;
                $question_completed = 0;
                foreach ($questions as $question) {
                    $valid_response = []; //  valid response in this question
                    $inputs = $question->surveyInputs;

                    foreach ($inputs as $input) {
                        $inputid = $input->inputid;
                        $inputkey = str_replace('_', '', $inputid);
                        // check input is optional
                        if ($input->optional) {
                            $optional++;
                        }

                        // look for numeric answers
                        if ($input->type == 'checkbox') {
                            // if $message is AA11 and checkbox has only AA1, response will be invalid
                            // if $message is AA11 and checkbox has both AA1 and AA11, this will assume as sending for AA11
                            // if $message is AA12 and checkbox has both AA1 and AA11, response will be invalid
                            preg_match('/(' . $inputkey . '\d*)/', $message, $response_match);
                        } else {
                            preg_match('/' . $inputkey . '(\d+)/', $message, $response_match);
                        }
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

                            $is_value_valid = ($input->value == $response || empty($input->value) || ($input->type == 'checkbox' && $response == $inputkey));

                            if ($is_value_valid) {
                                $section_inputs[$inputid] = $response;
                                if ($input->type == 'checkbox') {
                                    $result->{$inputid} = $input->value;
                                } else {
                                    $result->{$inputid} = $response;
                                }

                                // required input complete and count incremental
                                if (!$input->optional) {
                                    $valid_response[] = $inputid;
                                }
                            } else {
                                $section_error_inputs[] = strtoupper($inputkey);
                            }
                            // unset $response  to avoid loop overwrite empty elements with previous value
                            unset($response);
                        } else {
                            if (!$input->optional) {
                                $section_error_inputs[] = strtoupper($inputkey);
                            }
                        }
                    }

                    //dd($valid_response);
                    $required_response_with_value = $question->surveyInputs->filter(function ($input, $key) {
                        return (!$input->optional && $input->value);
                    })->pluck('inputid')->toArray();
                    //dd($required_response_with_value);
                    $required_response_empty_value = $question->surveyInputs->filter(function ($input, $key) {
                        return (!$input->optional && empty($input->value));
                    })->pluck('inputid')->toArray();

                    $intersect_with_value = array_intersect($required_response_with_value, $valid_response);

                    if (count($intersect_with_value) > 0 || (!empty($required_response_empty_value) && $required_response_empty_value == $valid_response)) {

                        $question_completed++;
                    }
                    unset($required_response_with_value);
                    unset($required_response_empty_value);
                    unset($valid_response);
                    unset($intersect_with_value);
                }
                $required_questions = $section->questions->filter(function ($question, $key) {
                    return !$question->optional;
                });

                $error_inputs = $section_error_inputs;

                unset($optional); // unset optional to avoid unexpect outcomes when checking section status

                if (!empty($section_inputs)) {

                    if ($required_questions->count() === $question_completed) {
                        $result->{'section' . $skey . 'status'} = 1;
                    } else {
                        $result->{'section' . $skey . 'status'} = 2;
                    }
                    $reply['section'] = $skey;

                    $result_inputs = $section_inputs;
                    break; // this break and stop the loop - this is required not to process cross section data submit in one SMS
                } else {
                    $result->{'section' . $skey . 'status'} = 0;
                }
                $skey++;
            } // after section loop

            $result->sample()->associate($sample);
            $result->sample = $sample->data->sample;
            $result->user_id = 1; // need to change this
            $result->setTable($dbname); // need to set table name again for some reason
            $result->save();
            if (!empty($error_inputs)) {
                $reply['message'] = implode(', ', $error_inputs) . ' have problem. Please check SMS format.';
                $reply['status'] = 'error';
            } else {
                $reply['message'] = 'Success!';
                $reply['status'] = 'success';
            }
            $reply['result_id'] = $result->id;


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
