<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  // Requirement: seed SuperAdmin with raw SQL. Change credentials before deployment.
  $now=now()->toDateTimeString();
  DB::statement('INSERT INTO users (name, email, email_verified_at, password, role, company_id, remember_token, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',[
   'Super Admin','superadmin@sembark.com',$now,Hash::make('password123'),'super_admin',null,Str::random(10),$now,$now
  ]);
 }
}
