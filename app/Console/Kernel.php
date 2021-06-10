<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\SupportCategoryTranslation;
use App\Models\SupportQuestionTranslation;
use App\Models\SupportCategory;
use App\Models\SupportQuestion;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {

        $schedule->call(function () {
            echo 'Get Questions - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.dentacoin.com/api/get-suppor-info/',
                CURLOPT_SSL_VERIFYPEER => 0
            ));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);

            if(!empty($resp) && isset($resp->success) && $resp->success) {

                $categories = $resp->data->categories;

                SupportCategory::truncate();
                SupportCategoryTranslation::truncate();

                foreach($categories as $category) {
                    $new_cat = new SupportCategory;
                    $new_cat->name = '';
                    $new_cat->save();

                    foreach (config('langs') as $key => $value) {
                        $translation = $new_cat->translateOrNew($key);
                        $translation->support_category_id = $new_cat->id;
                        $translation->name = $category->name;
                        $translation->save();
                    }
                }

                $questions = $resp->data->all_questions;

                SupportQuestion::truncate();
                SupportQuestionTranslation::truncate();

                foreach($questions as $question) {
                    $new_q = new SupportQuestion;
                    $new_q->category_id = $question->category_id;
                    $new_q->is_main = $question->is_main;
                    $new_q->save();

                    foreach (config('langs') as $key => $value) {
                        $translation = $new_q->translateOrNew($key);
                        $translation->support_question_id = $new_q->id;
                        $translation->slug = $question->slug;
                        $translation->question = $question->question;
                        $translation->content = $question->content;
                        $translation->save();
                    }
                }

            } else {
                echo 'Can\'t connect api!'.PHP_EOL.PHP_EOL.PHP_EOL;
            }

            echo 'Get Questions - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron('30 7 * * 0');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
