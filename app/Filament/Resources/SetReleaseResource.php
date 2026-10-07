<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetReleaseResource\Pages;
use App\Jobs\GenerateReleaseArticleJob;
use App\Models\SetRelease;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SetReleaseResource extends Resource
{
    protected static ?string $model = SetRelease::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Pipeline';

    protected static ?string $modelLabel = 'sortie détectée';

    protected static ?string $pluralModelLabel = 'Sorties détectées';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Set')
                    ->searchable(),
                TextColumn::make('game.name')
                    ->label('Jeu')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('release_date')
                    ->label('Sortie prévue')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('announced_at')
                    ->label('Détecté le')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->label('Article généré')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? 'Oui' : 'Non')
                    ->color(fn (?string $state) => $state ? 'success' : 'danger'),
                TextColumn::make('articles_count')
                    ->label('Articles liés')
                    ->counts('articles'),
            ])
            ->filters([
                Tables\Filters\Filter::make('pending')
                    ->label('En attente de génération')
                    ->query(fn ($query) => $query->whereNull('processed_at')),
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Jeu')
                    ->relationship('game', 'name'),
            ])
            ->actions([
                Action::make('generate_article')
                    ->label("Générer l'article")
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->visible(fn (SetRelease $record) => $record->processed_at === null && $record->articles()->doesntExist())
                    ->action(function (SetRelease $record): void {
                        GenerateReleaseArticleJob::dispatch($record);
                        Notification::make()
                            ->title("Génération de l'article lancée")
                            ->body($record->name)
                            ->success()
                            ->send();
                    }),
                Action::make('view_article')
                    ->label('Voir l\'article')
                    ->icon('heroicon-o-eye')
                    ->url(fn (SetRelease $record) => $record->articles()->first()
                        ? ArticleResource::getUrl('edit', ['record' => $record->articles()->first()])
                        : null)
                    ->visible(fn (SetRelease $record) => $record->articles()->exists()),
            ])
            ->defaultSort('announced_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetReleases::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
