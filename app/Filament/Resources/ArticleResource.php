<?php

namespace App\Filament\Resources;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Contenu';

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'Articles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('game_id')
                                    ->label('Jeu')
                                    ->relationship('game', 'name')
                                    ->nullable()
                                    ->searchable()
                                    ->preload(),
                                Select::make('type')
                                    ->label('Type')
                                    ->options(ArticleType::class)
                                    ->required(),
                            ]),
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->helperText('Laisser vide pour génération automatique.'),
                        MarkdownEditor::make('content')
                            ->label('Contenu (Markdown)')
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Statut')
                                    ->options(ArticleStatus::class)
                                    ->required()
                                    ->default(ArticleStatus::Draft->value),
                                DateTimePicker::make('published_at')
                                    ->label('Publié le'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(60),
                TextColumn::make('game.name')
                    ->label('Jeu')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (ArticleType $state) => $state->label())
                    ->color(fn (ArticleType $state) => match ($state) {
                        ArticleType::ReleaseAnnouncement => 'info',
                        ArticleType::TcgNews => 'warning',
                    }),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (ArticleStatus $state) => $state->label())
                    ->color(fn (ArticleStatus $state) => match ($state) {
                        ArticleStatus::Draft => 'gray',
                        ArticleStatus::Published => 'success',
                    }),
                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ArticleStatus::class),
                Tables\Filters\SelectFilter::make('type')
                    ->options(ArticleType::class),
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Jeu')
                    ->relationship('game', 'name'),
            ])
            ->actions([
                Action::make('publish')
                    ->label('Publier')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Article $record) => $record->status === ArticleStatus::Draft)
                    ->requiresConfirmation()
                    ->action(fn (Article $record) => $record->publish()),
                Action::make('unpublish')
                    ->label('Dépublier')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Article $record) => $record->status === ArticleStatus::Published)
                    ->requiresConfirmation()
                    ->action(function (Article $record): void {
                        $record->update(['status' => ArticleStatus::Draft, 'published_at' => null]);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
