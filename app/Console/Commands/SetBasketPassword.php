<?php

namespace App\Console\Commands;

use App\Models\Basket;
use Illuminate\Console\Command;

class SetBasketPassword extends Command
{
    protected $signature = 'app:set-basket-password {slug} {password}';
    protected $description = 'Set the password for a basket';

    public function handle()
    {
        $slug = $this->argument('slug');
        $password = $this->argument('password');

        $basket = Basket::where('slug', $slug)->first();
        if (!$basket) {
            $this->error('Basket not found');
        }

        $basket->password = \Hash::make($password);
        $basket->save();

        $this->info('Password set for basket ' . $slug);
    }
}
