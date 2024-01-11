<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubCategory;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getBrandOptions(): Collection
    {
        return Brand::all()->pluck('name', 'id');
    }

    public static function getProductTypeOptions(): Collection
    {
        return ProductType::all()->pluck('name', 'id');
    }

    public static function getCategoryOptions(): Collection
    {
        return Category::all()->pluck('name', 'id');
    }

    public static function getSubCategoryOptions(): Collection
    {
        return SubCategory::all()->pluck('name', 'id');
    }

    public static function form(Form $form): Form
    {
        $last_product = Product::query()->select('*')->orderByDesc('created_at')->first();
        return $form
            ->columns(3)
            ->schema([
                TextInput::make('model')
                    ->columnSpan(2)
                    ->maxLength(200)
                    ->required()
                    ->placeholder($last_product?->model ?? '')
                ,
                TextInput::make('id')->columnSpan(1)->disabled(),
                TextInput::make('part_number')->unique(ignoreRecord: true),
                Select::make('brand')
                    ->options(ProductResource::getBrandOptions())
                    ->searchable()
                    ->required()
                    ->default($last_product?->brand)
                    ->createOptionForm([
                        TextInput::make('brand')
                            ->ascii()
                            ->unique(table: Brand::class, column: 'name')
                            ->rules(['uppercase'])
                            ->required()
                    ])
                    ->createOptionUsing(function (array $data) {
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
                    ->options(ProductResource::getProductTypeOptions())
                    ->searchable()
                    ->required()
                    ->default($last_product?->product_type)
                    ->createOptionForm([
                        TextInput::make('product_type')
                            ->ascii()
                            ->unique(table: ProductType::class, column: 'name')
                            ->rules(['uppercase'])
                            ->required()
                    ])
                    ->createOptionUsing(function (array $data) {
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
                Select::make('category')
                    ->id('category')
                    ->options(ProductResource::getSubCategoryOptions())
                    ->searchable()
                    ->default($last_product?->category)
                    ->createOptionForm([TextInput::make('category')->required()])
                    ->createOptionUsing(function (array $data) {
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
                    ->options(ProductResource::getSubCategoryOptions())
                    ->default($last_product?->sub_category)
                    ->searchable()
                    ->createOptionForm([TextInput::make('sub_category')->required()])
                    ->createOptionUsing(function (array $data, $form, Component $component) {
                        $category_picked = null;
                        foreach ($form->getComponents() as $formComponent) {
                            if ($formComponent->getId() == 'category') {
                                $category_picked = $formComponent->getState();
                                break;
                            }
                        }

                        if (is_null($category_picked)) {
                            Notification::make('missing_category')
                                ->title('Error while creating a product sub category. You must select first a category.')
                                ->send();

                            return null;
                        }

                        if (isset($data['sub_category'])) {
                            $new_sub_category = SubCategory::query()->create(['name' => $data['sub_category'], "parent_category_id" => $category_picked]);
                            Notification::make('create_sub_category_ok')->title('Product sub-category created successfully!')->success()->send();
                            return $new_sub_category->id;
                        }

                        Notification::make('create_sub_category_error')
                            ->title('Error while creating a product sub category')
                            ->send();

                        return null;
                    }),
                TextInput::make('datasheet')->url(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
            )
            ->searchable()
            ->columns(components: [
                TextColumn::make('id'),
                TextColumn::make('model')
                    ->sortable()
                    ->copyable()
                    ->copyable()
                    ->copyMessage('Model copied')
                ,
                TextColumn::make('productType.name')->sortable(),
                TextColumn::make('_category.name')->sortable(),
                TextColumn::make('subCategory.name')->sortable(),
                TextColumn::make('_brand.name')->sortable(),
                TextColumn::make('part_number')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Part number copied')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query
                        ->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%");
                })
                ,
                TextColumn::make('datasheet')->copyable()->copyMessage('Datasheet link copied'),
            ])
            ->filters([
                SelectFilter::make('brand')->options(self::getBrandOptions())->searchable(),
                SelectFilter::make('product_type')->options(self::getProductTypeOptions())->searchable(),
                SelectFilter::make('category')->options(self::getCategoryOptions())->searchable(),
                SelectFilter::make('sub_category')->options(self::getSubCategoryOptions())->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
            ], position: ActionsPosition::BeforeColumns)
            ->persistSortInSession()
            ->defaultSort('model', 'asc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
