<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSmsAPIRequest;
use App\Http\Requests\API\UpdateSmsAPIRequest;
use App\Models\Observer;
use App\Models\Project;
use App\Models\ProjectPhone;
use App\Models\SmsLog;
use App\Models\SurveyResult;
use App\Repositories\SmsLogRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Krucas\Settings\Facades\Settings;
use Prettus\Repository\Criteria\RequestCriteria;
use Ramsey\Uuid\Uuid;
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
        if( env('APP_DEBUG', false)) {
            Log::info($request->all());
        }

        $secret = $request->input('secret');
        $app_secret = Settings::get('app_secret');
        $header = ['Content-Type' => 'application/json'];

        $from_number = $request->input('from_number');

        if (empty($from_number)) {
            return $this->sendError('from_number required.');
        }

        $reply = [
            //'content', // SMS message to send
            'to_number' => $from_number, // optional to number, default will use same incoming phone
            // 'message_type', // optinal sms or ussd. default is sms.
            // 'status_url', // optional send status notification url hook
            // 'status_secret', // optional notification url secret
            'route_id' => $request->input('phone_id'), // phone route to send message, default will use same
            'service_id' => $request->input('service_id'),
            'project_id' => $request->input('project_id')
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

        if (empty($event)) {
            return $this->sendError('Event type required.');
        }
        $content = $request->input('content'); // P1000S1AA1AB2AC3

        if (empty($content)) {
            return $this->sendError('Content is empty.');
        }
        //$message = preg_replace('/[^0-9a-zA-Z]/', '', $message);
        $to_number = $request->input('to_number');
        if (empty($to_number)) {
            return $this->sendError('to_number required.');
        }

        $message_type = $request->input('message_type');
//        if(empty($message_type)) {
        //            return $this->sendError('message type required.');
        //        }

        $service_id = $request->input('id');

        $response = [];

        switch ($event) {
            case 'incoming_message':
            case 'default':
                if ($secret != $app_secret) {
                    $reply['content'] = 'Forbidden';
                    $this->sendToTelerivet($reply); // need to make asycronous
                    return $this->sendError('Forbidden');
                }

                $response = $this->parseMessage($content, $to_number);
                $status = 'new';
                $smsLog = new SmsLog;
                $smsLog->event = $event;
                $smsLog->message_type = $message_type;
                $smsLog->service_id = $service_id;
                $smsLog->from_number = $from_number;
                $smsLog->from_number_e164 = $request->input('from_number_e164');
                //$smsLog->api_project_id = $request->input('project_id');
                $smsLog->to_number = $to_number;
                $smsLog->content = $content; // incoming message
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $content. Carbon::now());
                $smsLog->status_secret = $uuid->toString();
                $smsLog->status_message = $response['message']; // reply message
                $smsLog->status = $response['status'];

                $smsLog->form_code = (array_key_exists('form_code', $response)) ? $response['form_code'] : 'Not Valid';

                $smsLog->section = (array_key_exists('section', $response)) ? $response['section'] : null; // not actual id from database, just ordering number from form
                $smsLog->result_id = (array_key_exists('result_id', $response)) ? $response['result_id'] : null;
                $smsLog->project_id = (array_key_exists('project_id', $response)) ? $response['project_id'] : null;
                $smsLog->sample_id = (array_key_exists('sample_id', $response)) ? $response['sample_id'] : null;

                $smsLog->remark = '';

                $reply['status_url'] = route('telerivet');
                $reply['status_secret'] = $smsLog->status_secret;
                break;
            case 'send_status':
                $status_secret = $request->input('secret');
                $smsLog = SmsLog::where('status_secret', $status_secret)->first();
                $status = $request->input('status');

                break;
            default:

                return $this->sendError('No valid Event.');
                break;

        }

        if($smsLog) {
            $smsLog->sms_status = (isset($status)) ? $status : null;

            $smsLog->save();

            if($event != 'send_status') {
                $reply['content'] = $response['message']; // reply message

                $this->sendToTelerivet($reply); // need to make asycronous
            }
        }
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
            $sent_sms = $project->sendMessage($reply);
            if( env('APP_DEBUG', false)) {
                Log::info(json_encode($sent_sms));
            }

        } catch (TelerivetAPIException $e) {
            if( env('APP_DEBUG', false)) {
                Log::info($e->getMessage());
            }
            return $this->sendError($e->getMessage());
        }
    }

    private function parseMessage($message, $to_number = '')
    {
        // look for Form Code and PCODE/Location code
        $match_code = preg_match('/^([a-zA-Z]+)(\d+)/', $message, $pcode);

        if ($match_code) {

            $projects = Project::all();

            $training_mode = Settings::get('training');

            $form_prefix = strtolower($pcode[1]);

            switch ($form_prefix) {
                case 'c':
                    $form_type = 'incident';
                    break;
                default:
                    $form_type = 'survey';
                    break;
            }

            if ($projects->count() === 1) {
                // if project is only one project use this project
                $project = Project::first();

            } else {

                // if not training mode
                $project = Project::whereRaw('LOWER(unique_code) =' . $form_prefix)->first();

                if (!empty($to_number) && empty($project)) {
                    // if to_number exists, look for project with phone number first
                    $projectPhone = ProjectPhone::where('phone', $to_number)->first();

                    if ($projectPhone) {
                        $project = $projectPhone->project;
                    }
                }

            }


            if (empty($project)) {
                // if project is empty
                $reply['message'] = 'Please check SMS format. Project code not found!';
                $reply['status'] = 'error';
                return $reply;
            }

            if ($project->status != 'published' && !$training_mode) {
                // if project is not published and not training mode
                $reply['message'] = 'Not ready! Please call to data center immediately.';
                $reply['status'] = 'error';
                return $reply;
            }

            $dbname = $project->dbname;

            $reply['form_code'] = $pcode[1] . $pcode[2];
            $sms_code = $pcode[2];

            if (!$training_mode) {

                $sms_type = config('sms.type');

                if ($sms_type == 'observer') {
                    $observer = Observer::where('code', $sms_code)->first();

                    $location_code = $observer->location->location_code;

                } else {
                    $location_code = $sms_code;
                }


                if (empty($location_code)) {
                    $reply['message'] = 'Please check location code in SMS. No such code found in database!';
                    $reply['status'] = 'error';
                    return $reply;
                }


                $sample_data = $project->samplesData->where('location_code', $location_code)->first();

                if(empty($sample_data)) {
                    $reply['message'] = 'Please check location code in SMS. No such code found in database!';
                    $reply['status'] = 'error';
                    return $reply;
                }

                if ($project->type != 'sample2db') { // if project type is not incident
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

                } else {
                    // if project is incident

                }

                if (empty($result)) {
                    $result = new SurveyResult();
                    $result->setTable($dbname);
                }
            } else {
                $result = new SurveyResult();
                $result->setTable($dbname . '_training');

                $result->sample_code = $sms_code;

                // if it is training mode
                if ($form_type == 'incident') {
                    $project = Project::where('training', true)->where('type', 'sample2db')->first();
                } elseif ($form_type != 'incident') {
                    $project = Project::where('training', true)->where('type', '<>', 'sample2db')->first();
                } else {
                    $project = Project::where('type', '<>', 'sample2db')->first();
                }
            }


            $message = strtolower($message);
            $sms_content = preg_replace('([^a-zA-Z0-9]*)', '', $message);
            // get all sections in a project
            $sections = $project->sectionsDb->sortBy('sort');
            $error_inputs = [];


            foreach ($sections as $key => $section) {
                $skey = $key + 1;
                $optional = 0; // initial count for optional inputs
                $questions = $section->questions;
                $question_completed = 0;
                $section_error_inputs = [];
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

                        if ($input->type == 'checkbox' || $question->surveyInputs->count() === 1) {
                            preg_match('/' . strtolower($question->qnum) . '(\d+)/', $sms_content, $response_match);
                        } else {
                            preg_match('/' . $inputkey . '(\d+)/', $sms_content, $response_match);
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

                            $checkbox_values = str_split($response);

                            $is_value_valid = (($input->type == 'checkbox' && in_array($input->value, $checkbox_values)) || ($input->type == 'radio' && $input->value == $response) || empty($input->value));

                            if ($is_value_valid) {

                                if ($input->type == 'checkbox') {

                                    // checkbox value is in sent message value
                                    if (in_array($input->value, $checkbox_values)) {
                                        $result->{$inputid} = $input->value;
                                    } else {
                                        $result->{$inputid} = null;
                                    }

                                } else {
                                    $result->{$inputid} = $response;
                                }

                            } else {
                                if ($input->type == 'checkbox') {
                                    $result->{$inputid} = null;
                                }
                            }
                            // unset $response  to avoid loop overwrite empty elements with previous value
                            unset($response);
                        }
                        // required input complete and count incremental
                        if (!$input->optional) {
                            if (!empty($result->{$inputid})) {
                                $section_inputs[$question->qnum] = $result->{$inputid};
                                $valid_response[] = $inputid;
                            } else {
                                if (!$training_mode) {
                                    if (empty($question->observation_type) || in_array($sample_data->observer_field, $question->observation_type)) {
                                        if (in_array($input->type, ['radio', 'checkbox'])) {
                                            $section_error_inputs[$question->qnum] = $question->qnum;
                                        } else {
                                            $error_key = strtoupper($inputkey);
                                            $section_error_inputs[$error_key] = $error_key;
                                        }
                                    }
                                } else {
                                    if (in_array($input->type, ['radio', 'checkbox'])) {
                                        $section_error_inputs[$question->qnum] = $question->qnum;
                                    } else {
                                        $error_key = strtoupper($inputkey);
                                        $section_error_inputs[$error_key] = $error_key;
                                    }
                                }
                            }
                        }

                    }


                    // for checkbox and radio
                    $required_response_with_value = $question->surveyInputs->filter(function ($input, $key) {
                        return (!$input->optional && $input->value);
                    })->pluck('inputid')->toArray();

                    // for text based inputs
                    $required_response_empty_value = $question->surveyInputs->filter(function ($input, $key) {
                        return (!$input->optional && empty($input->value));
                    })->pluck('inputid')->toArray();

                    $intersect_with_value = array_intersect($required_response_with_value, $valid_response);

                    if (count($intersect_with_value) > 0 || (!empty($required_response_empty_value) && $required_response_empty_value == $valid_response)) {
                        if (isset($section_error_inputs)) {
                            unset($section_error_inputs[$question->qnum]);
                        }
                        $question_completed++;
                    }
                    unset($required_response_with_value);
                    unset($required_response_empty_value);
                    unset($valid_response);
                    unset($intersect_with_value);
                }
                // get required questions in a section
                // question must not optional and observation type should be empty
                // or location observer field value should be in question observation type

                if (!$training_mode) {
                    $required_questions = $section->questions->filter(function ($question, $key) use ($sample_data) {
                        return !$question->optional && (empty($question->observation_type) || in_array($sample_data->observer_field, $question->observation_type));
                    });
                } else {
                    $required_questions = $section->questions->filter(function ($question, $key) {
                        return !$question->optional;
                    });
                }

                $error_inputs = array_unique(array_filter($section_error_inputs));

                unset($optional); // unset optional to avoid unexpect outcomes when checking section status


                if (!empty($section_inputs)) {

                    if ($required_questions->count() === $question_completed) {
                        $result->{'section' . $skey . 'status'} = 1;
                    } else {
                        $result->{'section' . $skey . 'status'} = 2;
                    }
                    $reply['section'] = $skey;

                    break; // this break and stop the loop - this is required not to process cross section data submit in one SMS
                } else {
                    $result->{'section' . $skey . 'status'} = 0;
                }
                $skey++;
            } // after section loop

            if (!$training_mode) {
                $result->sample()->associate($sample);
                $result->sample = $sample->data->sample;
                $result->user_id = 1; // need to change this
                $result->setTable($dbname); // need to set table name again for some reason
            } else {
                $result->setTable($dbname . '_training'); // need to set table name again for some reason
            }

            //$result = $this->logicalCheck($project, $result);

            $result->save();
            if (!empty($error_inputs)) {
                if (empty($section_inputs)) {
                    $reply['message'] = 'You did not sumbit valid response. Please check SMS format.';
                } else {
                    $reply['message'] = implode(', ', $error_inputs) . ' have problem. Please check SMS format.';
                }

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

    private function logicalCheck($project, $result)
    {

        return $result;
    }
}
