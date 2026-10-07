<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Models\Game;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Pipeline';

    protected static ?string $modelLabel = 'jeu';

    protected static ?string $pluralModelLabel = 'Jeux';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('api_identifier')
                    ->label('Identifiant API (tcgapi.dev)')
                    ->required()
                    ->helperText('Ex. : pokemon, magic, yugioh'),
                FileUpload::make('banner_path')
                    ->label('Bannière de la page jeu')
                    ->disk('public')
                    ->image()
                    ->imageEditor()
                    ->directory('games')
                    ->maxSize(2048)
                    ->helperText('Format idéal : 1600x400, affichée en haut de la page du jeu.'),
                Toggle::make('is_active')
                    ->label('Actif (jobs quotidiens)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug'),
                TextColumn::make('api_identifier')
                    ->label('API ID'),
                ImageColumn::make('banner_path')
                    ->label('Bannière')
                    ->disk('public')
                    ->width(160)
                    ->square(false),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
