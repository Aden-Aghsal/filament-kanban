<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    
       protected static ?string $navigationGroup = 'Project Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Sembunyikan dari sidebar Admin
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Task Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Task Title')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->default(fn () => request()->query('project') ?? request()->query('project_id'))
                            ->disabled(fn (?Task $record) => filled($record) || filled(request()->query('project')) || filled(request()->query('project_id')))
                            ->dehydrated(fn (?Task $record) => $record === null)
                            ->required(),

                        // Assignee Normal
                        Forms\Components\Select::make('user_id')
                            ->label('Assignee')
                            ->relationship(
                                name: 'assignee',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query, \Filament\Forms\Get $get, ?Task $record) {
                                    $projectId = $record?->project_id ?? $get('project_id');
                                    $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'));
                                    if ($projectId) {
                                        $query->where(function ($q) use ($projectId) {
                                            $q->whereHas('projects', function ($subQ) use ($projectId) {
                                                $subQ->where('projects.id', $projectId);
                                            })
                                            ->orWhereIn('id', function ($subQ) use ($projectId) {
                                                $subQ->select('leader_id')->from('projects')->where('id', $projectId);
                                            });
                                        });
                                    }
                                }
                            )
                            ->searchable()
                            ->preload(),

                    ])->columns(2),

                Forms\Components\Section::make('Details & Timeline')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->disableToolbarButtons(['attachFiles'])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'High' => 'High',
                                'Normal' => 'Normal',
                                'Low' => 'Low',
                            ])
                            ->default('Normal'),

                        Forms\Components\DatePicker::make('due_date')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->closeOnDateSelection()
                            ->label('Due Date'),
                    ])->columns(2),

                Forms\Components\Section::make('Subtasks')
                    ->schema([
                        Forms\Components\Repeater::make('subtasks')
                            ->label('Checklist')
                            ->schema([
                                Forms\Components\TextInput::make('title')->label('Step Name')->required(),
                                Forms\Components\Checkbox::make('is_completed')->label('Completed')->default(false),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('assignee.name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
