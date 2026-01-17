<?php

namespace App\Filament\App\Resources\ProjectResource\Pages;

use App\Filament\App\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['leader_id'] = auth()->id();
        return $data;
    }


    protected function handleRecordCreation(array $data): Model
    {
        $project = parent::handleRecordCreation($data);
        
      
        $project->members()->attach(auth()->id());

        return $project;
    }
}