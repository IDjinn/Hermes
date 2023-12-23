<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubCategory;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
class ProductList extends BaseWidget
{
    public function table(Table $table): Table
    {

        return $table
            ->query(
                Product::query()
            )
            ->columns(components: [
                TextColumn::make('productType.name'),
                TextColumn::make('_category.name'),
                TextColumn::make('subCategory.name'),
                TextColumn::make('model')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('model', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    }),
                TextColumn::make('brand'),
                TextColumn::make('part_number'),
                TextColumn::make('datasheet'),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->options(Brand::all(['name'])->map(function ($brand) {
                        return $brand->name;
                    })),
                SelectFilter::make('product_type')
                    ->options(ProductType::all()->pluck('name','id')),
                SelectFilter::make('category')
                    ->options(Category::all()->pluck('name','id')),
                SelectFilter::make('sub_category')
                    ->options(SubCategory::all()->pluck('name','id')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([25, 50, 150, 500])
            ->defaultPaginationPageOption(50)


            ;
    }
}
