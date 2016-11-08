<?php

use App\Traits\QuestionsTrait;

class MyFaker {
    use QuestionsTrait;
    public function render($faker) {
        return $this->to_render($faker);
    }
}

$myfaker = new MyFaker();
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;
    $date = $faker->dateTimeThisMonth($max = 'now');
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'created_at' => $date,
        'updated_at' => $date
    ];
});

/**
 * Factory Model for Projects
 */

$factory->define(App\Models\Project::class, function (Faker\Generator $faker) {
    $date = $faker->dateTimeThisMonth($max = 'now');
    return [
        'project' => $faker->words($nb = 3, $asText = true).' Project',
        'type' => $faker->randomElement(['p2l','l2p']),
        'sections' => [
                        [ 
                        "sectionname" => "Section One ",
                        "descriptions" => "Section One Test"],
                        [
                        "sectionname" => "Section Two",
                        "descriptions" => "Section Two Test"
                        ]
                    ],
        'dblink' => $faker->randomElement(['voterlist','location','enumerator']),
        'created_at' => $date,
        'updated_at' => $date
    ];
});


/**
 * Factory Model for Questions
 */
$factory->define(App\Models\Question::class, function (Faker\Generator $faker) use ($myfaker) {

    $raw_ans =  ans();
    
    /**
     * run closure function to get project id
     */
    $project = function () {
            return factory(App\Models\Project::class)->make()->id;
        };
    $project_id = $project();
    $qnum = 'Q'.$faker->numberBetween($min = 1, $max = 30);
    $layout = '';
    $section = $faker->numberBetween($min = 0, $max = 1);
    return [
        'qnum' => $qnum,
        'question' => $faker->realText($maxNbChars = 100, $indexSize = 5).'?',
        'raw_ans' => $raw_ans,
        'layout' => $layout,
        'section' => $section,
        'sort' => $faker->numberBetween($min = 1, $max = 30),
        'project_id' => $project_id,

        'render' => $myfaker->render([
                'raw_ans' => $raw_ans,
                'qnum' => $qnum,
                'layout' => $layout,
                'section' => $section,
                'project_id' => $project_id
            ]),
    ];
});

/**
 * "[\r\n\t{\r\n\t\t\"type\": \"checkbox\",\r\n\t\t\"label\": \"Checkbox\",\r\n\t\t\"className\": \"checkbox\",\r\n\t\t\"name\": \"checkbox-1478449196912\"\r\n\t}\r\n]"
 */

function ans() {
    $faker = Faker\Factory::create();
    $count = $faker->randomDigitNotNull;
    $return = "\r\n";
    $tab = "\t";
    
    $string = "[$return$tab";

    for($i=1;$i<=$count;$i++) {
        $type = $faker->randomElement($array = array ('checkbox','radio','text', 'number'));
        $n = $faker->words(3, true);
        $label = ucfirst($n);
        $name = str_slug($n).'-'.$faker->randomNumber($nbDigits = 9);

        $string .= '{'.$return.$tab.$tab;
        $string .= '"type": "'.$type.'",'.$return.$tab.$tab.'"label": "'.$label.'",'.$return.$tab.$tab.'"className": "'.$type;
        $string .= '",'.$return.$tab.$tab.'"name": "'.$name.'"';
        $string .= "$return$tab}";

        if($i != $count) {
            $string .= ','.$return.$tab;
        }
    }

    $string .= "$return]";
    return $string;
}