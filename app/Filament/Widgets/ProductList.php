<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubCategory;
use App\Models\User;
use Dflydev\DotAccessData\Data;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Laravel\Prompts\Concerns\Colors;
use Mockery\Matcher\Not;

class ProductList extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = true;

    protected function getBrandOptions(): \Illuminate\Support\Collection
    {
        return Brand::all(['name'])->pluck('name', 'id');
    }

    protected function getProductTypeOptions(): \Illuminate\Support\Collection
    {
        return ProductType::all(['name'])->pluck('name', 'id');
    }

    protected function getCategoryOptions(): \Illuminate\Support\Collection
    {
        return Category::all(['name'])->pluck('name', 'id');
    }

    protected function getSubCategoryOptions(): \Illuminate\Support\Collection
    {
        return SubCategory::all(['name'])->pluck('name', 'id');
    }

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
                TextColumn::make('_brand.name'),
                TextColumn::make('part_number'),
                TextColumn::make('datasheet'),
            ])
            ->filters([
                SelectFilter::make('brand')->options($this->getBrandOptions())->searchable(),
                SelectFilter::make('product_type')->options($this->getProductTypeOptions())->searchable(),
                SelectFilter::make('category')->options($this->getCategoryOptions())->searchable(),
                SelectFilter::make('sub_category')->options($this->getSubCategoryOptions())->searchable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->steps($this->createRecordForm()),
            ])
            ->actions([
                ViewAction::make()
                    ->record($this->cachedMountedTableActionRecord),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                EditAction::make('edit')
                    ->record($this->cachedMountedTableActionRecord)
                    ->form($this->updateRecordForm())
                    ->successNotificationTitle('Product updated')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([25, 50, 150, 500])
            ->defaultPaginationPageOption(50);
    }


    public function createRecordForm(): array
    {
        return [
            Step::make('Give a model name')
                ->description('Set your model name of product you want insert it.')
                ->schema([
                    TextInput::make('model')
                        ->rules([
                            'uppercase'
                        ])
                        ->required()
                        ->maxLength(200)
                ]),
            Step::make('Select the product brand')
                ->description('Pick one of the products brands available or insert a new one.')
                ->schema([
                    Select::make('brand')
                        ->options(Brand::all()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->createOptionForm([
                            TextInput::make('brand')
                                ->ascii()
                                ->unique(table: Brand::class,column: 'name')
                                ->rules([
                                    'uppercase'
                                ])
                                ->required()
                        ])
                        ->createOptionUsing(function (array $data){
                            if (isset($data['brand'])) {
                                $new_brand = Brand::query()->create(['name' => $data['brand']]);
                                Notification::make('create_brand_ok')->title('Brand created successfully!')->success()->send();
                                return $new_brand;
                            }
                            else  Notification::make('create_brand_error')
                                ->title('Error while creating a brand')
                                ->send();
                        }),

                    Select::make('product_type')
                        ->options(ProductType::all()->pluck('name', 'id'))
//                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('product_type')
                                ->ascii()
                                ->unique(table: ProductType::class,column: 'name')
                                ->rules([
                                    'uppercase'
                                ])
                                ->required()
                        ])
                        ->createOptionUsing(function (array $data){
                            if (isset($data['product_type']))
                                return ProductType::query()->create(['name'=> $data['product_type']]);

                            return Notification::make('create_product_type_error')
                                ->title('Error while creating a product type')
                                ->send();
                        }),
                ]),
            Step::make('Select the product category')
                ->description('Choose one of the products categories available or insert a new one.')
                ->schema([
                    Select::make('category')
                        ->options(Category::all()->pluck('name', 'id'))
                        ->searchable()->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                        ]),
                    Select::make('sub_category')
                        ->options(SubCategory::all()->pluck('name', 'id'))
                        ->searchable()->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                        ]),
                ]),
        ];
    }

    public function updateRecordForm(): array
    {
        return [
            TextInput::make('model')->maxLength(200),
            Select::make('brand')
                ->options(Brand::all(['name'])->map(function ($brand) {
                    return $brand->name;
                }))->searchable(),
            Select::make('product_type')
                ->options(ProductType::all()->pluck('name', 'id'))->searchable(),
            Select::make('category')->options($this->getCategoryOptions())->searchable(),
            Select::make('sub_category')->options($this->getSubCategoryOptions())->searchable(),
            TextInput::make('datasheet'),
        ];
    }
}
