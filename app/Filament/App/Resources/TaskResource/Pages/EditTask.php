<?php

namespace App\Filament\App\Resources\TaskResource\Pages;

use App\Filament\App\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\App\Pages\MemberKanbanBoard;
use Illuminate\Contracts\Support\Htmlable; // Tambahkan ini
use Illuminate\Support\HtmlString;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;


    // --- TRIK: MENGUBAH JUDUL JADI ADA TOMBOL PANAH DI KIRINYA ---
    public function getHeading(): string | Htmlable
    {
       
        $url = MemberKanbanBoard::getUrl(['project' => $this->record->project_id]);
        
        return new HtmlString('
            <div class="flex items-center gap-3">
                <a href="' . $url . '" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition" title="Back to Kanban">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                
                <span>Edit Task</span>
            </div>
        ');
    }

    protected function getHeaderActions(): array
    {
        return [
         
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return \App\Filament\App\Pages\MemberKanbanBoard::getUrl(['project' => $this->record->project_id]);
    }
}
