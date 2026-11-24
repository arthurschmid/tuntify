<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateFirstAdmin extends Command
{
    protected $signature = 'tuntify:first-admin';

    protected $description = 'Activate the first administrator account';

    public function handle(): int
    {
        $shortname = $this->ask('Shortname');
        $email = User::normalizeLoginEmail((string) $this->ask('Login-E-Mail'));
        $password = $this->secret('Passwort', false);
        $confirmation = $this->secret('Passwort wiederholen', false);

        $validator = Validator::make([
            'shortname' => $shortname,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'shortname' => ['required', 'regex:/\A[A-Za-z0-9_-]{1,32}\z/'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:72', Password::min(12), 'confirmed'],
        ]);

        if ($validator->fails() || strlen((string) $password) > 72) {
            $this->error('Bitte prüfe die Eingaben. Das Passwort muss mindestens 12 Zeichen und höchstens 72 Bytes haben.');

            return self::FAILURE;
        }

        $created = DB::transaction(function () use ($shortname, $email, $password): bool {
            $role = DB::table('roles')->where('code', 'admin')->lockForUpdate()->sole();
            if (DB::table('user_roles')->where('role_id', $role->id)->exists()) {
                return false;
            }

            $user = User::firstOrNew(['shortname' => $shortname]);
            if ($user->login_email !== null || $user->disabled_at !== null || User::where('login_email', $email)->exists()) {
                return false;
            }

            $user->forceFill(['login_email' => $email, 'password_hash' => $password])->save();
            DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $role->id]);

            return true;
        });

        if (! $created) {
            $this->error('Die Adminrolle ist bereits vergeben oder die Anmeldedaten kollidieren mit einem bestehenden Konto.');

            return self::FAILURE;
        }

        $this->info('Admin-Konto aktiviert.');

        return self::SUCCESS;
    }
}
