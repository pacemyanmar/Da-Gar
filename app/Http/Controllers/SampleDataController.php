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
                //dd($row);
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

                $row_array = [
                    "idcode" => ($row->id_code) ? (string) $row->id_code : null,
                    "sample" => ($row->sample) ? $row->sample : 1,
                    "state" => ($row->state) ? $row->state : null,
                    "district" => ($row->district) ? $row->district : null,
                    "township" => ($row->township) ? $row->township : null,
                    "village_tract" => ($row->village_tract) ? $row->village_tract : null,
                    "village" => ($row->village) ? $row->village : null,
                    "name" => ($row->name) ? $row->name : null,
                    "current_org" => ($row->current_occupation) ? $row->current_occupation : null,
                    "mobile" => ($row->phone_no_1) ? $row->phone_no_1 : null,
                    "line_phone" => ($row->phone_no_2) ? $row->phone_no_2 : null,
                    "nrc_id" => ($row->nrc_no) ? $row->nrc_no : null,
                    "gender" => ($row->gender) ? $row->gender : null,
                    "ethnicity" => ($row->ethnicity) ? $row->ethnicity : null,
                    "dob" => ($row->date_of_birth) ? date("Y-m-d", strtotime($row->date_of_birth)) : null,
                    "education" => ($row->edu_background) ? $row->edu_background : null,
                    "email" => ($row->email) ? $row->email : null,
                    "address" => ($row->mailing_address) ? $row->mailing_address : null,
                    "language" => ($row->language) ? $row->language : null,
                    "bank_information" => ($row->bank_information) ? $row->bank_information : null,
                    "mobile_provider" => ($row->mobile_provider) ? $row->mobile_provider : null,
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
}
