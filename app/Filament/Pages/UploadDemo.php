<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use RalphJSmit\Filament\Upload\Filament\Forms\Components\AdvancedFileUpload;

class UploadDemo extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Upload Pro Demo';

    protected static ?string $title = 'Upload Pro demo';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.upload-demo';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                AdvancedFileUpload::make('single_image')
                    ->image()
                    ->editable(true)
                    ->previewable(true)
                    ->label('Single image upload'),

                AdvancedFileUpload::make('multiple_files')
                    ->multiple()
                    ->maxFiles(5)
                    ->reorderable()
                    ->downloadable(true)
                    ->editable(true)
                    ->previewable(true)
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->label('Multiple file upload'),
            ]);
    }
}
