<?php

namespace App\Filament\Widgets;
use App\Filament\Imports\ProductImporter;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubCategory;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Component;
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
                       TextInput::make('productType.name'),
                       TextInput::make('_brand.name'),
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
                        ->autofocus()
                        ->required()
                        ->rules([
                            'uppercase'
                        ])
                        ->maxLength(200)
                ]),
            Step::make('Select the product brand')
                ->description('Pick one of the products brands available or insert a new one.')
                ->schema([
                    Select::make('brand')
                        ->autofocus()
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

                            Notification::make('create_brand_error')
                                ->title('Error while creating a brand')
                                ->send();
                            return null;
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

                            Notification::make('create_product_type_error')
                                ->title('Error while creating a product type')
                                ->send();
                            return null;
                        }),
                ]),
            Step::make('Select the product category')
                ->description('Choose one of the products categories available or insert a new one.')
                ->schema([
                    Select::make('category')
                        ->autofocus()
                        ->options(Category::all()->pluck('name', 'id'))
                        ->searchable()->createOptionForm([
                            TextInput::make('category')
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data){
                            if (isset($data['category'])) {
                                $new_category = Category::query()->create(['name' => $data['category']]);
                                Notification::make('create_category_ok')->title('Product category created successfully!')->success()->send();
                                return $new_category->id;
                            }

                            Notification::make('create_category_error')
                                ->title('Error while creating a product category')
                                ->send();
                            return null;
                        }),
                    Select::make('sub_category')
                        ->options(SubCategory::all()->pluck('name', 'id')) // TODO: show only sub categories with parent category equals inserted
                        ->searchable()->createOptionForm([
                            TextInput::make('sub_category')
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data, $form, Component $component){
                            $category_picked = $component->getContainer()->getParentComponent()->getChildComponents()[0]->getState(); // TODO: find a way to get state without that trick
                            if (is_null($category_picked)){
                                Notification::make('missing_category')
                                    ->title('Error while creating a product sub category. You must select first a category.')
                                    ->send();

                                return null;
                            }

                            if (isset($data['sub_category'])) {
                                $new_sub_category = SubCategory::query()->create(['name' => $data['sub_category'], "parent_category_id" =>$category_picked ]);
                                Notification::make('create_sub_category_ok')->title('Product sub-category created successfully!')->success()->send();
                                return $new_sub_category->id;
                            }

                            Notification::make('create_sub_category_error')
                                ->title('Error while creating a product sub category')
                                ->send();

                            return null;
                        }),
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
