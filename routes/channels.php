<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\{Queue, User};

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('queue-call.{team_id}', function (User $user,  $team_id) {
    // dd($team_id);
    return $user->teams->first()?->id == $team_id;
});

Broadcast::channel('queue-progress.{team_id}', function (User $user,  $team_id) {
    // $teamId = Team::getTeamId(Team::getSlug());
    return $user->teams->first()?->id == $team_id;
    $teamId = $user->team_id;
    // return $teamId == $team_id;
    return $teamId == $team_id;
});


Broadcast::channel('queue-display.{team_id}', function (User $user,  $team_id) {
    // $teamId = Team::getTeamId(Team::getSlug());
    return $user->teams->first()?->id == $team_id;
    $teamId = $user->team_id;
    // return $teamId == $team_id;
    return $teamId == $team_id;
});

Broadcast::channel('test-progress', function () {

    return true;
});

Broadcast::channel('queue-pending.{team_id}', function ($team_id) {

    $teamId = Team::getTeamId(Team::getSlug());
    return $teamId == $team_id;
});

Broadcast::channel('break-reason.{created_by}', function (User $user,  $created_by) {

    return $user?->id == $created_by;
});

Broadcast::channel('desktop-notification.{team_id}', function ($team_id) {
    $teamId = Team::getTeamId(Team::getSlug());
    return $teamId == $team_id;
});
