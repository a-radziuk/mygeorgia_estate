<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class DeleteMockListings extends Command
{
    protected $signature = 'listings:delete-mock
                            {--force : Delete without confirmation}';

    protected $description = 'Delete all listings where is_mock is true.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Delete all listings where is_mock is true?')) {
            return self::FAILURE;
        }

        $count = Listing::query()->where('is_mock', true)->delete();

        $this->info("Deleted {$count} listing(s).");

        return self::SUCCESS;
    }
}
