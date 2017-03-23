<?php

namespace App\Http\Controllers;

use App\DataTables\SampleDataDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateSampleDataRequest;
use App\Http\Requests\UpdateSampleDataRequest;
use App\Models\SampleData;
use App\Repositories\SampleDataRepository;
use Flash;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Response;

class SampleDataController extends AppBaseController
{
    /** @var  SampleDataRepository */
    private $sampleDataRepository;

    public function __construct(SampleDataRepository $sampleDataRepo)
    {
        $this->middleware('auth');
        $this->sampleDataRepository = $sampleDataRepo;
    }

    /**
     * Display a listing of the SampleData.
     *
     * @param SampleDataDataTable $sampleDataDataTable
     * @return Response
     */
    public function index(SampleDataDataTable $sampleDataDataTable)
    {
        return $sampleDataDataTable->render('sample_datas.index');
    }

    /**
     * Show the form for creating a new SampleData.
     *
     * @return Response
     */
    public function create()
    {
        return view('sample_datas.create');
    }

    /**
     * Store a newly created SampleData in storage.
     *
     * @param CreateSampleDataRequest $request
     *
     * @return Response
     */
    public function store(CreateSampleDataRequest $request)
    {
        $input = $request->all();

        $sampleData = $this->sampleDataRepository->create($input);

        Flash::success('Sample Data saved successfully.');

        return redirect(route('sampleDatas.index'));
    }

    /**
     * Display the specified SampleData.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        return view('sample_datas.show')->with('sampleData', $sampleData);
    }

    /**
     * Show the form for editing the specified SampleData.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        return view('sample_datas.edit')->with('sampleData', $sampleData);
    }

    /**
     * Update the specified SampleData in storage.
     *
     * @param  int              $id
     * @param UpdateSampleDataRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSampleDataRequest $request)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        $sampleData = $this->sampleDataRepository->update($request->all(), $id);

        Flash::success('Sample Data updated successfully.');

        return redirect(route('sampleDatas.index'));
    }

    /**
     * Remove the specified SampleData from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $sampleData = $this->sampleDataRepository->findWithoutFail($id);

        if (empty($sampleData)) {
            Flash::error('Sample Data not found');

            return redirect(route('sampleDatas.index'));
        }

        $this->sampleDataRepository->delete($id);

        Flash::success('Sample Data deleted successfully.');

        return redirect(route('sampleDatas.index'));
    }

    public function import(Request $request)
    {
        $file = $request->file('samplefile');
        $type = $request->only('type');
        $group = $request->only('dbgroup');
        Excel::load($file, function ($reader) use ($type, $group) {
            $reader->each(function ($row) use ($type, $group) {

                /**
                "pace_id" => "02202"
                "state_region" => "Bago (East)"
                "district" => "Bago"
                "township" => "Kyauktaga"
                "ward" => null
                "village_tract" => "Inn Pat Lel"
                "village" => "Bon Taw"
                "m" => "U Soe Win"
                "nrc_no" => "7/NyaLaPa(N)102212"
                "gender" => "M"
                "date_of_birth" => "27.7.62"
                "edu_background" => "B.Sc (Botany)"
                "ethinicity" => "Bamar"
                "language" => null
                "current_occupation" => null
                "phone_no_1" => "09-428010716"
                "phone_no_2" => "09-794143708"
                "address" => "No (30) MyoMa(4) Ward, Zayyarminlwin Ward, Kyauktaga"
                "bank_information" => null
                "mobile_singal_info" => "MPT-OOREDOO"
                 */
                $sampleData = new SampleData();

                $area_type = ($row->area_type) ? $row->area_type : null;

                if (empty($area_type)) {
                    $area_type = ($row->ward) ? 1 : 2;
                }

                $row_array = [
                    "idcode" => ($row->id_code) ? (string) $row->id_code : null,
                    "spotchecker_code" => ($row->spotchecker_code) ? (string) $row->spotchecker_code : null,
                    "sample" => ($row->sample) ? $row->sample : 1,
                    "area_type" => $area_type,
                    "state" => ($row->state) ? $row->state : null,
                    "district" => ($row->district) ? $row->district : null,
                    "township" => ($row->township) ? $row->township : null,
                    "village_tract" => ($row->village_tract) ? $row->village_tract : null,
                    "ward" => ($row->ward) ? $row->ward : null,
                    "village" => ($row->village) ? $row->village : null,
                    "code" => ($row->observer_id) ? $row->observer_id : ($row->observer_1) ? $row->observer_1 : null,
                    "name" => ($row->name) ? $row->name : ($row->name_1) ? $row->name_1 : null,
                    "father" => ($row->father) ? $row->father : ($row->father_1) ? $row->father_1 : null,
                    "mother" => ($row->mother) ? $row->mother : ($row->mother_1) ? $row->mother_1 : null,
                    "current_org" => ($row->current_occupation) ? $row->current_occupation : ($row->current_occupation_1) ? $row->current_occupation_1 : null,
                    "mobile" => ($row->phone_no_1) ? $row->phone_no_1 : ($row->phone_no_1_1) ? $row->phone_no_1_1 : null,
                    "line_phone" => ($row->phone_no_2) ? $row->phone_no_2 : ($row->phone_no_2_1) ? $row->phone_no_2_1 : null,
                    "nrc_id" => ($row->nrc_no) ? $row->nrc_no : ($row->nrc_no_1) ? $row->nrc_no_1 : null,
                    "gender" => ($row->gender) ? $row->gender : ($row->gender_1) ? $row->gender_1 : null,
                    "ethnicity" => ($row->ethnicity) ? $row->ethnicity : ($row->ethnicity_1) ? $row->ethnicity_1 : null,
                    "dob" => ($row->date_of_birth) ? date("Y-m-d", strtotime($row->date_of_birth)) : ($row->date_of_birth_1) ? date("Y-m-d", strtotime($row->date_of_birth_1)) : null,
                    "education" => ($row->edu_background) ? $row->edu_background : ($row->edu_background_1) ? $row->edu_background_1 : null,
                    "email" => ($row->email) ? $row->email : ($row->email_1) ? $row->email_1 : null,
                    "address" => ($row->mailing_address) ? $row->mailing_address : ($row->address_1) ? $row->address_1 : null,
                    "language" => ($row->language) ? $row->language : ($row->language_1) ? $row->language_1 : null,
                    "bank_information" => ($row->bank_information) ? $row->bank_information : ($row->bank_information_1) ? $row->bank_information_1 : null,

                    "code2" => ($row->observer_2) ? $row->observer_2 : null,
                    "name2" => ($row->name_2) ? $row->name_2 : null,
                    "father2" => ($row->father_2) ? $row->father_2 : null,
                    "mother2" => ($row->mother_2) ? $row->mother_2 : null,
                    "current_org2" => ($row->current_occupation_2) ? $row->current_occupation_2 : null,
                    "mobile2" => ($row->phone_no_1_2) ? $row->phone_no_1_2 : null,
                    "line_phone2" => ($row->phone_no_2_2) ? $row->phone_no_2_2 : null,
                    "nrc_id2" => ($row->nrc_no_2) ? $row->nrc_no_2 : null,
                    "gender2" => ($row->gender_2) ? $row->gender_2 : null,
                    "ethnicity2" => ($row->ethnicity_2) ? $row->ethnicity_2 : null,
                    "dob2" => ($row->date_of_birth_2) ? date("Y-m-d", strtotime($row->date_of_birth_2)) : null,
                    "education2" => ($row->edu_background_2) ? $row->edu_background_2 : null,
                    "email2" => ($row->email_2) ? $row->email_2 : null,
                    "address2" => ($row->mailing_address_2) ? $row->mailing_address_2 : null,
                    "language2" => ($row->language_2) ? $row->language_2 : null,
                    "bank_information2" => ($row->bank_information_2) ? $row->bank_information_2 : null,

                    "mobile_provider" => ($row->mobile_provider) ? $row->mobile_provider : null,
                    "parties" => ($row->parties) ? $row->parties : null,
                ];
                $attr = [
                    "idcode" => ($row->id_code) ? $row->id_code : null,
                    "sample" => ($row->sample) ? $row->sample : 1,
                ];
                $row_array = array_merge($type, $group, $row_array);
                $row_attr = array_merge($type, $group, $attr);

                $data = $sampleData->updateOrCreate($row_attr, $row_array);
            });
        });
        Flash::success($file . ' Data imported successfully.');
        return redirect()->back();
    }

    public function importTranslation(Request $request)
    {
        $file = $request->file('samplefile');
        $type = $request->only('type');
        $group = $request->only('dbgroup');
        $lang = \App::getLocale();
        Excel::load($file, function ($reader) use ($type, $group, $lang) {
            $reader->each(function ($row) use ($type, $group, $lang) {
                $attr = [
                    "idcode" => ($row->id_code) ? $row->id_code : null,
                    "sample" => ($row->sample) ? $row->sample : 1,
                ];
                $row_attr = array_merge($type, $group, $attr);

                $sampleData = SampleData::where('idcode', $row_attr['idcode'])
                    ->where('sample', $row_attr['sample'])
                    ->where('dbgroup', $row_attr['dbgroup'])
                    ->where('type', $row_attr['type'])->first();

                $sampleData->idcode = ($row->id_code) ? (string) $row->id_code : null;
                $sampleData->spotchecker_code = ($row->spotchecker_code) ? (string) $row->spotchecker_code : null;
                $sampleData->sample = ($row->sample) ? $row->sample : 1;
                $sampleData->state_trans = [$lang => ($row->state) ? $row->state : null];
                $sampleData->district_trans = [$lang => ($row->district) ? $row->district : null];
                $sampleData->township_trans = [$lang => ($row->township) ? $row->township : null];
                $sampleData->village_tract_trans = [$lang => ($row->village_tract) ? $row->village_tract : null];
                $sampleData->ward_trans = [$lang => ($row->ward) ? $row->ward : null];
                $sampleData->village_trans = [$lang => ($row->village) ? $row->village : null];

                $sampleData->name_trans = [$lang => ($row->name) ? $row->name : ($row->name_1) ? $row->name_1 : null];
                $sampleData->father_trans = [$lang => ($row->father) ? $row->father : ($row->father_1) ? $row->father_1 : null];
                $sampleData->mother_trans = [$lang => ($row->mother) ? $row->mother : ($row->mother_1) ? $row->mother_1 : null];
                $sampleData->gender_trans = [$lang => ($row->gender) ? $row->gender : ($row->gender_1) ? $row->gender_1 : null];
                $sampleData->nrc_id_trans = [$lang => ($row->nrc_no) ? $row->nrc_no : ($row->nrc_no_1) ? $row->nrc_no_1 : null];
                $sampleData->ethnicity_trans = [$lang => ($row->ethnicity) ? $row->ethnicity : ($row->ethnicity_1) ? $row->ethnicity_1 : null];
                $sampleData->education_trans = [$lang => ($row->edu_background) ? $row->edu_background : ($row->edu_background_1) ? $row->edu_background_1 : null];
                $sampleData->address_trans = [$lang => ($row->mailing_address) ? $row->mailing_address : ($row->mailing_address_1) ? $row->mailing_address_1 : null];
                $sampleData->language_trans = [$lang => ($row->language) ? $row->language : ($row->language_1) ? $row->language_1 : null];
                $sampleData->bank_information_trans = [$lang => ($row->bank_information) ? $row->bank_information : ($row->bank_information_2) ? $row->bank_information_2 : null];

                $sampleData->name2_trans = [$lang => ($row->name_2) ? $row->name_2 : null];
                $sampleData->father2_trans = [$lang => ($row->father_2) ? $row->father_2 : null];
                $sampleData->mother2_trans = [$lang => ($row->mother_2) ? $row->mother_2 : null];
                $sampleData->gender2_trans = [$lang => ($row->gender_2) ? $row->gender_2 : null];
                $sampleData->nrc_id2_trans = [$lang => ($row->nrc_no_2) ? $row->nrc_no_2 : null];
                $sampleData->ethnicity2_trans = [$lang => ($row->ethnicity_2) ? $row->ethnicity_2 : null];
                $sampleData->education2_trans = [$lang => ($row->edu_background_2) ? $row->edu_background_2 : null];
                $sampleData->address2_trans = [$lang => ($row->mailing_address_2) ? $row->mailing_address_2 : null];
                //$sampleData->language2_trans = [$lang => ($row->language_2) ? $row->language_2 : null];
                //$sampleData->bank_information2_trans = [$lang => ($row->bank_information_2) ? $row->bank_information_2 : null];

                $sampleData->mobile_provider_trans = [$lang => ($row->mobile_provider) ? $row->mobile_provider : null];

                $sampleData->save();
            });
        });
        Flash::success($file . ' Data imported successfully.');
        return redirect()->back();
    }

}
