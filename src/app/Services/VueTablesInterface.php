<?php
/**
 *  VueTables server-side component interface
 */

namespace App\Services;

Interface VueTablesInterface {

  public function get($table, Array $fields);

}
