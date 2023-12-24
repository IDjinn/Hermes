<?php

namespace App\Filament\Widgets;
use App\Filament\Imports\ProductImporter;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubCategory;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\SelectFilter;

class ProductList extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = true;

    protected function getBrandOptions(): Collection
    {
        return Brand::all(['name'])->pluck('name', 'id');
    }

    protected function getProductTypeOptions(): Collection
    {
        return ProductType::all(['name'])->pluck('name', 'id');
    }

    protected function getCategoryOptions(): Collection
    {
        return Category::all(['name'])->pluck('name', 'id');
    }

    protected function getSubCategoryOptions(): Collection
    {
        return SubCategory::all(['name'])->pluck('name', 'id');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
            )->searchable()
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
                SelectFilter::make('sub_category')->options($this->getSubCategoryOptions())->searchable()
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ProductImporter::class),
                CreateAction::make()
                    ->steps($this->createRecordForm()),
            ])
            ->actions([

               Tables\Actions\ActionGroup::make([ ViewAction::make()
                   ->form([
                       TextInput::make('model')->maxLength(200),
                       TextInput::make('brand'),
                       TextInput::make('datasheet'),
                       TextInput::make('product_type'),
                       TextInput::make('category'),
                       TextInput::make('sub_category'),
                   ]),
                   Tables\Actions\DeleteAction::make(),
                   EditAction::make('edit')
                       ->record($this->cachedMountedTableActionRecord)
                       ->form($this->updateRecordForm())
                       ->successNotificationTitle('Product updated')])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
                        ->preload()
                        ->searchable()
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
                                return $new_brand->id;
                            }
                            else  Notification::make('create_brand_error')
                                ->title('Error while creating a brand')
                                ->send();
                        }),

                    Select::make('product_type')
                        ->options(ProductType::all()->pluck('name', 'id'))
                        ->searchable()
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
                            if (isset($data['product_type'])) {
                                $new_product_type = ProductType::query()->create(['name' => $data['product_type']]);
                                Notification::make('create_product_type_ok')->title('Product type created successfully!')->success()->send();
                                return $new_product_type->id;
                            }

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
