<?php

namespace App\Filament\Widgets;

use App\Models\PropertyTypes;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductPropertyTypesWidgetView extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PropertyTypes::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('value'),
            ]);
    }
}
