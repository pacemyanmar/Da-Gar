<?php

function str_dbcolumn($column)
{
    return preg_replace('/[^0-9a-zA-Z]+/', '_', strtolower(trim($column)));
}
