<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected function getStats(): array
    {
        return [
            $this->getTotalProductsStats(),
            $this->getTotalQueries(),
        ];
    }


    private function getTotalProductsStats(): Stat
    {
        return Stat::make('Total of Products', '10k')
            ->description('2k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');
    }

    private function getTotalQueries(): Stat
    {
        return Stat::make('Total of Queries', '55k')
            ->description('8k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');
    }
}
