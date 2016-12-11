<?php

use App\Traits\QuestionsTrait;

class MyFaker
{
    use QuestionsTrait;
    public function render($faker)
    {
        return $this->to_render($faker);
    }
    /**
     * "[\r\n\t{\r\n\t\t\"type\": \"checkbox\",\r\n\t\t\"label\": \"Checkbox\",\r\n\t\t\"className\": \"checkbox\",\r\n\t\t\"name\": \"checkbox-1478449196912\"\r\n\t}\r\n]"
     */
    public function ans()
    {
        $faker = Faker\Factory::create();
        $count = $faker->randomDigitNotNull;
        $return = "\r\n";
        $tab = "\t";

        $string = "[$return$tab";

        for ($i = 1; $i <= $count; $i++) {
            $type = $faker->randomElement($array = array('checkbox', 'radio', 'text', 'number'));
            $n = $faker->words(3, true);
            $label = ucfirst($n);
            $name = str_slug($n) . '-' . $faker->randomNumber($nbDigits = 9);

            $string .= '{' . $return . $tab . $tab;
            $string .= '"type": "' . $type . '",' . $return . $tab . $tab . '"label": "' . $label . '",' . $return . $tab . $tab . '"className": "';
            $string .= '",' . $return . $tab . $tab . '"name": "' . $name . '"';
            $string .= "$return$tab}";

            if ($i != $count) {
                $string .= ',' . $return . $tab;
            }
        }

        $string .= "$return]";
        return $string;
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
        'updated_at' => $date,
    ];
});

/**
 * Factory Model for Projects
 */

$factory->define(App\Models\Project::class, function (Faker\Generator $faker) {
    $date = $faker->dateTimeThisMonth($max = 'now');
    return [
        'project' => $faker->words($nb = 3, $asText = true) . ' Project',
        'dblink' => $faker->randomElement(['voter', 'location', 'enumerator']),
        'type' => $faker->randomElement(['sample2db', 'db2sample']),
        'sections' => [
            [
                "sectionname" => "Section One ",
                "descriptions" => "Section One Test"],
            [
                "sectionname" => "Section Two",
                "descriptions" => "Section Two Test",
            ],
        ],
        'samples' => [
            [
                "name" => "Country",
                "id" => "1"],
            [
                "name" => "Region",
                "id" => "2",
            ],
        ],
        'created_at' => $date,
        'updated_at' => $date,
    ];
});

/**
 * Factory Model for Questions
 */
$factory->define(App\Models\Question::class, function (Faker\Generator $faker) use ($myfaker) {

    $raw_ans = $myfaker->ans();

    /**
     * run closure function to get project id
     */

    $layout = '';

    $unique_num = $faker->unique()->numberBetween($min = 1, $max = 30);

    $section = ($unique_num > 16) ? 1 : 0;
    $qnum = 'Q' . $unique_num;
    return [
        'qnum' => $qnum,
        'question' => $faker->realText($maxNbChars = 100, $indexSize = 5) . '?',
        'raw_ans' => $raw_ans,
        'layout' => $layout,
        'section' => $section,
        'sort' => $unique_num,
        'project_id' => function () {
            return factory(App\Models\Project::class)->create()->id;
        },

        'render' => [],
    ];
}, 'question');

/**
 * Factory Model for Voters
 */

$factory->define(App\Models\Voter::class, function (Faker\Generator $faker) {
    $date = $faker->dateTimeThisMonth($max = 'now');
    $nrc = $faker->regexify('[1-9]{1,2}');
    $nrc .= '/';
    $nrc .= $faker->regexify('[a-z]{3}');
    $nrc .= '(နိုင်)';
    $nrc .= $faker->regexify('[0-9]{6}');
    $en_my = [
        'a' => 'က', 'b' => 'စ', 'c' => 'ဋ', 'd' => 'တ', 'e' => 'ပ', 'f' => 'ရ', 'g' => 'ဟ', 'h' => 'ခ', 'i' => 'ဆ', 'j' => 'ဌ', 'k' => 'ထ', 'l' => 'ဖ', 'm' => 'ယ', 'n' => 'ဂ', 'o' => 'ဇ', 'p' => 'ဍ',
        'q' => 'ဒ', 'r' => 'ဗ', 's' => 'လ', 't' => 'သ', 'u' => 'င', 'v' => 'ည', 'w' => 'ဏ', 'x' => 'န', 'y' => 'ဘ', 'z' => 'မ', '0' => '၀', '1' => '၁', '2' => '၂', '3' => '၃', '4' => '၄', '5' => '၅', '6' => '၆', '7' => '၇', '8' => '၈', '9' => '၉',
    ];
    $nrc = strtr($nrc, $en_my);
    return [
        'name' => $faker->name,
        'dob' => $faker->date($format = 'Y-m-d H:i:s', $max = '2000-01-01'),
        'gender' => $faker->randomElement(['male', 'female', 'other']),
        'nrc_id' => $nrc,
        'father' => $faker->name('male'),
        'mother' => $faker->name('female'),
        'address' => $faker->address,
    ];
});
