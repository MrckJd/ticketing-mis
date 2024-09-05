<?php

namespace App\Filament\Support\Resources;

use App\Enums\RequestDifficulty;
use App\Filament\Actions\AcceptAssignmentAction;
use App\Filament\Actions\RejectAssignmentAction;
use App\Filament\Actions\Tables\AdjustRequestAction;
use App\Filament\Actions\Tables\AmmendRecentActionAction;
use App\Filament\Actions\Tables\ScheduleRequestAction;
use App\Filament\Actions\Tables\StartedRequestAction;
use App\Filament\Actions\Tables\UpdateRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Support\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('currentUserAssignee');

            })
            ->columns([
                Tables\Columns\TextColumn::make('requestor.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestor.office.acronym')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currentUserAssignee.response')
                    ->badge()
                    ->label('Response')
                    ->sortable(),
                Tables\Columns\TextColumn::make('action.status')
                    ->badge()
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                UpdateRequestAction::make(),
                Tables\Actions\ViewAction::make()
                    ->modalCancelAction(false)
                    ->infolist([
                        ComponentsGrid::make(12)
                        ->schema([
                            Group::make([
                                Section::make('Personal Details')
                                    ->columnSpan(8)
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('requestor.name')
                                            ->label('Name'),
                                        TextEntry::make('requestor.number')
                                            ->prefix('+63 0')
                                            ->label('Phone Number'),
                                        TextEntry::make('requestor.email')
                                            ->label('Email'),
                                    ]),
                                Section::make('Office Details')
                                    ->columnSpan(8)
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('office.acronym')
                                            ->label('Office'),
                                        TextEntry::make('office.room')
                                            ->label('Room Number'),
                                        TextEntry::make('office.address')
                                            ->label('Office address :'),

                                    ]),

                            ])->columnSpan(8),

                            Group::make([
                                Section::make('Availability')
                                    ->columnSpan(4)
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('availability_from')
                                            ->columnSpan(1)
                                            ->date()
                                            ->label('Availability from'),
                                        TextEntry::make('availability_to')
                                            ->columnSpan(1)
                                            ->date()
                                            ->label('Availability to'),
                                    ]),
                                Section::make('Assignee Details')
                                    ->columns(2)->columnSpan(4)
                                    ->schema([
                                        TextEntry::make('target_date')
                                            ->placeholder('N/A')
                                            ->label('Target date'),
                                        TextEntry::make('target_time')
                                            ->placeholder('N/A')
                                            ->label('Target time'),
                                    ]),

                            ])->columnSpan(4),
                            Group::make([Section::make('Request Remarks')
                                ->columnSpan(4)
                                ->schema([
                                    TextEntry::make('remarks')
                                        ->columnSpan(2)
                                        ->formatStateUsing(fn ($record) => new HtmlString($record->remarks))
                                        ->label(false)
                                        ->inLinelabel(false),
                                ]),

                            ])->columnSpan(4),
                            Group::make([

                                Section::make('Request Details')
                                    ->columnSpan(5)
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('category.name')
                                            ->label('Category'),
                                        TextEntry::make('subcategory.name')
                                            ->label('Subcategory'),
                                    ]),
                            ])->columnSpan(5),
                            Group::make([
                                Section::make('Assignee Details')
                                    ->columns(2)
                                    ->columnSpan(6)
                                    ->schema([
                                        TextEntry::make('priority')
                                            ->placeholder('N/A')
                                            ->label('Priority'),
                                        TextEntry::make('difficulty')
                                            ->placeholder('N/A')
                                            ->label('Difficulty'),
                                    ]),

                            ])->columnSpan(3),

                            Group::make([

                                Section::make('Attachments')
                                    ->columns(2)->columnSpan(4)
                                    ->schema([
                                        TextEntry::make('attachment.name')
                                            ->label(false)
                                            ->inLinelabel(false),
                                    ]),
                            ])->columnSpan(8),
                            Group::make([

                                Section::make('Assignees')
                                    ->columnSpan(4)
                                    ->schema([
                                        TextEntry::make('')
                                            ->label(false)
                                            ->placeholder(fn ($record)=>  implode(', ' , $record->assignees->pluck('name')->toArray()))
                                            ->inLinelabel(false),
                                    ]),
                            ])->columnSpan(4),
                            Group::make([
                                Actions::make([
                                    AcceptAssignmentAction::make(),
                                    RejectAssignmentAction::make(),
                                ])
                            ])

                        ])
                        ]),

                ActionGroup::make([
                    AmmendRecentActionAction::make(),
                    StartedRequestAction::make(),
                    ViewRequestHistoryAction::make(),
                    AdjustRequestAction::make(),
                    ScheduleRequestAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
        ];
    }
}
