<?php

namespace App\Filament\Resources\Blog\Posts\Schemas;

use App\Models\Blog\Post;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use RalphJSmit\Filament\MediaLibrary\Filament\Forms\Components\MediaPicker;
use RalphJSmit\Filament\Upload\Filament\Forms\Components\AdvancedFileUpload;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->unique(Post::class, 'slug', ignoreRecord: true),

                        RichEditor::make('content')
                            ->required()
                            ->columnSpan('full'),

                        Select::make('blog_author_id')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('blog_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),

                        DatePicker::make('published_at')
                            ->label('Publishing date'),

                        SpatieTagsInput::make('tags'),
                    ])
                    ->columns(2),

                Section::make('Image')
                    ->schema([
                        MediaPicker::make('image_id')
                            ->relationship('image')
                            ->label('Featured image'),
                    ])
                    ->collapsible(),

                Section::make('Attachments')
                    ->schema([
                        AdvancedFileUpload::make('attachments')
                            ->multiple()
                            ->disk('r2_public')
                            ->directory(fn () => Filament::getTenant()?->getStoragePrefix() . '/post-attachments')
                            ->reorderable()
                            ->downloadable(true)
                            ->editable(true)
                            ->previewable(true)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->label('Attachments')
                            ->columns(2),
                    ])
                    ->collapsible(),
            ]);
    }
}
