<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateOwnerAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firm:create-owner {name} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creează un cont nou de proprietar pentru o firmă';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                $ownerName = $this->argument('name');
                $ownerEmail = $this->argument('email');
                $ownerPassword = $this->argument('password');

                // Pentru simplitate, numele firmei este derivat din numele proprietarului
                // Poate fi modificat ulterior din interfața web, din "Profilul firmei"
                $companyName = $ownerName . ' SRL';

                // 1. Creăm firma
                $company = Company::create(['name' => $companyName]);
                $this->info("Firma '{$companyName}' a fost creată.");

                // 2. Creăm utilizatorul "Owner"
                $owner = User::create([
                    'company_id' => $company->id,
                    'name' => $ownerName,
                    'email' => $ownerEmail,
                    'password' => Hash::make($ownerPassword),
                ]);
                $this->info("Utilizatorul '{$ownerName}' a fost creat.");

                // 3. Creăm rolurile dacă nu există
                $roleOwner = Role::firstOrCreate(['name' => 'Owner']);
                Role::firstOrCreate(['name' => 'Administrator']);
                Role::firstOrCreate(['name' => 'Inginer']);
                Role::firstOrCreate(['name' => 'Tehnician']);
                Role::firstOrCreate(['name' => 'Asistent']);

                // 4. Atribuim rolul de "Owner"
                $owner->assignRole($roleOwner);
                $this->info("Rolul 'Owner' a fost atribuit.");

                // 5. Creăm setările implicite
                $company->companyProfile()->create(['company_name' => $companyName]);
                $company->offerSetting()->create();
                $company->templateSetting()->create();

                $this->info('Configurările implicite au fost create.');
                $this->comment("Proprietarul '{$ownerName}' a fost creat cu succes!");
            });
        } catch (\Exception $e) {
            $this->error('A apărut o eroare: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}