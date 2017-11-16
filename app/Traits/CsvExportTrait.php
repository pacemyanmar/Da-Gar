<?php

namespace App\Traits;
use League\Csv\Writer;

/**
 * Created by PhpStorm.
 * User: sithu
 * Date: 11/16/17
 * Time: 11:15 PM
 */

trait CsvExportTrait {

    /**
     * Export results to CSV file.
     *
     * @return void
     */
    public function csv()
    {
        $data = $this->getDataForExport();

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setEnclosure('"');

        $headers = array_keys($data[0]);

        $csv->insertOne($headers);

        $csv->insertAll($data);
        $csv->output($this->filename().'.csv');
    }
}