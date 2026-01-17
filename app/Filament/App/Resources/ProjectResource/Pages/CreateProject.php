<?php

namespace App\Filament\App\Resources\ProjectResource\Pages;

use App\Filament\App\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    // FUNGSI 1: Setel kamu sebagai Leader (Supaya tombol Undang muncul)
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['leader_id'] = auth()->id();
        return $data;
    }

    // FUNGSI 2: Masukkan kamu sebagai Anggota (Supaya proyek muncul di List)
    protected function handleRecordCreation(array $data): Model
    {
        $project = parent::handleRecordCreation($data);
        
        // Otomatis 'join' ke project sendiri
        $project->members()->attach(auth()->id());

        return $project;
    }
}