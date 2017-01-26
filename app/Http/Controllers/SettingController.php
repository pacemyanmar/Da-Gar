<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateSettingRequest;
use App\Http\Requests\SaveSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Repositories\SettingRepository;
use Flash;
use Illuminate\Http\Request;
use Krucas\Settings\Facades\Settings;
use Response;

class SettingController extends AppBaseController
{
    /** @var  SettingRepository */
    private $settingRepository;

    public function __construct(SettingRepository $settingRepo)
    {
        $this->middleware('auth');
        $this->settingRepository = $settingRepo;
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

        return view('settings.index')
            ->with('settings', $settings);
    }

    /**
     * Display a listing of the Questions.
     *
     * @param Request $request
     * @return Response
     */
    public function save(SaveSettingRequest $request)
    {
        $settings = $request->only('configs');

        foreach ($settings['configs'] as $key => $value) {
            Settings::set($key, $value);
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
        $model = $request->input('model');
        $locale = \App::getLocale();
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
                            $old_translation = $classInstance->{$translation_column};
                            $new_translation[$locale] = $translation;
                            if (!empty($old_translation)) {
                                $updated_translation = array_merge($old_translation, $new_translation);
                            } else {
                                $updated_translation = $new_translation;
                            }
                            $classInstance->{$translation_column} = $updated_translation;

                            $classInstance->save();
                        }
                    }
                }
            } else {

                $classInstance = $class::find($id);
                foreach ($columns as $column => $translation) {
                    $translation_column = $column . '_trans';
                    $old_translation = $classInstance->{$translation_column};
                    $new_translation[$locale] = $translation;
                    if (!empty($old_translation)) {
                        $updated_translation = array_merge($old_translation, $new_translation);
                    } else {
                        $updated_translation = $new_translation;
                    }
                    $classInstance->{$translation_column} = $updated_translation;

                    $classInstance->save();
                }
            }
        }

        return $this->sendResponse($classInstance, trans('messages.saved'));
    }
}
