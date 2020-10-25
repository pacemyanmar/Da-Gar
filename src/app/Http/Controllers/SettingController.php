<?php
namespace App\Http\Controllers;

use Akaunting\Setting\Facade as Settings;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateSettingRequest;
use App\Http\Requests\SaveSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Repositories\SettingRepository;
use Flash;
use Illuminate\Http\Request;
use Response;
use Spatie\TranslationLoader\LanguageLine;

class SettingController extends AppBaseController
{
    /** @var  SettingRepository */
    private $settingRepository;

    private $project;


    public function __construct(SettingRepository $settingRepo, Project $project)
    {
        $this->middleware('auth');
        $this->settingRepository = $settingRepo;
        $this->project = $project;
    }
    /**
     * Display a listing of the Questions.
     *
     * @param Request $request
     * @return Response
     */
    public function index()
    {
        $settings = $this->settingRepository->all();

        $projects = array_merge(['None' => ''], $this->project->pluck('id', 'project')->toArray());
        return view('settings.index')
            ->with('settings', $settings)
            ->with('projects', $projects);
    }
    /**
     * Display a listing of the Questions.
     *
     * @param Request $request
     * @return Response
     */
    public function save(SaveSettingRequest $request)
    {
        $settings = $request->input('configs');

        $settings['training'] = array_key_exists('training', $settings);
        $settings['noreply'] = array_key_exists('noreply', $settings);
        $settings['show_projects'] = ($settings['show_projects'])??[''];
        foreach ($settings as $key => $value) {
            Settings::set($key, $value);
            if($key == 'telerivet_api_key' && !empty($value)) {
                $user = User::firstOrNew(['username' => 'telerivet']);
                $user->name = 'Telerivet';
                $user->email = 'telerivet';
            }

            if($key == 'boom_api_key' && !empty($value)) {
                $user = User::firstOrNew(['username' => 'boom']);
                $user->name = 'Boom';
                $user->email = 'boom';
            }

            if(isset($user)) {
                if (empty($user->api_token)) {
                    $user->api_token = str_random(32);
                }
                $user->password = bcrypt(str_random());
                $role = $guest = Role::where('role_name', 'smsapi')->first();
                $user->role()->associate($role);
                $user->save();
            }
        }

        Flash::info('Settings updated successfully.');
        return redirect()->back();
    }
    /**
     * Show the form for creating a new Setting.
     *
     * @return Response
     */
    public function create()
    {
        return view('settings.create');
    }
    /**
     * Store a newly created Setting in storage.
     *
     * @param CreateSettingRequest $request
     *
     * @return Response
     */
    public function store(CreateSettingRequest $request)
    {
        $key = $request->only('key');
        $value = $request->only('value');
        $setting = Settings::set($key['key'], $value['value']);
        Flash::success('Setting saved successfully.');
        return redirect(route('settings.index'));
    }
    /**
     * Display the specified Setting.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $setting = $this->settingRepository->findWithoutFail($id);
        if (empty($setting)) {
            Flash::error('Setting not found');
            return redirect(route('settings.index'));
        }
        return view('settings.show')->with('setting', $setting);
    }
    /**
     * Show the form fqor editing the specified Setting.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $setting = $this->settingRepository->findWithoutFail($id);
        if (empty($setting)) {
            Flash::error('Setting not found');
            return redirect(route('settings.index'));
        }
        return view('settings.edit')->with('setting', $setting);
    }
    /**
     * Update the specified Setting in storage.
     *
     * @param  int              $id
     * @param UpdateSettingRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSettingRequest $request)
    {
        $key = $request->only('key');
        $value = $request->only('value');
        $setting = Settings::set($key['key'], $value['value']);
        Flash::success('Setting updated successfully.');
        return redirect(route('settings.index'));
    }
    /**
     * Remove the specified Setting from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $setting = $this->settingRepository->findWithoutFail($id);
        if (empty($setting)) {
            Flash::error('Setting not found');
            return redirect(route('settings.index'));
        }
        $this->settingRepository->delete($id);
        Flash::success('Setting deleted successfully.');
        return redirect(route('settings.index'));
    }

    public function translate($id, Request $request)
    {
        $group = $request->input('group');
        $key = $request->input('key');
        $origin = $request->input('origin');
        $translation = $request->input('translation');

        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');
        $language_line = LanguageLine::firstOrNew([
            'group' => $group,
            'key' => $key
        ]);

        $language_line->text = [$primary_locale => $origin, $second_locale => $translation];
        $language_line->save();
    }

    public function translateBak($id, Request $request)
    {
        $model = $request->input('model');
        $locale = config('sms.second_locale.locale');
        if ($locale == config('app.fallback_locale')) {
            return;
        }
        $columns = $request->input('columns'); //array
        if (!empty($model) && class_exists('App\Models\\' . studly_case($model))) {
            // set dblink model class
            $class = 'App\Models\\' . studly_case($model);
            if ($id == 'group') {
                //dd($request->all());
                $qid = $request->input('qid');
                $inputs = $request->input('input');
                if ($qid) {
                    $classInstances = $class::where('question_id', $qid)->where('inputid', $inputs)->get();
                    foreach ($classInstances as $classInstance) {
                        $extras = $classInstance->extras;
                        foreach ($columns as $column => $translation) {
                            //$extras['lang'][$locale][$column] = Converter::convert($translation, 'zawgyi', 'unicode');
                            $extras['lang'][$locale][$column] = $translation;
                        }
                        $classInstance->extras = $extras;
                        $classInstance->save();
                    }
                } else {
                    foreach ($inputs as $input_id) {
                        $classInstance = $class::find($input_id);
                        foreach ($columns as $column => $translation) {
                            $translation_column = $column . '_trans';

                            //$new_translation[$locale] = Converter::convert($translation, 'zawgyi', 'unicode');


                            $classInstance->{$translation_column} = $translation;
                            $classInstance->save();
                        }
                    }
                }
            } else {
                $classInstance = $class::find($id);
                foreach ($columns as $column => $translation) {
                    $translation_column = $column . '_trans';
                    //$new_translation[$locale] = Converter::convert($translation, 'zawgyi', 'unicode');

                    $classInstance->{$translation_column} = $translation;
                    $classInstance->save();
                }
            }
        }
        return $this->sendResponse($classInstance, trans('messages.saved'));
    }
}
