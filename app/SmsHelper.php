<?php

function str_dbcolumn($column)
{
    $column =preg_replace('/[^0-9a-zA-Z]+/', '_', strtolower(trim($column)));
    return preg_replace('/_$/','', $column);
}
