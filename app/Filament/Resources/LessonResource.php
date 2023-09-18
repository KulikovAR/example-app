<?php

namespace App\Filament\Resources;

use App\Models\Tag;
use Filament\Forms;
use Filament\Tables;
use App\Models\Lesson;
use App\Filament\MenuTitles;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\LessonResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LessonResource\RelationManagers;
use App\Filament\Resources\LessonResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\LessonResource\RelationManagers\LessonAdditionalDataRelationManager;
use App\Filament\Resources\LessonResource\RelationManagers\QuizRelationManager;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = MenuTitles::CATEGORY_APP;

    protected static ?string $pluralModelLabel = 'Уроки';

    protected static ?string $modelLabel = 'Урок';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->maxLength(255)->translateLabel(),
                Textarea::make('description'),
                TextInput::make('video_path')
                    ->maxLength(255),
                FileUpload::make('preview_path')
                    ->maxSize(25000)
                    ->required(),
                Select::make('tags')
                    ->multiple()
                    ->relationship('tags', 'name')
                    ->searchable(),
                TextInput::make('rating')
                    ->integer()
                    ->minValue(1)
                    ->maxValue(5)
                    ->maxLength(255),
                DateTimePicker::make('announc_date'),
                TextInput::make('order_in_display')
                    ->integer()

                    ->unique(ignoreRecord: true),
                ViewField::make('video')
                    ->view('livewire.chunkuploader'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->tooltip(fn($record) => $record->title)
                    ->limit(15),
                // TextColumn::make('description'),
                TextColumn::make('video_path')
                    ->tooltip(fn($record) => $record->video_path)
                    ->limit(15),
                TextColumn::make('order_in_display'),
                ImageColumn::make('preview_path'),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            LessonAdditionalDataRelationManager::class,
            QuizRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
