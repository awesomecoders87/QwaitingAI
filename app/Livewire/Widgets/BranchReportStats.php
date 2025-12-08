<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\Rating;
use Filament\Facades\Filament;
use Livewire\Component;
use Filament\Widgets\Widget;
use App\Filament\Resources\BranchReportResource\Pages\ListBranchReports;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Auth;
use Illuminate\Support\Facades\Session;


class BranchReportStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListBranchReports::class;
    }
    
    protected function getStats(): array
    {
        $selectedLocation = Session::get('selectedLocation');

        $query = QueueStorage::where('team_id', Filament::getTenant()->id)->where('locations_id',$selectedLocation);
        $ratingQuery = $this->getPageTableQuery()->where('team_id', Filament::getTenant()->id);

        $totalQueue = $query->count();
        $closedQueue = $query->where('status', 'Close')->count();
        $averageRating = $ratingQuery->average('rating');
        $emojiText = Queue::getEmojiText();
        $emoji = null;

        foreach ($emojiText as $label => $data) {
          $range = $data['range'];
          if ($averageRating >= $range[0] && $averageRating <= $range[1]) {
            $emoji = $data['emoji'];
            break;
          }
        }

        return [
            Stat::make(__('text.total queue'), $totalQueue),
            Stat::make(__('text.closed queue'), $closedQueue),
            Stat::make(__('text.average rating'), number_format($averageRating, 2)),
            Stat::make(__('text.emotional rating'), ($emoji ? " $emoji" : ' N/A')),
        ];
    }

    public static function canView(): bool
    {
        return request()->is('branch-reports') && Auth::check();
    }

}
