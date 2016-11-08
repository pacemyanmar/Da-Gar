<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$questions = factory(App\Models\Question::class, 30)->make();
        $project = factory(App\Models\Project::class)->create();
        $project->questions()->saveMany($questions);
    }
}
