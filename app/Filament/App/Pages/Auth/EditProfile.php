<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;


class EditProfile extends BaseEditProfile
{
     protected function afterSave(): void
    {
        Auth::user()->refresh();
    }
   protected function handleRecordUpdate(Model $record, array $data): Model
{
    $record->update($data);

    return $record;
}


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