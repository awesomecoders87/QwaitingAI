<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Forms;
use Filament\Facades\Filament;

class CalendarViewWidget extends FullCalendarWidget
{

    public Model | string | null $model = Booking::class;

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'dayGridWeek,dayGridDay',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'selectable'=>false,
            'editable'=>false

        ];
    }
    protected function headerActions(): array
    {
        return [
            // Actions\CreateAction::make(),
     ];
    }
    public function fetchEvents(array $fetchInfo): array
    {
        return Booking::whereDate('booking_date', '>=', $fetchInfo['start'])
            ->whereDate('booking_date', '<=', $fetchInfo['end'])
            ->where(['team_id'=>Filament::getTenant()->id])
            ->get()
            ->map(function (Booking $booking) {
                return [
                    'id'    => $booking->id,
                    'title' => $booking->name.'( '. $booking->booking_time.' )' , // Use newline character
                    'start' => $booking->booking_date,
                    'end'   => $booking->booking_date,
                    'textColor' => '#000000',
                    'backgroundColor' =>'#75f390'
                ];
            })
            ->toArray();
    }
  
    public static function canView(): bool
    {
        return false;
    }
    protected function modalActions(): array
    {
        return [
            // Actions\EditAction::make(),
            // Actions\DeleteAction::make(),

        ];
    }
    public function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Section::make('Visitor Details') 
                    ->schema([
                    Forms\Components\TextInput::make('name'),
                    Forms\Components\TextInput::make('email'),
                    Forms\Components\TextInput::make('phone'),
                    ])->columns(3),
                    Forms\Components\Section::make('Booking Details')
                    ->schema([
                    Forms\Components\TextInput::make('refID')->label(__('text.ID').'( '.__('text.Email'). ' )' ),
                    Forms\Components\TextInput::make('booking_date'),
                    Forms\Components\TextInput::make('booking_time'),
                    ])->columns(3)
                ])->columns(2),
        ];
    }


    public function eventDidMount(): string
    {
        return <<<JS
            function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
                el.setAttribute("x-tooltip", "tooltip");
                el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
            }
        JS;
    }
}
