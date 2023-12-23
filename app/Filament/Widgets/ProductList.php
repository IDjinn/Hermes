<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductList extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
            )
            ->columns([
              Tables\Columns\TextColumn::make("name")
            ]);
    }
}
