<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductList extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
            )
            ->columns(components: [
                TextColumn::make("model"),
                TextColumn::make("brand"),
                TextColumn::make("part_number"),
                TextColumn::make("datasheet"),
            ]);
    }
}
