<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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

            $users = User::whereIn('patient_status', ['suspicious_admin', 'suspicious_badip'])->where('updated_at', '<', Carbon::now()->subDays(30) )->doesnthave('newBanAppeal')->get();

            if ($users->isNotEmpty()) {

                foreach ($users as $user) {
                    $action = new UserAction;
                    $action->user_id = $user->id;
                    $action->action = 'deleted';
                    $action->reason = 'Automatically - Patient with status suspicious over a month';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $user->deleteActions();
                    User::destroy( $user->id );
                }
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
