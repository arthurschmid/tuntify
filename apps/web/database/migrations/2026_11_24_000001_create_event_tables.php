<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_series', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name')->nullable();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('shortname', 32)->charset('ascii')->collation('ascii_general_ci')->unique();
            $table->foreignId('event_series_id')->nullable()->constrained('event_series')->restrictOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            $table->decimal('location_latitude', 9, 6)->nullable();
            $table->decimal('location_longitude', 9, 6)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->string('starts_timezone', 64)->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('ends_timezone', 64)->nullable();
            $table->index(['event_series_id', 'starts_at', 'id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE events
                ADD CONSTRAINT events_shortname_format CHECK (shortname NOT REGEXP '[^A-Za-z0-9_-]' AND CHAR_LENGTH(shortname) > 0),
                ADD CONSTRAINT events_coordinates_pair CHECK ((location_latitude IS NULL) = (location_longitude IS NULL)),
                ADD CONSTRAINT events_latitude_range CHECK (location_latitude BETWEEN -90 AND 90),
                ADD CONSTRAINT events_longitude_range CHECK (location_longitude BETWEEN -180 AND 180),
                ADD CONSTRAINT events_starts_timezone_pair CHECK ((starts_at IS NULL) = (starts_timezone IS NULL)),
                ADD CONSTRAINT events_ends_timezone_pair CHECK ((ends_at IS NULL) = (ends_timezone IS NULL)),
                ADD CONSTRAINT events_date_order CHECK (ends_at >= starts_at)
            SQL);

        Schema::create('user_participation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->date('invite')->nullable()->default(null);
            $table->date('rsvp')->nullable()->default(null);
            $table->boolean('participate')->nullable()->default(null);
            $table->unique(['user_id', 'event_id']);
            $table->index(['event_id', 'invite', 'user_id']);
            $table->index(['user_id', 'participate', 'event_id']);
        });

        DB::statement('ALTER TABLE user_participation ADD CONSTRAINT user_participation_bool CHECK (participate IN (0, 1))');

        DB::table('event_series')->insert(['code' => 'att', 'name' => 'Alttuntentreffen']);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_participation');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_series');
    }
};
