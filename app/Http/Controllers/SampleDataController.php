<?php

namespace App\Http\Controllers;

use App\DataTables\SampleDataDataTable;
use App\Http\Requests\CreateSampleDataRequest;
use App\Http\Requests\UpdateSampleDataRequest;
use App\Models\Observer;
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
     * @param  int $id
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



                $row_array = [
                    "location_code" => ($row->location_code) ? (string)$row->location_code : null,
                    "ps_code" => ($row->ps_code) ? (string)$row->ps_code : null, // official polling station number

                    "sample" => ($row->sample) ? $row->sample : 1,
                    "area_type" => strtolower($row->area_type), // rural or urban

                    "level1" => ($row->level1) ? $row->level1 : null, // state or province
                    "level2" => ($row->level2) ? $row->level2 : null, // district
                    "level3" => ($row->level3) ? $row->level3 : null, // township
                    "level4" => ($row->level4) ? $row->level4 : null, // village tract, ward, or commune

                    "level5" => ($row->level5) ? $row->level5 : null, // village
                    "level6" => ($row->level6) ? $row->level6 : null,

                    "level1_trans" => ($row->level1_trans) ? $row->level1_trans : null, // state or province
                    "level2_trans" => ($row->level2_trans) ? $row->level2_trans : null, // district
                    "level3_trans" => ($row->level3_trans) ? $row->level3_trans : null, // township
                    "level4_trans" => ($row->level4_trans) ? $row->level4_trans : null, // village tract, ward, or commune

                    "level5_trans" => ($row->level5_trans) ? $row->level5_trans : null, // village
                    "level6_trans" => ($row->level6_trans) ? $row->level6_trans : null,

                    "parties" => ($row->parties) ? $row->parties : null,

                    "observer_field" => ($row->observer_field) ? $row->observer_field : null,

                    "supervisor_field" => ($row->supervisor_field) ? $row->supervisor_field : null,
                    "supervisor_name" => ($row->supervisor_name) ? $row->supervisor_title.' '.$row->supervisor_name : null,
                    "supervisor_name_trans" => ($row->supervisor_name_trans) ? $row->supervisor_name_trans : null,
                    "supervisor_gender" => ($row->supervisor_gender) ? $row->supervisor_gender : null,
                    "supervisor_dob" => ($row->supervisor_dob) ? date("Y-m-d", strtotime($row->supervisor_dob)) : null,
                    "supervisor_mobile" => ($row->supervisor_mobile) ? $row->supervisor_mobile : null,
                    "supervisor_email1" => ($row->supervisor_email1) ? $row->supervisor_email1 : null,
                    "supervisor_email2" => ($row->supervisor_email2) ? $row->supervisor_email2 : null,
                    "supervisor_address" => ($row->supervisor_address) ? $row->supervisor_address : null,
                ];
                $attr = [
                    "location_code" => ($row->location_code) ? $row->location_code : null,
                    "sample" => ($row->sample) ? $row->sample : 1,
                ];
                $row_array = array_merge($type, $group, $row_array);
                $row_attr = array_merge($type, $group, $attr);

                if ($row->location_code) {
                    $location_data = SampleData::updateOrCreate($row_attr, $row_array);

                    $given_name = ($row->given_name) ? $row->given_name : null;
                    $family_name = ($row->family_name) ? $row->family_name : null;
                    $full_name = ($row->full_name) ? $row->full_name : $given_name . ' ' . $family_name;

                    $observer_arr = [
                        "code" => ($row->observer_code) ? $row->observer_code : null,
                        "observer_field" => ($row->observer_field) ? $row->observer_field : null,

                        'given_name' => $given_name,
                        'family_name' => $family_name,
                        "full_name" => (!empty($full_name)) ? $full_name : 'No Name',
                        "full_name_trans" => ($row->full_name_trans) ? $row->full_name_trans : null,
                        "father" => ($row->father) ? $row->father : null,
                        "mother" => ($row->mother) ? $row->mother : null,
                        "occupation" => ($row->current_occupation) ? $row->current_occupation : null,
                        "phone_1" => ($row->phone_1) ? $row->phone_1 : null,
                        "phone_2" => ($row->phone_2) ? $row->phone_2 : null,
                        "national_id" => ($row->national_id) ? $row->national_id : null,
                        "gender" => ($row->gender_) ? $row->gender_ : null,
                        "ethnicity" => ($row->ethnicity) ? $row->ethnicity : null,
                        "dob" => ($row->date_of_birth) ? date("Y-m-d", strtotime($row->date_of_birth)) : null,
                        "education" => ($row->edu_background_) ? $row->edu_background_ : null,
                        "email1" => ($row->email1) ? $row->email1 : null,
                        "email2" => ($row->email2) ? $row->email2 : null,
                        "address" => ($row->mailing_address) ? $row->mailing_address : null,
                        "language" => ($row->language) ? $row->language : null,
                        "bank_information" => ($row->bank_information) ? $row->bank_information : null,
                        "mobile_provider" => ($row->mobile_provider) ? $row->mobile_provider : null,
                        "sms_primary" => ($row->sms_primary) ? $row->sms_primary : null,
                        "sms_backup" => ($row->sms_backup) ? $row->sms_backup : null,
                        "call_primary" => ($row->call_primary) ? $row->call_primary : null,
                        "call_backup" => ($row->call_backup) ? $row->call_backup : null,
                        "hotline1" => ($row->hotline_1) ? $row->hotline_1 : null,
                        "hotline2" => ($row->hotline_2) ? $row->hotline_2 : null,
                    ];
                    $observer_attr = [
                        'sample_id' => $location_data->id,
                        'code' => ($row->observer_code) ? $row->observer_code : null,
                    ];
                    $observer = Observer::updateOrCreate($observer_attr, $observer_arr);
                }
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

                $sampleData->idcode = ($row->id_code) ? (string)$row->id_code : null;
                $sampleData->spotchecker_code = ($row->spotchecker_code) ? (string)$row->spotchecker_code : null;
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
