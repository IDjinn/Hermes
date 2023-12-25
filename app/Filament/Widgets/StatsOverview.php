<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
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

    function shrinkNumber($number): string
    {
        $suffixes = array('x','k', 'M', 'B', 'T');

        foreach ($suffixes as $suffix) {
            if ($number < 1000) {
                break;
            }
            $number /= 1000;
        }

        return round($number, 1) . $suffix;
    }

    private function getTotalProductsStats(): Stat
    {
        $total_products_previous_month = Product::query()->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth(), Carbon::now()->startOfMonth()->subMonth()->endOfMonth()])->count();
        $total_products_last_month = Product::query()->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth(), Carbon::now()->endOfMonth()])->count();

        $total_increase = $total_products_last_month - $total_products_previous_month;
        $stat = Stat::make('Total of Products', $this->shrinkNumber(Product::all()->count()));
        if ($total_increase == 0){
            return $stat->description('None product created this month.')->color('gray');
        }
        else if ($total_increase < 0){
            return $stat->description($this->shrinkNumber($total_increase) . ' decrease this month')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger');
        }
        else{
            return $stat->description($this->shrinkNumber($total_increase) . ' increase this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success');
        }
    }

    private function getTotalQueries(): Stat
    {
        return Stat::make('Total of Queries', '55k')
            ->description('8k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');
    }
}
