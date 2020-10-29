<?php
namespace App\Traits;

use App\Models\Phone;
use App\Models\SampleData;
use Illuminate\Support\Facades\Log;

trait SampleImportTrait
{
private function massInsert($project,$records)
{
    $data_array = iterator_to_array($records,true);

    $phones = Phone::all();

    $phone_mass_insert = [];

    array_walk($data_array, function(&$data, $key) use ($project, $phones, &$phone_mass_insert) {
        $newdata = [];


        foreach($data as $dk => $dv) {
            $data_column = str_dbcolumn($dk);
            if($data_column == $project->idcolumn) {
                $newdata['id'] = filter_var($dv, FILTER_SANITIZE_STRING);
            } else {
                $newdata[$data_column] = filter_var($dv, FILTER_SANITIZE_STRING);
            }
        }

        foreach($newdata as $dk => $dv) {
            $phone_column = $project->locationMetas->where('field_name', $dk)->where('field_type', 'phone')->first();

            if($phone_column) {
                Log::debug($newdata);
                $observer_number = null;
                $sbo_number_col = $project->locationMetas->where('field_type', 'sbo_number')->first();
                if($sbo_number_col)
                    $observer_number = (array_key_exists($sbo_number_col->field_name, $newdata))?$newdata[$sbo_number_col->field_name]:null;

                $guessed_observer_number = (is_numeric(substr($phone_column->data_type, -1)))?substr($phone_column->data_type, -1):1;
                Log::debug($sbo_number_col);
                Log::debug($observer_number);

                $sms_code_col = $project->locationMetas->where('field_type', 'code')->first();
                $sms_code = $newdata['id'];
                if($sms_code_col)
                    $sms_code = (array_key_exists($sms_code_col->field_name, $newdata))?$newdata[$sms_code_col->field_name]:$newdata['id'];

                $phone_number = preg_replace('/[^0-9]/','',$newdata[$dk]);
                if($phone_number) {
                    if($phone = $phones->find($phone_number)) {

                        if ($guessed_observer_number != $phone->observer || $sms_code != $phone->sample_code) {
                            Log::debug($phone->phone . ',' . $guessed_observer_number .','.$observer_number. ',' . $phone->observer . ',' . $sms_code . ',' . $phone->sample_code);

                            $phone->observer = ($observer_number)??$guessed_observer_number;
                            $phone->sample_code = $sms_code;
                            $phone->save();
                        }
                    } else {
                        $phone_mass_insert[$phone_number] = [
                            'phone' => $phone_number,
                            'sample_code' => $sms_code,
                            'observer' => ($observer_number)??$guessed_observer_number
                        ];
                    }
                }
            }
        }
        $data = $newdata;
    });

    if(!empty($phone_mass_insert))
        Phone::insert(array_values($phone_mass_insert));

    $sample_data = new SampleData();

    $sample_data->insertOrUpdate($data_array, $project->dbname.'_samples');
}
}
