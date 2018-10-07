<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSmsAPIRequest;
use App\Http\Requests\API\UpdateSmsAPIRequest;
use App\Models\Observer;
use App\Models\Project;
use App\Models\ProjectPhone;
use App\Models\Question;
use App\Models\Sample;
use App\Models\SampleData;
use App\Models\Section;
use App\Models\SmsLog;
use App\Models\SurveyResult;
use App\Models\User;
use App\Repositories\ProjectRepository;
use App\Repositories\SmsLogRepository;
use App\Traits\LogicalCheckTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Krucas\Settings\Facades\Settings;
use Laracasts\Flash\Flash;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Criteria\RequestCriteria;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Response;

/**
 * Class SmsController
 * @package App\Http\Controllers\API
 */
class SmsAPIController extends AppBaseController
{
    use LogicalCheckTrait;
    /** @var  SmsRepository */
    private $smsRepository;

    private  $projectRepository;

    private $project;

    private $section;

    private $sample;

    private $user_id;

    private $results;

    private $rawlog;

    public function __construct(SmsLogRepository $smsRepo,ProjectRepository $projectRepo)
    {
        $this->smsRepository = $smsRepo;
        $this->projectRepository = $projectRepo;
        App::setLocale(config('sms.second_locale.locale'));

    }

    public function apiStatus()
    {
        return $this->sendResponse('OK', 'API is running!');
    }
    public function echoResponse(Request $request)
    {
        return $this->sendResponse($request->all(), 'I give back what you give me :P');
    }

    public function recieveSms(Request $request)
    {
        if (env('APP_DEBUG', false)) {
            Log::info($request->all());
        }

        $secret = $request->input('secret');

        $callerid = $request->input('callerid');

        if(!empty($secret)) {
            $user = User::whereUsername('telerivet')->first();
            return $this->telerivet($request, $user);
        }

        if(!empty($callerid)) {
            $user = User::whereUsername('boom')->first();
            return $this->boom($request, $user);
        }

        if(!isset($user)) {
            return $this->sendError("Can't find any services", 404);
        }
    }

    public function boom(Request $request, User $user)
    {
        $code = $request->input('s');
        $boom_number = Settings::get('boom_number');
        $from_number = $request->input('callerid');
        if($refid = $request->input('refid')) {
            $event = 'delivery_status';
            $smsLog = SmsLog::where('service_id', $refid)->first();
            $smsLog->sms_status = $request->input('result_status');
            return $smsLog->save();
        }

        if($code == $boom_number) {

            $auth = Auth::loginUsingId($user->id);

            if (!Auth::check()) {
                $reply['content'] = trans('sms.forbidden');
                $this->sendToTelerivet($reply); // need to make asycronous
                return $this->sendError(trans('sms.forbidden'));
            }


            $log = [
                'from_number' => $from_number
            ];

            $message = $request->input('m');
            if($message) {
                $log['event'] = $event = 'incoming';
                $log['to_number'] = $to_number = $request->input('s');

                $log['response'] = $response = $this->parseMessage($message);

                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $message.'boomsms'.$from_number.$to_number . Carbon::now().rand());

                $log['status_secret'] = $status_uuid = $uuid->toString();
                $log['status'] = 'new';
                $log['message_type'] = 'incoming';
                $log['content'] = $message;
                $log['service_id'] = 'boomsms';
                $this->smsLogs($response, $log);
                $this->sendToBoom($response, $from_number, $status_uuid);
            }

        } else {
            return $this->sendError("Can't find any services", 404);
        }
    }

    public function sendToBoom($response, $to_number, $status_uuid)
    {
        $client = new Client();

        $message = $response['message']; // m
        $api_key = Settings::get('boom_api_key'); // p
        // hardcoded since this is provided by BOOM SMS
        $keyword = 'PA'; // k
        $user = 'PACE'; // u
        $title = 'PACE'; // t

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create();
        $stack->push($history);

        $form_params = ['handler' => $stack, 'form_params' => [
            'k' => $keyword,
            'u' => $user,
            'p' => $api_key,
            'm' => $message,
            't' => $title,
            'callerid' => $to_number
          ]
        ];
        $promise = $client->requestAsync('POST', 'http://apiv2.blueplanet.com.mm/mptsdp/bizsendsmsapi.php', $form_params);

        $promise->then(
            function (ResponseInterface $res) use($status_uuid) {
                $http_status = $res->getStatusCode();
                $response_body = json_decode($res->getBody(), true);

                $smsLog = SmsLog::where('status_secret', $status_uuid)->first();
                $smsLog->sms_status = ($response_body['result_name'])?$response_body['result_name']:$response_body['result_status'];
                $smsLog->service_id = (array_key_exists('result_refid', $response_body))?$response_body['result_refid']:$smsLog->service_id;
                $smsLog->save();
                return $res;
            },
            function (RequestException $e) {
                $error_msg = $e->getMessage();
                $request_method = $e->getRequest()->getMethod();
            }
        );
        $response = $promise->wait();
        return $this->sendResponse((string) $response->getBody(), 'Recieved!');
        // Iterate over the requests and responses
        //foreach ($container as $transaction) {
            //echo (string) $transaction['request']->getBody(); // Hello World
        //}

    }
    public function telerivet(Request $request, User $user)
    {
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

        $auth = Auth::loginUsingId($user->id);

        if (!Auth::check()) {
            $reply['content'] = trans('sms.forbidden');
            $this->sendToTelerivet($reply); // need to make asycronous
            return $this->sendError(trans('sms.forbidden'));
        }


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

        $this->user_id = $auth->id;

        switch ($event) {
            case 'incoming_message':
            case 'default':
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
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $content.$service_id.$from_number.$to_number . Carbon::now().rand());
                $smsLog->status_secret = $uuid->toString();
                $smsLog->status_message = $response['message']; // reply message
                $smsLog->status = $response['status'];

                $smsLog->form_code = (array_key_exists('form_code', $response)) ? $response['form_code'] : 'Not Valid';

                $smsLog->section = (array_key_exists('section', $response)) ? $response['section'] : null; // not actual id from database, just ordering number from form
                $smsLog->result_id = (array_key_exists('result_id', $response)) ? $response['result_id'] : null;
                $smsLog->project_id = (array_key_exists('project_id', $response)) ? $response['project_id'] : null;
                $smsLog->sample_id = (array_key_exists('sample_id', $response)) ? $response['sample_id'] : null;

                $smsLog->remark = '';

                $reply['status_url'] = route('recieve-sms');
                $reply['status_secret'] = $smsLog->status_secret;
                break;
            case 'send_status':
                $status_secret = $request->input('secret');
                $smsLog = SmsLog::where('status_secret', $status_secret)->first();
                $status = $request->input('status');

                break;
            default:

                return $this->sendError(trans('sms.no_valid_event'));
                break;

        }

        if ($smsLog) {
            $smsLog->sms_status = (isset($status)) ? $status : null;

            $smsLog->save();

            if ($event != 'send_status') {
                $reply['content'] = $response['message']; // reply message
                $no_reply = ($request->input('noreply'))?$request->input('noreply'):Settings::get('noreply');
                if(!$no_reply) {
                    $this->sendToTelerivet($reply); // need to make asycronous
                }
            }
        }
        return $this->sendResponse($reply, 'Message processed successfully');
    }

    private function sendToTelerivet($reply)
    {
        // from https://telerivet.com/api/keys
        if (Settings::has('telerivet_api_key')) {
            $API_KEY = Settings::get('telerivet_api_key');
        } else {
            return $this->sendError('API_KEY not found in your settings!');
        }
        if (Settings::has('project_id')) {
            $PROJECT_ID = Settings::get('project_id');
        } else {
            return $this->sendError('SMS PROJECT_ID not found in your settings!');
        }

        $telerivet = new \Telerivet_API($API_KEY);
        $project = $telerivet->initProjectById($PROJECT_ID);

        try {
            // Send a SMS message
            $sent_sms = $project->sendMessage($reply);
            if (env('APP_DEBUG', false)) {
                Log::info(json_encode($sent_sms));
            }

        } catch (\Telerivet_APIException $e) {
            if (env('APP_DEBUG', false)) {
                Log::info($e->getMessage());
            }
            return $this->sendError($e->getMessage());
        }
    }

    private function parseMessage($message, $to_number = '')
    {

        // Clean up code, look for Form Code and PCODE/Location code
        $message = strtolower($message);
        $message = preg_replace('([^a-zA-Z0-9]*)', '', $message);

        $match_code = preg_match('/^([a-zA-Z]+)(\d+)/', trim($message), $pcode);

        if(!$match_code) {
            $reply['message'] = 'ERROR';
            $reply['status'] = 'error';
            $reply['form_code'] = 'unknown';
            return $reply;
        } else {

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
                $project = Project::whereRaw('LOWER(unique_code) ="' . strtolower($form_prefix) . '"')->first();

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
                $reply['message'] = 'ERROR: '.strtoupper($form_prefix);
                $reply['status'] = 'error';
                return $reply;
            }

            $this->project = $project;

            $reply['project_id'] = $project->id;

            if ($project->status != 'published' && !$training_mode) {
                // if project is not published and not training mode
                $reply['message'] = trans('sms.not_ready');
                $reply['status'] = 'error';
                return $reply;
            }

            $dbname = $project->dbname;

            $reply['form_code'] = $pcode[2];

            if($project->frequencies > 1 && $project->copies > 1) {
                $sample_id = mb_substr($pcode[2], 0, -2);
                $frequency = mb_substr($pcode[2], -1, 1);
                $form_no = mb_substr($pcode[2], -2, 1);
            } elseif( $project->frequencies > 1 && $project->copies == 1) {
                $sample_id = mb_substr($pcode[2], 0, -1);
                $form_no = 1;
                $frequency = mb_substr($pcode[2], -1, 1);
            } elseif( 1 == $project->frequencies && $project->copies > 1) {
                $sample_id = mb_substr($pcode[2], 0, -1);
                $form_no = mb_substr($pcode[2], -1, 1);
                $frequency = 1;
            } else {
                $sample_id = $pcode[2];
                $form_no = 1;
                $frequency = 1;
            }

            $sample = $this->findSample($sample_id, $form_no, $frequency);

            if(empty($sample)) {
                $reply['message'] = 'ERROR: Check Code';
                $reply['status'] = 'error';
                return $reply;
            }

            $reply['sample_id'] = $sample->id;

            $message = str_replace($pcode[1].$pcode[2],'',$message);

            preg_match_all('/([a-zA-Z]+)(\d+)/', trim($message), $qna);

            /**
             * ['AC' => 1, // for radio
             *  'AD' => 134, // for checkbox
             *  'AE' => '#sometext#', // for text - for furture
             *  'AF' => '11011', // for number as text
             * ]
             */
            $qna_combined = array_combine($qna[1], $qna[2]);

            // check section of first question and set as current section
            $first_question = $project->questions->where('qnum', strtoupper($qna[1][0]))->first();
            $current_section = $first_question->sectionInstance;

            $this->section = $current_section;

            $reply['section'] = $current_section->sort;

            // get questions in a sections
            $questions = $current_section->questions;

            $cap_qna_combined = array_change_key_case($qna_combined, CASE_UPPER);
            $sms_results = [];
            $missingOrError = [];
            foreach($questions as $question) {
                $QNUM = strtoupper($question->qnum);
                if(array_key_exists($QNUM, $cap_qna_combined)) {
                    $surveyInputs = $question->surveyInputs;
                    $values = str_split($cap_qna_combined[$QNUM]);
                    foreach($surveyInputs as $input) {
                        switch ($input->type) {
                            case 'checkbox':
                                $value = (in_array($input->value, $values))? true:false;
                                break;
                            case 'radio':
                                $value = $cap_qna_combined[$QNUM];
                                break;
                            default:
                                $value = $cap_qna_combined[$QNUM];
                                break;
                        }
                        $sms_results[$input->inputid] = $value;
                    }

                } else {
                    // this question is missing in SMS
                    $missingOrError[] = $question->qnum;
                }
            }

            $allResults = $this->processUserInput($questions, $sms_results);

            $section_table = $dbname . '_s' . $this->section->sort;

            $this->sampleType = (isset($sample_type)) ? $sample_type : 1;

            if($training_mode) {
                $allResults['sample_code'] = $reply['form_code'];
            }

            $this->results = $allResults;

            $this->section = 'section' . $this->section->sort . 'status';

            if($training_mode) {
                $this->saveTrainingLogs($dbname . '_training');
                $reply['result_id'] = $this->traininglog->id;
            } else {
                $this->saveRawLogs($dbname . '_rawlog');
                $savedResult = $this->saveResults($section_table);
                $reply['result_id'] = $savedResult->id;
            }

            if(!empty($missingOrError)) {
                $reply['message'] = 'ERROR:'. implode(',', $missingOrError);
                $reply['status'] = 'error';
            } else {
                $reply['message'] = 'SUCCESS';
                $reply['status'] = 'success';
            }

            $reply['content'] = $allResults;
            return $reply;
        }

    }

    private function smsLogs(array $response, array $logs)
    {
        $default = [
            'from_number' => '0000000',
            'from_number_e164' => '0000000',
            'to_number' => '0000000',
            'event' => 'default',
            'content' => 'NO CONTENT',
            'message_type' => 'default',
            'service_id' => '0000000',
        ];

        $log_data = array_merge($default, $logs);

        $status = 'new';
        $smsLog = new SmsLog;
        $smsLog->event = $event = $log_data['event'];
        $smsLog->message_type = $log_data['message_type'];
        $smsLog->service_id = $service_id = $log_data['service_id'];
        $smsLog->from_number = $from_number = $log_data['from_number'];
        $smsLog->from_number_e164 = $log_data['from_number_e164'];
        //$smsLog->api_project_id = $request->input('project_id');
        $smsLog->to_number = $to_number = $log_data['to_number'];
        $smsLog->content = $content=  $log_data['content']; // incoming message
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $content.$service_id.$from_number.$to_number . Carbon::now().rand());
        $smsLog->status_secret = ($log_data['status_secret'])?$log_data['status_secret']:$uuid->toString();
        $smsLog->status_message = $response['message']; // reply message
        $smsLog->status = $response['status'];

        $smsLog->form_code = (array_key_exists('form_code', $response)) ? $response['form_code'] : 'Not Valid';

        $smsLog->section = (array_key_exists('section', $response)) ? $response['section'] : null; // not actual id from database, just ordering number from form
        $smsLog->result_id = (!empty($this->rawlog))?$this->rawlog->id:$response['result_id'];
        $smsLog->project_id = (array_key_exists('project_id', $response)) ? $response['project_id'] : null;
        $smsLog->sample_id = (array_key_exists('sample_id', $response)) ? $response['sample_id'] : null;

        $smsLog->remark = '';


        $smsLog->sms_status = (isset($status)) ? $status : null;

        $smsLog->save();

        return $smsLog;

    }

    /*
     * To save parsed raw result
     */
    private function saveRawLogs($table)
    {


        $rawlog = new SurveyResult();

        $rawlog->setTable($table);
        $rawlog->user_id = Auth()->user()->id;
        $rawlog->sample_id = $this->sample->id;
        $rawlog->sample_type = $this->project->type;

        // need to remove this
        foreach($this->project->sections as $section){
            $section_status_name = 'section'.$section->sort.'status';
            $rawlog->{$section_status_name} = 0;
        }
        foreach ($this->results as $input => $value) {
            $rawlog->{$input} = $value;
        }
        $rawlog->save();
        $this->rawlog = $rawlog;
    }

    /*
     * To save parsed raw result
     */
    private function saveTrainingLogs($table)
    {
        $traininglog = new SurveyResult();
        $traininglog->setTable($table);
        //$traininglog->user_id = Auth()->user()->id;
        //$traininglog->sample_id = $this->sample->id;
        //$traininglog->sample_type = $this->project->type;

        // need to remove this
        foreach($this->project->sections as $section){
            $section_status_name = 'section'.$section->sort.'status';
            $traininglog->{$section_status_name} = 0;
        }
        foreach ($this->results as $input => $value) {
            $traininglog->{$input} = $value;
        }
        $traininglog->sample_code = $this->sample->sample_data_id;
        $traininglog->save();
        $this->traininglog = $traininglog;
    }

    private function findSample($sample_id, $form_no = 1, $frequency = 1) {
        $project = $this->project;
        return $this->sample = $project->samplesList->where('project_id', $this->project->id)->where('sample_data_id', $sample_id)->where('form_id', $form_no)->where('frequency', $frequency) ->first();
    }

    private function parseMessageBak($message, $to_number = '')
    {
        // Clean up code, look for Form Code and PCODE/Location code
        $message = strtolower($message);
        $message = preg_replace('([^a-zA-Z0-9]*)', '', $message);
        
        $match_code = preg_match('/^([a-zA-Z]+)(\d+)/', trim($message), $pcode);

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
                $project = Project::whereRaw('LOWER(unique_code) ="' . $form_prefix . '"')->first();

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
                $reply['message'] = 'ERROR: '.strtoupper($form_prefix);
                $reply['status'] = 'error';
                return $reply;
            }

            if ($project->status != 'published' && !$training_mode) {
                // if project is not published and not training mode
                $reply['message'] = trans('sms.not_ready');
                $reply['status'] = 'error';
                return $reply;
            }

            $dbname = $project->dbname;

            $reply['form_code'] = $pcode[2];
            $sms_code = $pcode[2];

            $message = str_replace($pcode[1].$pcode[2],'',$message);

            if (!$training_mode) {

                $sms_type = config('sms.type');

                if ($sms_type == 'observer') {
                    $observer = Observer::where('code', $sms_code)->first();

                    if($observer) {
                        if($observer->location) {
                            $location_code = $observer->location->location_code;
                        }
                    }

                }

                if(!isset($location_code)) {
                    $location_code = $sms_code;
                }


                if (empty($location_code)) {
                    $reply['message'] = 'ERROR: '.strtoupper($form_prefix);
                    $reply['status'] = 'error';
                    return $reply;
                }


                if ($project->type != 'sample2db') { // if project type is not incident
                    $sample_data = $project->samplesData->where('location_code', $location_code)->first();
                } else {
                    $sample_data = SampleData::where('location_code', $location_code)->where('type', 'fixed')->first();
                }


                if (empty($sample_data)) {
                    $reply['message'] = 'ERROR: '.strtoupper($form_prefix);
                    $reply['status'] = 'error';
                    return $reply;
                }

                if ($project->type != 'sample2db') { // if project type is not incident
                    // look for Form ID
                    preg_match('/fnnnnnn(\d+)/', $message, $form_id); // this is temporary form number code
                    if ($form_id) {
                        $form_number = $form_id[1];
                    } else {
                        $form_number = 1;
                    }
                    $sample = $sample_data->samples->where('sample_data_type', 'fixed')->where('form_id', $form_number)->first();

                    $sample->setRelatedTable($dbname);

                    $result = $sample->resultWithTable($dbname)->first();

                } else {
                    // if project is incident
                    $last_incident = Sample::where('sample_data_id', $sample_data->id)
                        ->where('project_id', $project->id)
                        ->where('sample_data_type', $project->dblink)->orderBy('form_id', 'desc')->first();
                    if ($last_incident) {
                        $last_id = $last_incident->form_id + 1;
                    } else {
                        $last_id = 1;
                    }


                    $sample = Sample::firstOrCreate(['sample_data_id' => $sample_data->id, 'form_id' => $last_id, 'project_id' => $project->id, 'sample_data_type' => $project->dblink]);
                    $sample->setRelatedTable($dbname);

                    $result = $sample->resultWithTable($dbname)->first();
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

            $rawlog = new SurveyResult();
            $rawlog->setTable($dbname.'_rawlog');

            // get all sections in a project
            $sections = $project->sections->sortBy('sort');
            $error_inputs = [];

            $result_arr = [];

            $section_with_result = '';
            $section_key = '';
            foreach ($sections as $key => $section) {
                $skey = $key + 1;
                $optional = 0; // initial count for optional inputs
                $questions = $section->questions;
                $question_completed = 0;
                $section_error_inputs = [];
                if(!$section->disablesms) {
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
                                preg_match('/' . strtolower($question->qnum) . '(\d+)/', $message, $response_match);
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

                                // To Do :
                                // if checkbox, split string by one character
                                // if not use as is.
                                switch ($input->type) {
                                    case 'checkbox':
                                    case 'radio':
                                        $checkbox_values = str_split($response);
                                        $inputs_values = $inputs->pluck('value')->toArray();
                                        $invalid_values = array_diff($checkbox_values, $inputs_values);

                                        $valid_values = array_intersect($inputs_values, $checkbox_values);
                                        // invalid values are empty and valid values are not empty, use value from user input
                                        // else use value from result database
                                        if (empty($invalid_values) && !empty($valid_values)) {
                                            if ($input->type == 'checkbox') {
                                                $rawlog->{$inputid} = $result_arr[$section->id][$question->id][$inputid] = (in_array($input->value, $checkbox_values)) ? $input->value : 0;
                                            } else {
                                                $rawlog->{$inputid} = $result_arr[$section->id][$question->id][$inputid] = (in_array($response, $inputs_values)) ? $response : null;
                                            }

                                        } else {
                                            $rawlog->{$inputid} = $result_arr[$section->id][$question->id][$inputid] = null;
                                        }

                                        break;
                                    default:
                                        $rawlog->{$inputid} = $result_arr[$section->id][$question->id][$inputid] = $response;
                                        break;
                                }

                                if (empty($section_with_result)) {
                                    $section_with_result = $section->id;

                                    $section_key = $section->sort + 1;
                                }

                                if (!empty($section_with_result) && $section->id != $section_with_result) {
                                    // if sending cross section

                                    $rawlog->sample = $sample->data->sample;
                                    $rawlog->user_id = 1; // need to change this

                                    $rawlog->sample_id = $sample->id;
                                    $rawlog->save();
                                    $reply['sample_id'] = $rawlog->id;
                                    $reply['project_id'] = $project->id;
                                    $reply['result_id'] = $result->id;
                                    $reply['message'] = 'ERROR';
                                    $reply['status'] = 'error';
                                    return $reply;
                                }
//
                                // unset $response  to avoid loop overwrite empty elements with previous value
                                unset($response);
                            } else {
                                $result_arr[$section->id][$question->id][$inputid] = $result->{$inputid};
                                $rawlog->{$inputid} = null;
                            }


                            if (!$input->optional) {
                                if (!empty($rawlog->{$inputid})) {

                                    $section_inputs[$question->qnum] = $result_arr[$section->id][$question->id][$inputid];
                                    $valid_response[] = $inputid;

                                } else {
                                    if (!empty($result->{$inputid}) && $input->type != 'checkbox') {

                                        $section_inputs[$question->qnum] = $result->{$inputid};

                                    } else {
                                        $section_inputs[$question->qnum] = null;
                                    }


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

                        } // after input loop

                    } // after question loop
                }

                unset($optional); // unset optional to avoid unexpect outcomes when checking section status


            } // after section loop

            if (!$training_mode) {
                $result->sample()->associate($sample);
                $rawlog->sample = $result->sample = $sample->data->sample;
                $rawlog->user_id = $result->user_id = 1; // need to change this
                $result->setTable($dbname); // need to set table name again for some reason
                $rawlog->sample_id = $sample->id;
                $rawlog->save();
                $reply['sample_id'] = $rawlog->id;
            } else {
                $result->setTable($dbname . '_training'); // need to set table name again for some reason
                $sample = Sample::where('sample_data_type', $project->dblink)->where('project_id', $project->id)->where('form_id', 1)->first();
            }
            if($section_with_result) {
                
                $checked = $this->logicalCheck($result_arr, $result, $project, $sample);
                $result = $checked['results'];
                $timestamp = 'section'.$section_key.'updated';
                $result->{$timestamp} = Carbon::now();

                $result->save();

                if (!empty($checked['error'][$section_with_result])) {
                    if (empty($section_inputs)) {
                        $reply['message'] = 'ERROR';
                    } else {
                        $errors = array_unique($checked['error'][$section_with_result]);
                        $reply['message'] = 'ERROR: SMS '.$section_key.': '. implode(', ', $errors);
                    }

                    $reply['status'] = 'error';
                } else {
                    if($form_type == 'incident') {
                        $reply['message'] = 'OK: INCIDENT';
                    } else {
                        $reply['message'] = (isset($section_key))?'OK: SMS '.$section_key:'OK: SMS';
                    }

                    $reply['status'] = 'success';
                }
            } else {
                $reply['message'] = 'ERROR';
                $reply['status'] = 'error';
            }


            $reply['section'] = (isset($section_key) && !empty($section_key))?$section_key:null;
            $reply['result_id'] = $result->id;
            $reply['project_id'] = $project->id;

            return $reply;
        } else {
            $reply['message'] = 'ERROR';
            $reply['status'] = 'error';
            $reply['form_code'] = 'unknown';
            return $reply;
        }
    }

    public function getcsv($project_id, Request $request) {
        $allowedip = config('sms.allowedip');
        Log::info($request->getClientIp());
        if(!in_array($request->getClientIp(),$allowedip)) {
            Flash::error('Project not found');

            return redirect('home');
        }

        App::setLocale('en');
        $project = $this->projectRepository->findWithoutFail($project_id);
        if (empty($project)) {
            Flash::error('Project not found');

            return redirect()->back();
        }

        $inputs = $project->inputs->sortBy('sort');

        $comment = $request->input('comments');

        $request_columns = $request->input('columns');

        $request_columns = explode(',', $request_columns);

        $map_inputs = $inputs->map(function($input, $key) use ($request_columns,$comment) {
            $inputid = null;
            if(!empty(array_filter($request_columns))) {
                if(in_array(strtolower($input->question->qnum), $request_columns)) {
                    $inputid =  $input->inputid;
                } else {
                    $inputid =  null;
                }
            } elseif( $comment == 'off' ) {
                if(str_contains($input->inputid, 'comment')) {
                    $inputid =  null;
                } else {
                    $inputid = $input->inputid;
                }
            } else {
                $inputid = $input->inputid;
            }

            if($inputid) {
                switch ($input->type) {
                    case 'checkbox':
                        $column = 'IF(pdb.' . $inputid . ' IS NOT NULL,IF(pdb.' . $inputid . ' = 0, 0, 1),null) AS ' . $inputid;
                        break;
                    default:
                        $column = 'pdb.' . $inputid;
                        break;
                }

                return $column;
            }

        });


        $childTable = $project->dbname;

        $unique_inputs = $map_inputs->toArray();

        $sample_columns = [
            'location_code',
            'ps_code',
            'updated_at',
            'observer1_id',
            'observer2_id',
            'sbo',
            'pvt1 AS '. strtolower(trans('sample.pvt1')),
            'pvt2 AS '. strtolower(trans('sample.pvt2')),
            'pvt3 AS '. strtolower(trans('sample.pvt3')),
            strtolower(snake_case(trans('sample.level1_id'))),

        ];

        $sectionColumns = [];
        foreach ($project->sections->sortBy('sort') as $k => $section) {
            $skey = $k + 1;
            $sectionColumns[] = 'pdb.section' . $skey . 'status, pdb.section'.$skey.'updated';
        }

        $export_columns = array_merge($sample_columns, $sectionColumns, $unique_inputs);

        $export_columns = array_filter($export_columns);

        $export_columns_list = implode(',', $export_columns);

        if (!Schema::hasTable('sdata_view')) {
            DB::statement("CREATE VIEW sdata_view AS (SELECT sd.id AS sdid, sd.location_code, sd.observer1_id, sd.observer2_id, sd.sample, sd.ps_code, sd.area_type, 
                           sd.level6 AS ".snake_case(trans('sample.level6')).", sd.level5 AS ".snake_case(trans('sample.level5')).", 
                           sd.level4 AS ".snake_case(trans('sample.level4')).", sd.level3 AS ".snake_case(trans('sample.level3')).", 
                           sd.level2 AS ".snake_case(trans('sample.level2')).", sd.level1 AS ".snake_case(trans('sample.level1')).", 
                           sd.level1_id AS ".snake_case(trans('sample.level1_id')).", 
                           sd.obs_type, sd.sbo, sd.pvt1, sd.pvt2, sd.pvt3, sd.pvt4, 
                           sd.parties, sd.sms_time, sd.observer_field, GROUP_CONCAT(ob.code) AS observer_code  
                           FROM sample_datas AS sd LEFT JOIN observers AS ob ON ob.sample_id = sd.id  GROUP BY sd.id, sd.location_code, sd.sample,
                           sd.ps_code, sd.area_type, ".snake_case(trans('sample.level6')).", 
                           ".snake_case(trans('sample.level5')).", 
                           ".snake_case(trans('sample.level4')).", ".snake_case(trans('sample.level3')).", 
                           ".snake_case(trans('sample.level2')).", ".snake_case(trans('sample.level1')).", 
                           ".snake_case(trans('sample.level1_id')).", sd.obs_type, sd.sbo, sd.pvt1, sd.pvt2, sd.pvt3, sd.pvt4,
                           sd.parties, sd.sms_time, sd.observer_field, sd.observer1_id, sd.observer2_id)");
        }


        if (!Schema::hasTable($project->dbname.'sample_view')) {
            DB::statement("CREATE VIEW ".$project->dbname."sample_view AS (SELECT * FROM samples JOIN sdata_view AS sdata ON sdata.sdid = samples.sample_data_id 
                           WHERE samples.sample_data_type = '$project->dblink' AND samples.project_id = '$project->id')");
        }




        $results = DB::select("SELECT $export_columns_list FROM ".$project->dbname."sample_view AS psv  LEFT JOIN $project->dbname as pdb ON psv.id = pdb.sample_id");

        $filename = preg_replace('/[^a-zA-Z0-9\s]/','', $project->project).' '.date("Y-m-d-H-i-s");
        Excel::create($filename, function($excel) use ($results) {

            $excel->sheet('result', function($sheet) use ($results) {
                $rowdata = [];
                foreach($results as $result) {
                    $rowdata[] = (array) $result;
                }
                $sheet->fromArray($rowdata, null, 'A1', true);
            });

        })->store('csv')->export('csv');
    }

}
