<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('shortname', 32)->charset('ascii')->collation('ascii_general_ci')->unique();
            $table->string('preferred_locale', 5)->nullable();
            $table->string('postal_locale', 5)->nullable();
            $table->string('salutation')->nullable();
            $table->string('alias_given_name')->nullable();
            $table->string('alias_family_name')->nullable();
            $table->string('login_email')->nullable()->unique();
            $table->string('password_hash')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_shortname_format CHECK (shortname NOT REGEXP '[^A-Za-z0-9_-]' AND CHAR_LENGTH(shortname) > 0)");
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_login_credentials_pair CHECK ((login_email IS NULL) = (password_hash IS NULL))');

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('user_contacts', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->restrictOnDelete();
            $table->string('legal_given_name')->nullable();
            $table->string('legal_family_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->date('contact_verified_on')->nullable();
            $table->string('line_1')->nullable();
            $table->string('line_2')->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->string('city')->nullable();
            $table->string('additional_line')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->boolean('allow_alias_for_post')->nullable();
        });

        DB::statement('ALTER TABLE user_contacts ADD CONSTRAINT user_contacts_post_bool CHECK (allow_alias_for_post IN (0, 1))');

        DB::table('roles')->insert(['code' => 'admin']);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_contacts');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
