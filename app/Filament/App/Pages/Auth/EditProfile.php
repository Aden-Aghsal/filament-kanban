<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Kita panggil input standar (Nama & Email)
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                
                // INPUT KHUSUS: Upload Avatar
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->avatar() // Mode tampilan bulat
                    ->imageEditor() // Bisa crop/rotate gambar
                    ->disk('public') // Simpan di folder public
                    ->directory('avatars') // Masuk folder 'avatars'
                    ->visibility('public'),

                // Input Password (Wajib ada di halaman profil)
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}