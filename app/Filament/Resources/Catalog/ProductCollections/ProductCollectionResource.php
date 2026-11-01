<?php

namespace App\Filament\Resources\Catalog\ProductCollections;

use App\Enums\PublishStatus;
use App\Filament\Forms\Components\ImageGallery;
use App\Filament\Resources\Catalog\ProductCollections\Pages\ManageProductCollections;
use App\Filament\Tables\Columns\StackedImagesColumn;
use App\Models\Catalog\ProductCollection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductCollectionResource extends Resource
{
    protected static ?string $model = ProductCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                    ImageGallery::make(),
                Select::make('status')
                    ->options(PublishStatus::class)
                    ->default(PublishStatus::Draft)
                    ->disabled()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->withImages()
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
                    ->searchable(),
                StackedImagesColumn::make(),
                TextColumn::make('status')
                    ->badge()

                    ->sortable(),
                TextColumn::make('products_count')

                    ->counts('products') // Matches your relationship name on the ProductCollection model
                    ->sortable()
                    ->label('Products Count'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublishStatus::class)
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                EditAction::make()->visible(fn(ProductCollection $record) => $record->status == PublishStatus::Draft),
                Action::make('viewProducts')
                    ->tooltip('Products')
                    ->iconButton()
                    ->label('Products')
                    ->icon('heroicon-m-shopping-bag')
                    ->color('info')
                    ->modalHeading(fn(ProductCollection $record) => "Products in: {$record->name}")
                    ->modalContent(fn(ProductCollection $record) => view(
                        'filament.modals.collection-products',
                        ['products' => $record->products()->with('productVariants')->withCount('productVariants')->get()]
                    ))
                    ->modalSubmitAction(false) // Hides the submit button since this is just for viewing
                    ->modalCancelActionLabel('Close'),


                // ActionGroup::make([

                Action::make("publish")
                    ->tooltip('Publish')
                    ->iconButton()
                    ->visible(fn(ProductCollection $record): bool => in_array($record->status, [PublishStatus::Draft]))
                    ->icon(Heroicon::PaperAirplane)
                    // 1. Add a form to the action to pop up in the modal
                    ->schema([
                        DateTimePicker::make('publish_at')
                            ->label('Publish At')
                            ->required()
                            ->default(now())
                            ->suffixAction(
                                Action::make('setNow')
                                    ->icon('heroicon-m-clock')
                                    ->tooltip('Set to Now')
                                    ->action(fn(Set $set) => $set('publish_at', now()))
                            ),
                    ])
                    // 2. Capture $data in the action closure to access the chosen date
                    ->action(function (ProductCollection $record, array $data) {
                        // Assign the selected date from the modal form data

                        // Call your publish logic and save
                        $record->publish($data['publish_at']);
                        $record->save();

                        Notification::make()
                            ->success()
                            ->title('Collection Published')
                            ->body('The collection has been scheduled/published successfully.')
                            ->send();
                    }),


                Action::make("archive")
                    ->visible(fn(ProductCollection $record): bool => in_array($record->status, [PublishStatus::Published]))
                    ->icon(Heroicon::ArchiveBox)
                    ->iconButton() // Renders it as a clean icon-only button
                    ->tooltip('Archive')
                    ->requiresConfirmation()
                    ->action(function (ProductCollection $record) {
                        $record->archive();
                        $record->save();
                    }),
                Action::make("unarchive")
                    ->tooltip("Unarchive")
                    ->iconButton()
                    ->visible(fn(ProductCollection $record): bool => in_array($record->status, [PublishStatus::Archived]))
                    ->icon(Heroicon::OutlinedArchiveBoxXMark)
                    ->requiresConfirmation()
                    ->action(function (ProductCollection $record) {
                        $record->unarchive();
                        $record->save();
                    }),

                // ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductCollections::route('/'),
        ];
    }
}
