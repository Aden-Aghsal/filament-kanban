<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum TaskStatus: string implements HasLabel, HasColor
{
    // Definisi Status sesuai request
    case Initiated = 'Initiated';
    case OnProgress = 'On Progress';
    case Check = 'Check';
    case Revision = 'Revision';
    case Done = 'Done';
    case Cancelled = 'Cancelled'; // Saya koreksi typo 'Cancell' jadi 'Cancelled' biar rapi

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Initiated => 'Initiated',
            self::OnProgress => 'On Progress',
            self::Check => 'Check (Review)',
            self::Revision => 'Revision',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Initiated => 'gray',    // Abu-abu (Awal)
            self::OnProgress => 'info',   // Biru (Sedang jalan)
            self::Check => 'warning',     // Kuning/Oranye (Butuh cek)
            self::Revision => 'danger',   // Merah (Perlu revisi)
            self::Done => 'success',      // Hijau (Selesai)
            self::Cancelled => 'gray',    // Abu-abu gelap (Batal)
        };
    }

    // Fungsi wajib untuk Mokhosh Kanban Plugin
    public static function statuses(): array
    {
        return array_map(fn($case) => [
            'id' => $case->value,
            'title' => $case->getLabel(),
            'color' => $case->getColor(),
        ], self::cases());
    }
}