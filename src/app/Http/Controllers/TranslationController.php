<?php

namespace App\Http\Controllers;

use App\DataTables\TranslationDataTable;
use App\Http\Requests\CreateTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Models\Translation;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use League\Csv\Reader;
use League\Csv\Statement;
use Response;
use Spatie\TranslationLoader\LanguageLine;

class TranslationController extends AppBaseController
{
    /** @var  Translation Model */
    private $translation;

    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Display a listing of the Translation.
     *
     * @param TranslationDataTable $translationDataTable
     * @return Response
     */
    public function index(TranslationDataTable $translationDataTable)
    {
        return $translationDataTable->render('translations.index');
    }

    /**
     * Show the form for creating a new Translation.
     *
     * @return Response
     */
    public function create()
    {
        return view('translations.create');
    }

    /**
     * Store a newly created Translation in storage.
     *
     * @param CreateTranslationRequest $request
     *
     * @return Response
     */
    public function store(CreateTranslationRequest $request)
    {
        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');
        LanguageLine::create([
            'group' => $request->group,
            'key' => $request->key,
            'text' => [$primary_locale => $request->{$primary_locale}, $second_locale => $request->{$second_locale}]
        ]);

        Cache::forget("spatie.translation-loader.{$request->group}.{$primary_locale}");
        Cache::forget("spatie.translation-loader.{$request->group}.{$second_locale}");

        Flash::success('Translation saved successfully.');

        return redirect(route('translations.index'));
    }

    /**
     * Display the specified Translation.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $translation = $this->translation->find($id);

        if (empty($translation)) {
            Flash::error('Translation not found');

            return redirect(route('translations.index'));
        }

        return view('translations.show')->with('translation', $translation);
    }

    /**
     * Show the form for editing the specified Translation.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $translation = $this->translation->find($id);

        if (empty($translation)) {
            Flash::error('Translation not found');

            return redirect(route('translations.index'));
        }

        return view('translations.edit')->with('translation', $translation);
    }

    /**
     * Update the specified Translation in storage.
     *
     * @param  int $id
     * @param UpdateTranslationRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateTranslationRequest $request)
    {
        $translation = $this->translation->find($id);

        if (empty($translation)) {
            Flash::error('Translation not found');

            return redirect(route('translations.index'));
        }

        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');

        $translation->group = $request->group;
        $translation->key = $request->key;
        $translation->text = [$primary_locale => $request->{$primary_locale}, $second_locale => $request->{$second_locale}];

        $translation->save();

        Cache::forget("spatie.translation-loader.{$request->group}.{$primary_locale}");
        Cache::forget("spatie.translation-loader.{$request->group}.{$second_locale}");

        Flash::success('Translation updated successfully.');

        return redirect(route('translations.index'));
    }

    /**
     * Remove the specified Translation from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $translation = $this->translation->find($id);

        if (empty($translation)) {
            Flash::error('Translation not found');

            return redirect(route('translations.index'));
        }

        $translation->delete();

        Flash::success('Translation deleted successfully.');

        return redirect(route('translations.index'));
    }


    public function import(Request $request)
    {
        if ($request->file('file')->isValid()) {

            $idcolumn = $request->input('idcolumn');

            if (!ini_get("auto_detect_line_endings")) {
                ini_set("auto_detect_line_endings", '1');
            }

            $reader = Reader::createFromPath($request->file->path());
            $reader->setHeaderOffset(0);

            $stmt = (new Statement());
            $records = $stmt->process($reader);

            $headers = $records->getHeader();

            array_walk($headers, function (&$slug, $key) {
                $slug = str_dbcolumn($slug);

            });

            $data_array = iterator_to_array($records, true);


            $primary_locale = config('sms.primary_locale.locale');
            $second_locale = config('sms.second_locale.locale');


            array_walk($data_array, function (&$data, $key) use ( $primary_locale, $second_locale) {
                $data = array_change_key_case($data, CASE_LOWER);

                $data['text'][$primary_locale] = $data[$primary_locale];

                $data['text'][$second_locale] = $data[$second_locale];
                unset($data[$primary_locale]);
                unset($data[$second_locale]);
                $data['text'] = json_encode($data['text'], JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
            });

            $translation = new Translation();
            $translation->insertOrUpdate($data_array);

            $translation_groups = array_unique(Arr::pluck($data_array, 'group'));
            foreach($translation_groups as $group) {
                Cache::forget("spatie.translation-loader.{$group}.{$primary_locale}");
                Cache::forget("spatie.translation-loader.{$group}.{$second_locale}");
            }

            Flash::success('Translation imported successfully.');

            return redirect(route('translations.index'));
        }
    }
}
