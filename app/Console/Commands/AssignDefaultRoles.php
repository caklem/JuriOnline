<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignDefaultRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign participant role to users without roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking users without roles...');

        // Get participant role
        $participantRole = Role::where('name', Role::PARTICIPANT)->first();

        if (!$participantRole) {
            $this->error('Participant role not found! Please run seeders first.');
            return 1;
        }

        // Get users without role
        $usersWithoutRole = User::whereNull('role_id')->get();

        if ($usersWithoutRole->isEmpty()) {
            $this->info('✅ All users already have roles!');
            return 0;
        }

        $this->info("Found {$usersWithoutRole->count()} users without roles.");

        $bar = $this->output->createProgressBar($usersWithoutRole->count());
        $bar->start();

        foreach ($usersWithoutRole as $user) {
            $user->role_id = $participantRole->id;
            $user->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✅ Successfully assigned 'participant' role to {$usersWithoutRole->count()} users!");

        return 0;
    }
}
