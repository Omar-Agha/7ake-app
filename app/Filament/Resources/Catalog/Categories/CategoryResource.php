<?php

namespace App\Filament\Resources\Catalog\Categories;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Resources\Catalog\Categories\Pages\ManageCategories;
use App\Models\Catalog\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([...static::formFields()]);
    }

    public static function formFields(): array
    {
        return [
            TranslatableTabs::make('')->schema([
                TextInput::make('name')
                    ->live(onBlur: true) // Makes it reactive (updates on blur or instantly if you remove onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->required(),

                Textarea::make('description')
                    ->columnSpanFull(),
            ])->columnSpanFull(),
            TextInput::make('slug')
                ->required()
                ->readOnly()
                ->maxLength(255)
                ->unique(ignoreRecord: true),


        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable()
                    ->bilingual(),
                TextColumn::make('slug')
                    ->searchable(),

                TextColumn::make('description')
                    ->searchable()
                    ->bilingual()
                    ->words(8),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Category $record) {
                        // Check if products exist before letting the modal proceed
                        if ($record->products()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Deletion Blocked')
                                ->body('This category cannot be deleted because it has active products.')
                                ->send();

                            // Halts the deletion process
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
