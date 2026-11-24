<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new LogicException('DemoSeeder requires a local or testing application environment.');
        }

        DB::transaction(function () {
            $user = User::firstOrCreate(['shortname' => 'demo-att-001']);
            $series = DB::table('event_series')->where('code', 'att')->value('id');

            DB::table('events')->insertOrIgnore([
                'shortname' => 'demo-att-event',
                'event_series_id' => $series,
                'starts_at' => '2024-10-05 14:00:00',
                'starts_timezone' => 'Europe/Berlin',
            ]);

            $event = DB::table('events')->where('shortname', 'demo-att-event')->value('id');

            DB::table('user_participation')->insertOrIgnore([
                'user_id' => $user->id,
                'event_id' => $event,
                'participate' => true,
            ]);
        });
    }
}
