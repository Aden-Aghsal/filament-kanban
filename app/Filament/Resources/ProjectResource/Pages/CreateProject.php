<?php

namespace App\Filament\Resources\ProjectResource\Pages; // Pastikan ini namespace Admin

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    // HAPUS fungsi mutateFormDataBeforeCreate, karena kita biarkan $data['leader_id'] 
    // mengambil nilai dari pilihan dropdown form, BUKAN dari auth()->id() admin.

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Simpan project (leader_id akan otomatis terisi sesuai pilihan Admin di form)
        $project = parent::handleRecordCreation($data);
        
        // 2. Masukkan user yang dipilih jadi leader tersebut ke dalam daftar member otomatis
        if ($project->leader_id) {
            $project->members()->attach($project->leader_id);
        }

        return $project;
    }

    protected function getRedirectUrl(): string
    {
        // Kembali ke halaman tabel project setelah selesai
        return $this->getResource()::getUrl('index');
    }
}