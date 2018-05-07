<?php

namespace App\Http\Controllers;

use App\DataTables\TranslationDataTable;
use App\Http\Requests\CreateTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Models\Translation;
use Flash;
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
}
