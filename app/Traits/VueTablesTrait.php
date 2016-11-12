<?php
namespace App\Traits;

use Illuminate\Http\Request;

trait VueTablesTrait
{
    
    public function VueTables(Request $request, array $fields)
    {
        extract($request->only('query', 'limit', 'page', 'orderBy', 'ascending', 'byColumn'));
        
        //\DB::connection()->enableQueryLog();

        $model = $this->model;
        /**
         * $query is extrected from Input
         * $fields is columns to select from database
         * if $byColumn is true $query will be ['column' => 'query'] array
         * if $byColumn is false, $query will be 'query' string
         */
        if (isset($query) && $query) {
            $model = $byColumn==1?$this->filterByColumn($model, $query):
                               $this->filter($model, $query, $fields);
        }
        $count = $model->count();

        $model = $model->limit($limit)
                    ->skip($limit * ($page-1));

        if (isset($orderBy) && $orderBy):
                $direction = $ascending==1?"ASC":"DESC";
                $model = $model->orderBy($orderBy, $direction);
        endif;

        $results = $model->get()->toArray();

        //$query = \DB::getQueryLog();
        //dd($query);

        //$lastQuery = end($query);

        return ['data'=>$results,
                'count'=>$count];
    }


    protected function filterByColumn($model, $query)
    {
        foreach ($query as $field => $query):

            if (!$query) {
                continue;
            }

	        if (is_string($query)) {
	            $model = $model->where($field, 'LIKE', "%{$query}%");
	        } else {
	            $start = Carbon::createFromFormat('Y-m-d', $query['start'])->startOfDay();
	            $end = Carbon::createFromFormat('Y-m-d', $query['end'])->endOfDay();

	            $model = $model->whereBetween($field, [$start, $end]);
	        }

        endforeach;

        return $model;
    }

    protected function filter($model, $query, $fields)
    {        
        foreach ($fields as $index => $field):
        	$method = $index ? "orWhere" : "where"; // very beautiful code to get first item of array because first numeric index will be "0". So if $index is false, it means $index = 0
        	$model = $model->{$method}($field, 'LIKE', "%{$query}%");
        endforeach;

        return $model;
    }
}
