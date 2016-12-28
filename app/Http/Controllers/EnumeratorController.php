<?php

namespace App\Http\Controllers;

use App\DataTables\EnumeratorDataTable;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateEnumeratorRequest;
use App\Http\Requests\UpdateEnumeratorRequest;
use App\Models\Enumerator;
use App\Models\Location;
use App\Repositories\EnumeratorRepository;
use Flash;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Response;

class EnumeratorController extends AppBaseController
{
    /** @var  EnumeratorRepository */
    private $enumeratorRepository;

    public function __construct(EnumeratorRepository $enumeratorRepo)
    {
        $this->enumeratorRepository = $enumeratorRepo;
    }

    /**
     * Display a listing of the Enumerator.
     *
     * @param EnumeratorDataTable $enumeratorDataTable
     * @return Response
     */
    public function index(EnumeratorDataTable $enumeratorDataTable)
    {
        return $enumeratorDataTable->render('enumerators.index');
    }

    /**
     * Show the form for creating a new Enumerator.
     *
     * @return Response
     */
    public function create()
    {
        return view('enumerators.create');
    }

    /**
     * Store a newly created Enumerator in storage.
     *
     * @param CreateEnumeratorRequest $request
     *
     * @return Response
     */
    public function store(CreateEnumeratorRequest $request)
    {
        $input = $request->all();

        $enumerator = $this->enumeratorRepository->create($input);

        Flash::success('Enumerator saved successfully.');

        return redirect(route('enumerators.index'));
    }

    /**
     * Display the specified Enumerator.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        return view('enumerators.show')->with('enumerator', $enumerator);
    }

    /**
     * Show the form for editing the specified Enumerator.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        return view('enumerators.edit')->with('enumerator', $enumerator);
    }

    /**
     * Update the specified Enumerator in storage.
     *
     * @param  int              $id
     * @param UpdateEnumeratorRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateEnumeratorRequest $request)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        $enumerator = $this->enumeratorRepository->update($request->all(), $id);

        Flash::success('Enumerator updated successfully.');

        return redirect(route('enumerators.index'));
    }

    /**
     * Remove the specified Enumerator from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $enumerator = $this->enumeratorRepository->findWithoutFail($id);

        if (empty($enumerator)) {
            Flash::error('Enumerator not found');

            return redirect(route('enumerators.index'));
        }

        $this->enumeratorRepository->delete($id);

        Flash::success('Enumerator deleted successfully.');

        return redirect(route('enumerators.index'));
    }

    public function import(Request $request)
    {
        $file = $request->file('samplefile');
        Excel::load($file, function ($reader) {

            $reader->each(function ($row) {
                $location = new Location();
                if ($row->state) {
                    $state_data['idcode'] = $row->location_id;
                    $state_data['type'] = $state_attr['type'] = 'state';
                    $state_data['name'] = $state_attr['type'] = $row->state;
                    $state_data['lat_long'] = '';
                    $state = $location->updateOrCreate($state_attr, $state_data);
                }
                if ($row->district) {
                    $district_data['idcode'] = $row->location_id;
                    $district_data['type'] = $district_attr['type'] = 'district';
                    $district_data['name'] = $district_attr['name'] = $row->district;
                    $district_data['lat_long'] = '';
                    $district = $location->updateOrCreate($district_attr, $district_data);
                }
                if ($row->township) {
                    $downship_data['idcode'] = $row->location_id;
                    $township_data['type'] = $township_attr['type'] = 'township';
                    $township_data['name'] = $township_attr['name'] = $row->township;
                    $township_data['lat_long'] = '';
                    $township = $location->updateOrCreate($township_attr, $township_data);
                }
                if ($row->village_tract) {
                    $village_tract_data['idcode'] = $row->location_id;
                    $village_tract_data['type'] = $village_tract_attr['type'] = 'village_tract';
                    $village_tract_data['name'] = $village_tract_attr['name'] = $row->village_tract;
                    $village_tract_data['lat_long'] = '';
                    $village_tract = $location->updateOrCreate($village_tract_attr, $village_tract_data);
                }
                if ($row->village) {
                    $village_data['idcode'] = $row->location_id;
                    $village_data['type'] = $village_attr['type'] = 'village';
                    $village_data['name'] = $village_attr['name'] = $row->village;
                    $village_data['lat_long'] = '';
                    $village = $location->updateOrCreate($village_attr, $village_data);
                }
                $enumerator = new Enumerator();
                $person['idcode'] = $row->location_id;
                $person['name'] = $row->name;
                $person['nrc_id'] = $row->nrc_id;
                $person['gender'] = $row->gender;
                $person['dob'] = (!empty($row->dob)) ? date("Y-m-d", strtotime($row->dob)) : null;
                $person['address'] = $row->mailing_address;
                $person['village'] = $village->id;
                $person['village_tract'] = $village_tract->id;
                $person['township'] = $township->id;
                $person['district'] = $district->id;
                $person['state'] = $state->id;
                $enumerator->updateOrCreate($person, $person);
            });
        });
        Flash::success($file . ' Data imported successfully.');
        return redirect()->back();
    }
}
