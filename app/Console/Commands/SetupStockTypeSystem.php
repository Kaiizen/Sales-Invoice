<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupStockTypeSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:stock-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the stock type system for different categories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Setting up stock type system...');

        // Run the migrations
        $this->info('Running migration to add stock_type to categories...');
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_04_27_000000_add_stock_type_to_categories_table.php']);
        $this->info(Artisan::output());
        
        $this->info('Running migration to create fabric rolls table...');
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_04_27_000100_create_fabric_rolls_table.php']);
        $this->info(Artisan::output());

        // Run the seeder to set up stock types for categories
        $this->info('Running seeder to set up stock types for categories...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CategoryStockTypeSeeder']);
        $this->info(Artisan::output());

        $this->info('Stock type system setup complete!');
        $this->info('Flag fabric parent category now tracks stock by square feet.');
        $this->info('All other categories track stock by quantity.');
        $this->info('Fabric rolls can now be tracked individually with dimensions and remaining square feet.');

        return 0;
    }
}