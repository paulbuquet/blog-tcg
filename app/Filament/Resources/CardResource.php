<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Models\Card;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static string|UnitEnum|null $navigationGroup = 'Pipeline';

    protected static ?string $modelLabel = 'carte';

    protected static ?string $pluralModelLabel = 'Cartes';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('')
                    ->width(40)
                    ->height(56)
                    ->circular(false),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('set_name')
                    ->label('Set')
                    ->searchable(),
                TextColumn::make('game.name')
                    ->label('Jeu')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('external_id')
                    ->label('ID externe'),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Jeu')
                    ->relationship('game', 'name'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
