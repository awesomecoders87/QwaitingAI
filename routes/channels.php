<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\{Queue, User};

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('queue-call.{team_id}', function (User $user, $team_id) {
    // Allow access if user belongs to the team
    // For public channels, this acts as a filter
    return $user->teams->first()?->id == $team_id;
});

Broadcast::channel('queue-progress.{team_id}.{location_id}.{user_id}', function (User $user, $team_id, $location_id, $user_id) {
    // Allow access if user belongs to the team and matches the user_id
    return $user->teams->first()?->id == $team_id && (int) $user->id === (int) $user_id;
});


Broadcast::channel('queue-display.{team_id}.{location_id}', function ($user, $team_id, $location_id) {
    // Display screens can be public, so allow access even without authentication
    // If user is authenticated, verify they belong to the team
    if ($user && $user instanceof User) {
        return $user->teams->first()?->id == $team_id;
    }
    // For unauthenticated display screens, allow access (they're public)
    return true;
});

Broadcast::channel('test-progress', function () {

    return true;
});

Broadcast::channel('queue-transfer.{team_id}.{location_id}', function (User $user, $team_id, $location_id) {
    // Allow access if user belongs to the team
    return $user->teams->first()?->id == $team_id;
});

Broadcast::channel('queue-notification.{team_id}', function (User $user, $team_id) {
    // Allow access if user belongs to the team
    return $user->teams->first()?->id == $team_id;
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
