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
               
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                
              
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->avatar() 
                    ->imageEditor() 
                    ->disk('public') 
                    ->directory('avatars') 
                    ->visibility('public'),

               
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}